<?php

namespace App\Http\Controllers\Pengumpulan;

use App\Http\Controllers\PdfWithAttachments;
use Exception;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\ProposalSkripsi;
use Illuminate\Support\Facades\DB;
use App\Models\ProposalSkripsiForm;
use App\Http\Controllers\Controller;
use App\Models\AreaPenelitian;
use App\Models\KodePenelitianProposal;
use App\Models\PembimbingMahasiswa;
use App\Models\ResearchList;
use App\Models\TopikPenelitianProposal;
use Illuminate\Support\Facades\Auth;
use TCPDF;
use setasign\Fpdi\Tcpdf\Fpdi; // Use the FPDI class that extends TCPDF

use Illuminate\Support\Facades\Storage;
use function Symfony\Component\VarDumper\Dumper\esc;

class ProposalSkripsiController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();
        $result = ProposalSkripsi::with('proposalSkripsiForm')
            ->where('mahasiswa_id', $user->id)
            ->orderBy('proposal_skripsi_form_id', 'desc')
            ->get();
        $research = AreaPenelitian::with('researchList')->get();
        $topik = ResearchList::all();
        if (count($result) <= 0) {
            $result = [];
        }
        return view('pages.mahasiswa.proposal-skripsi.proposal-skripsi', [
            'title' => 'Proposal Skripsi',
            'subtitle' => 'Pengumpulan',
            'data' => $data,
            'result' => $result,
            'research' => $research,
            'topik' => $topik,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_proposal' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:30720'],
            'topik_penelitian' => ['required'],
            'id_form' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('proposal_skripsi')->lockForUpdate()->get();
                $active = TahunAjaran::where('status_aktif', 1)->first();
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $form = ProposalSkripsiForm::where('uuid', $request->id_form)->firstOrFail();
                $pembimbing = PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->where('mahasiswa', $user->id)
                    ->first();

                $pembimbingPertama = $pembimbing->pembimbing1 ?? null;
                $pembimbingKedua = $pembimbing->pembimbing2 ?? null;

                // Handle file naming logic based on penamaan proposal
                $isPenamaan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->first();
                if ($isPenamaan->penamaan_proposal == 1) {
                    $format = explode("_", $isPenamaan->pengaturanDetail->penamaan_proposal);
                    if (count($format) != 3) {
                        throw new Exception('Format invalid');
                    }

                    $escJudul = esc($request->judul_proposal);
                    if (str_word_count($escJudul) < 4) {
                        throw new Exception('Jumlah kata kurang');
                    }

                    $file = $request->file('file');
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                    $fileName = $this->generateFileName($format, $user, $escJudul);

                    // Store the uploaded PDF file
                    $file->storeAs('uploads/proposal', $fileNameRandom);
                    $mimeType = $file->getClientMimeType();
                    // Insert proposal skripsi record
                    $proposal = ProposalSkripsi::create([
                        'proposal_skripsi_form_id' => $form->id,
                        'mahasiswa_id' => $user->id,
                        'judul_proposal' => $request->judul_proposal,
                        'file_proposal' => $fileName,
                        'file_proposal_random' => $fileNameRandom,
                        'file_proposal_mime' => $file->getClientMimeType(),
                        'status' => 1,
                        'penilai1' => $pembimbingPertama,
                        'penilai2' => $pembimbingKedua,
                        'is_expired' => false,
                        'file_penilai1_mime' => $mimeType,
                        'file_penilai2_mime' => $mimeType,
                        'file_penilai3_mime' => $mimeType,
                    ]);
                    // Now, call the embedFilesInExistingPdf method to attach additional files
                    $filesToEmbed = [storage_path('data.json'), storage_path('data.xml')];
                    $this->embedFilesInExistingPdf(
                        storage_path('app/uploads/proposal/' . $fileNameRandom),
                        storage_path('app/uploads/proposal-signed/' . $fileNameRandom),
                        $filesToEmbed
                    );
                } else {
                    // Similar process if there is no penamaan_proposal check
                    $file = $request->file('file');
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();

                    $proposal = ProposalSkripsi::create([
                        'proposal_skripsi_form_id' => $form->id,
                        'mahasiswa_id' => $user->id,
                        'judul_proposal' => $request->judul_proposal,
                        'file_proposal' => $file->getClientOriginalName(),
                        'file_proposal_random' => $fileNameRandom,
                        'file_proposal_mime' => $file->getClientMimeType(),
                        'status' => 1,
                        'penilai1' => $pembimbingPertama,
                        'penilai2' => $pembimbingKedua,
                        'is_expired' => false,
                    ]);

                    $file->storeAs('uploads/proposal', $fileNameRandom);

                    // Call the embedFilesInExistingPdf function
                    $filesToEmbed = ['data.json', 'data.xml'];
                    $this->embedFilesInExistingPdf(
                        storage_path('app/uploads/proposal/' . $fileNameRandom),
                        storage_path('app/uploads/proposal-signed/' . $fileNameRandom),
                        $filesToEmbed
                    );
                }
            });
            return redirect()->route('proposal.skripsi.pengumpulan')->with('success', 'Berhasil mengupload file');
        } catch (Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Gagal mengupload file');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            DB::transaction(function () use ($uuid) {
                $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
                $kode = TopikPenelitianProposal::where('proposal_skripsi_id', $data->id)->get();
                $path = 'uploads/proposal/' . $data->file_proposal_random;

                if (Storage::exists($path)) {
                    Storage::delete($path);
                    foreach ($kode as $row) {
                        $row->delete();
                    }
                    $data->delete();
                }
            });
            return redirect()->route('proposal.skripsi.pengumpulan')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Gagal menghapus file');
        }
    }

    public function hasil()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();
        $result = ProposalSkripsi::with('proposalSkripsiForm')
            ->with('penilaiPertama', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKedua', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKetiga', function ($query) {
                $query->select('id', 'nama');
            })
            ->where('mahasiswa_id', $user->id)
            ->where(function ($query) {
                $query->where('penilai1', '!=', null)
                    ->where('penilai2', '!=', null)
                    ->where('penilai3', '!=', null);
            })
            ->get();
        if (count($result) <= 0) {
            $result = [];
        }
        return view('pages.mahasiswa.hasil-proposal.hasil-proposal', [
            'title' => 'Hasil Proposal Skripsi',
            'subtitle' => '',
            'data' => $data,
            'result' => $result,
        ]);
    }

    public function downloadFilePeriksaPenilai1($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-proposal/' . $data->file_random_penilai1;
        $fileName = $data->file_penilai1;
        $mime = $data->file_penilai1_mime;

        if (Storage::exists($path)) {
            if ($mime == 'application/pdf') {
                return Storage::download($path, $fileName);
            }
        } else {
            abort(404);
        }
    }

    public function downloadFilePeriksaPenilai2($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();

        $path = 'uploads/periksa-proposal/' . $data->file_random_penilai2;
        $fileName = $data->file_penilai2;
        $mime = $data->file_penilai2_mime;

        if (Storage::exists($path)) {
            if ($mime == 'application/pdf') {
                return Storage::download($path, $fileName);
            }
        } else {
            abort(404);
        }
    }

    public function downloadFilePeriksaPenilai3($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();

        $path = 'uploads/periksa-proposal/' . $data->file_random_penilai3;
        $fileName = $data->file_penilai3;
        $mime = $data->file_penilai3_mime;

        if (Storage::exists($path)) {
            if ($mime == 'application/pdf') {
                return Storage::download($path, $fileName);
            }
        } else {
            abort(404);
        }
    }

    public function getFile($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/proposal/' . $data->file_proposal_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_proposal);
            // $fileContents = Storage::get($path);
            // return response($fileContents, 200)
            //     ->header('Content-Type', $data->file_proposal_mime);
        } else {
            abort(404);
        }
    }

    public function embedFilesInExistingPdf($existingPdfPath, $outputPdfPath, array $filesToEmbed)
    {
        // Ensure the existing PDF file exists
        if (!file_exists($existingPdfPath)) {
            return response()->json(['error' => 'PDF file not found'], 404);
        }

        // Create a new FPDI instance (which extends TCPDF)
        $pdf = new Fpdi();

        // Set document information if needed
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Document with Attachments');

        // Import the existing PDF
        $pageCount = $pdf->setSourceFile($existingPdfPath);

        // Import all pages from the existing PDF
        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            if ($i == $pageCount) {
                if (file_exists(storage_path('/signature.png'))) {
                    // Determine the position and size for the signature
                    $xPosition = $size['width'] - 60; // Adjust as needed
                    $yPosition = $size['height'] - 40; // Adjust as needed
                    $width = 50; // Adjust as needed
                    $height = 30; // Adjust as needed

                    // Embed the signature image
                    $pdf->Image(
                        storage_path('/signature.png'),
                        $xPosition,
                        $yPosition,
                        $width,
                        $height,
                        '',     // Image type (auto-detected if empty)
                        '',     // Link (none in this case)
                        '',     // Alignment
                        false,  // Resize (true or false)
                        300,    // DPI
                        '',     // Image mask
                        false,  // Fit box (true or false)
                        false,  // Hidden (true or false)
                        0       // Border (0 for no border)
                    );
                } else {
                    dd($existingPdfPath);
                }
            }
        }



        // Embed each file as a file annotation
        foreach ($filesToEmbed as $filePath) {
            if (file_exists($filePath)) {
                $fileName = basename($filePath);
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                // Determine the MIME type based on file extension
                $fileType = match($fileExtension) {
                    'json' => 'application/json',
                    'xml'  => 'application/xml',
                    'dat'  => 'application/octet-stream',  // DAT files typically use this MIME type
                    default => mime_content_type($filePath), // Fallback to generic mime type detection
                };

                // Description for the attachment
                $description = "Attached file: {$fileName}";

                // Attach the file as an annotation to the first page
                $pdf->Annotation(
                    0, 0, 1, 1,     // Position and size of the annotation icon
                    $description,      // Description of the file
                    ['Subtype' => 'FileAttachment', 'Name' => 'PushPin', 'FS' => $filePath]
                );
            } else {
                \Log::warning("File not found: {$filePath}");
            }
        }

        // Output the PDF to a file
        $pdf->Output($outputPdfPath, 'F');
    }


    private function generateFileName($format, $user, $judulProposal)
    {
        $nim = $user->nim;
        $nama = str_replace(' ', '', trim($user->nama));
        $fileName = '';

        $firstFormat = $format[0];
        $secondFormat = $format[1];

        if ($firstFormat === 'nim') {
            if ($secondFormat === 'nama') {
                $fileName = $nim . '_' . $nama . '_' . 'ProposalSkripsi' . '.pdf';
            } elseif ($secondFormat === 'judul') {
                $fileName = $nim . '_' . 'ProposalSkripsi' . '_' . $nama . '.pdf';
            }
        } elseif ($firstFormat === 'nama') {
            if ($secondFormat === 'nim') {
                $fileName = $nama . '_' . $nim . '_' . 'ProposalSkripsi' . '.pdf';
            } elseif ($secondFormat === 'judul') {
                $fileName = $nama . '_' . 'ProposalSkripsi' . '_' . $nim . '.pdf';
            }
        } elseif ($firstFormat === 'judul') {
            if ($secondFormat === 'nim') {
                $fileName = 'ProposalSkripsi' . '_' . $nim . '_' . $nama . '.pdf';
            } elseif ($secondFormat === 'nama') {
                $fileName = 'ProposalSkripsi' . '_' . $nama . '_' . $nim . '.pdf';
            }
        }

        return $fileName;
    }

}
