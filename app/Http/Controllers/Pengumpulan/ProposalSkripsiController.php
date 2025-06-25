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

    /**
     * Store a newly created resource in storage (called on mahasiswa's proposal submission)
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_proposal' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf'],
            'topik_penelitian' => ['required'],
            'id_form' => ['required'],
        ]);

        // Validate file upload
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau tidak ada');
        }

        try {
            DB::transaction(function () use ($request) {
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

                $file = $request->file('file');
                $fileNameRandom = $this->getRandomFileName($file);

                if ($this->shouldApplyNamingConvention($isPenamaan)) {
                    $this->validateProposalTitle($request->judul_proposal);

                    $fileName = $this->generateFileName($isPenamaan->pengaturanDetail->penamaan_proposal, $user, esc($request->judul_proposal));
                } else {
                    $fileName = $file->getClientOriginalName();
                }

                $this->storeFile($file, $fileNameRandom);

                $this->createProposalSkripsi($form->id, $user->id, $request->judul_proposal, $fileName, $fileNameRandom, $file->getClientMimeType(), $pembimbingPertama, $pembimbingKedua);
            });

            return redirect()->route('proposal.skripsi.pengumpulan')->with('success', 'Berhasil mengupload file');
        } catch (Exception $e) {
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
    private function createProposalSkripsi($formId, $mahasiswaId, $judulProposal, $fileName, $fileNameRandom, $mimeType, $pembimbing1, $pembimbing2)
    {
        ProposalSkripsi::create([
            'proposal_skripsi_form_id' => $formId,
            'mahasiswa_id' => $mahasiswaId,
            'judul_proposal' => $judulProposal,
            'file_proposal' => $fileName,
            'file_proposal_random' => $fileNameRandom,
            'status' => 1,
            'penilai1' => $pembimbing1,
            'penilai2' => $pembimbing2,
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
//            ->with('penilaiKedua', function ($query) {
//                $query->select('id', 'nama');
//            })
//            ->with('penilaiKetiga', function ($query) {
//                $query->select('id', 'nama');
//            })
            ->where('mahasiswa_id', $user->id)
            ->where(function ($query) {
                $query->where('penilai1', '!=', null);
//                    ->where('penilai2', '!=', null)
//                    ->where('penilai3', '!=', null);
            })
            ->get();
//        dd($result);
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
