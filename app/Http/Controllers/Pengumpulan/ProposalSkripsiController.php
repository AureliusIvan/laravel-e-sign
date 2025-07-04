<?php

namespace App\Http\Controllers\Pengumpulan;

use App\Http\Controllers\PdfWithAttachments;
use Exception;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
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
use setasign\Fpdi\Tcpdf\Fpdi;

// Use the FPDI class that extends TCPDF
use App\Http\Controllers\SignatureController;
use App\Http\Traits\PdfSanitizationTrait;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\VarDumper\Dumper\esc;

class ProposalSkripsiController extends Controller
{
    use PdfSanitizationTrait;
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
            'title' => 'Skripsi',
            'subtitle' => 'Pengumpulan',
            'data' => $data,
            'result' => $result,
            'research' => $research,
            'topik' => $topik,
        ]);
    }

    /**
     * Store a newly created resource in storage (called on mahasiswa's proposal submission)
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_proposal' => ['required', 'string'],
            'judul_proposal_en' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf'],
            'topik_penelitian' => ['required'],
            'id_form' => ['required'],
        ]);

        // Validate file upload
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ada');
        }

        $file = $request->file('file');
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();

        // **🔒 PDF SANITIZATION FOR STUDENT UPLOAD**
        $sanitizationResult = $this->sanitizeStudentPdf($file, 'proposal_skripsi', $user->nim);
        
        if (!$sanitizationResult['success']) {
            return redirect()->back()->with('error', 'Upload gagal: ' . $sanitizationResult['error']);
        }

        // Use sanitized file if available, otherwise use original
        $fileToStore = $sanitizationResult['sanitized'] ? $sanitizationResult['sanitized_file'] : $sanitizationResult['original_file'];
        
        // Log sanitization result
        if ($sanitizationResult['sanitized']) {
            \Illuminate\Support\Facades\Log::info("Student proposal PDF was sanitized", [
                'student_nim' => $user->nim,
                'original_file' => $file->getClientOriginalName(),
                'method' => $sanitizationResult['method'] ?? 'unknown'
            ]);
        }

        try {
            DB::transaction(function () use ($request, $fileToStore, $sanitizationResult) {
                $active = TahunAjaran::where('status_aktif', 1)->first();
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $form = ProposalSkripsiForm::where('uuid', $request->id_form)->firstOrFail();
                $pembimbing = PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->where('mahasiswa', $user->id)
                    ->first();

                $pembimbingPertama = $pembimbing->pembimbing1 ?? null;
                $pembimbingKedua = $pembimbing->pembimbing2 ?? null;

                $isPenamaan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->first();

                $fileNameRandom = $this->getRandomFileName($fileToStore);

                if ($this->shouldApplyNamingConvention($isPenamaan)) {
                    $this->validateProposalTitle($request->judul_proposal);
                    $this->validateProposalTitle($request->judul_proposal_en);

                    $fileName = $this->generateFileName($isPenamaan->pengaturanDetail->penamaan_proposal, $user, esc($request->judul_proposal));
                } else {
                    $fileName = $fileToStore->getClientOriginalName();
                }

                $this->storeFile($fileToStore, $fileNameRandom);

                $this->createProposalSkripsi($form->id, $user->id, $request->judul_proposal, $request->judul_proposal_en, $fileName, $fileNameRandom, $fileToStore->getClientMimeType(), $pembimbingPertama, $pembimbingKedua);

                // Clean up temporary sanitization files
                $this->cleanupSanitizationFiles();
            });

            // Prepare success message with sanitization info
            $successMessage = 'Berhasil mengupload file';
            if ($sanitizationResult['sanitized']) {
                $successMessage .= ' (file telah dibersihkan untuk keamanan)';
            } elseif (isset($sanitizationResult['warnings'])) {
                $successMessage .= ' (file lolos pemeriksaan keamanan)';
            }

            $redirect = redirect()->route('proposal.skripsi.pengumpulan')->with('success', $successMessage);

            // Add warnings if any
            if (isset($sanitizationResult['warnings']) && !empty($sanitizationResult['warnings'])) {
                $redirect->with('warning', 'Peringatan: ' . implode(', ', $sanitizationResult['warnings']));
            }

            return $redirect;

        } catch (Exception $e) {
            // Clean up any temporary files on error
            $this->cleanupSanitizationFiles();
            return redirect()->back()->with('error', 'Gagal mengupload file: ' . $e->getMessage());
        }
    }

    /**
     * Generate a random file name.
     */
    private function getRandomFileName($file)
    {
        return date('YmdHis') . '_' . $file->hashName();
    }

    /**
     * Determine if a naming convention should be applied.
     */
    private function shouldApplyNamingConvention($isPenamaan)
    {
        return isset($isPenamaan->penamaan_proposal) && $isPenamaan->penamaan_proposal == 1;
    }

    /**
     * Validate the proposal title based on word count.
     */
    private function validateProposalTitle($judul)
    {
        if (str_word_count(esc($judul)) < 4) {
            throw new Exception('Jumlah kata kurang');
        }
    }

    /**
     * Store the uploaded file.
     */
    private function storeFile($file, $fileNameRandom)
    {
        $file->storeAs('uploads/proposal', $fileNameRandom);
    }

    /**
     * Create a new ProposalSkripsi record.
     */
    private function createProposalSkripsi($formId, $mahasiswaId, $judulProposal, $judulProposalEn, $fileName, $fileNameRandom, $mimeType, $pembimbing1, $pembimbing2)
    {
        ProposalSkripsi::create([
            'proposal_skripsi_form_id' => $formId,
            'mahasiswa_id' => $mahasiswaId,
            'judul_proposal' => $judulProposal,
            'judul_proposal_en' => $judulProposalEn,
            'file_proposal' => $fileName,
            'file_proposal_random' => $fileNameRandom,
            'status' => 1,
            // Note: penilai1, penilai2, penilai3 will be assigned later by admin/kaprodi
            // to ensure 3 independent evaluators are assigned before evaluation can begin
            'is_expired' => false,
        ]);
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            DB::transaction(function () use ($uuid) {
//                $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
//                $kode = TopikPenelitianProposal::where('proposal_skripsi_id', $data->id)->get();
//                $path = 'uploads/proposal/' . $data->file_proposal_random;
//
//                if (Storage::exists($path)) {
//                    Storage::delete($path);
//                    foreach ($kode as $row) {
//                        $row->forceDelete();
//                    }
//                    $data->forceDelete();
//                }
                $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
                $path = 'uploads/proposal/' . $data->file_proposal_random;
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
                $data->delete();
            });
            return redirect()->route('proposal.skripsi.pengumpulan')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus file ' . $e->getMessage());
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
            ->get(); // Show all proposals for this student, regardless of evaluator assignment status
            
//        dd($result);
        if (count($result) <= 0) {
            $result = [];
        }
        return view('pages.mahasiswa.hasil-proposal.hasil-proposal', [
            'title' => 'Hasil Skripsi',
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
