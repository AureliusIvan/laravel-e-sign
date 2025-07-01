<?php

namespace App\Http\Controllers\Pengumpulan;

use Exception;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\AreaPenelitian;
use App\Models\RevisiProposal;
use App\Models\ProposalSkripsi;
use App\Models\RevisiProposalForm;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProposalSkripsiForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;
use App\Http\Traits\PdfSanitizationTrait;
use function Symfony\Component\VarDumper\Dumper\esc;

class RevisiProposalController extends Controller
{
    use PdfSanitizationTrait;
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = RevisiProposalForm::with('proposalSkripsiForm')
            ->where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();

        $result = RevisiProposal::with('revisiProposalForm')
            ->where('mahasiswa_id', $user->id)
            ->get();
        // $research = AreaPenelitian::with('researchList')->get();
        if (count($result) <= 0) {
            $result = [];
        }
        return view('pages.mahasiswa.revisi-proposal.revisi-proposal', [
            'title' => 'Revisi Skripsi',
            'subtitle' => 'Pengumpulan',
            'data' => $data,
            'result' => $result,
            // 'research' => $research,
        ]);
    }

    public function fetchProposal($slug = '')
    {
        try {
            $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
            $form = ProposalSkripsiForm::where('uuid', $slug)->firstOrFail();
            $proposal = RevisiProposalForm::withWhereHas(
                'proposalSkripsiForm.proposalSkripsi',
                function ($query) use ($user) {
                    $query->select('id', 'uuid', 'proposal_skripsi_form_id', 'judul_proposal', 'mahasiswa_id', 'status_akhir');
                    $query->where('mahasiswa_id', $user->id);
                    $query->where('status_akhir', 1);
                }
            )
                ->where('proposal_skripsi_form_id', $form->id)
                ->get();
            if (!$proposal) {
                throw new Exception('Proposal not found');
            } else {
                return response()->json([
                    'data' => $proposal,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
            ]);
        }
    }

    public function fetchDetailProposal($uuid = '')
    {
        if (!$uuid || $uuid === '' || empty($uuid)) {
            return response()->json([
                'data' => [],
            ]);
        } else {
            try {
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $proposal = ProposalSkripsi::select('id', 'judul_proposal')
                    ->where('uuid', $uuid)
                    ->where('mahasiswa_id', $user->id)
                    ->first();

                return response()->json([
                    'data' => $proposal,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal',
                ]);
            }
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'proposal' => ['required'],
            'judul_revisi_proposal' => ['required'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:30720'],
            'reupload' => ['required'],
            'revisi_id' => ['sometimes', 'required_if:reupload,1'],
            'id_form' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('revisi_proposal')->lockForUpdate()->get();
                $active = TahunAjaran::where('status_aktif', 1)->first();
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $form = RevisiProposalForm::where('uuid', $request->id_form)->firstOrFail();
                $isPenamaan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->first();
                $proposal = ProposalSkripsi::where('uuid', $request->proposal)->firstOrFail();

                if ($isPenamaan->penamaan_proposal == 1) {
                    $format = explode("_", $isPenamaan->pengaturanDetail->penamaan_revisi_proposal);
                    $countFormat = count($format);

                    if ($countFormat != 3) {
                        throw new Exception('Format invalid');
                    }

                    $escJudul = esc($request->judul_revisi_proposal);
                    $countWordJudul = str_word_count($escJudul);

                    if ($countWordJudul < 4) {
                        throw new Exception('Jumlah kata kurang');
                    } else {
                        $file = $request->file('file');
                        $clientName = $file->getClientOriginalName();
                        $mimeType = $file->getClientMimeType();
                        $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                        $nim = $user->nim;
                        $nama = $user->nama;

                        if ($nama == trim($nama) && str_contains($nama, ' ')) {
                            $nama = str_replace(' ', '', $nama);
                        }
                        // $judul = explode(' ', $escJudul);
                        $firstFormat = $format[0];
                        $secondFormat = $format[1];
                        $thridFormat = $format[2];
                        $fileName = '';

                        if ($firstFormat == 'nim') {
                            if ($secondFormat == 'nama') {
                                $fileName = $nim . '_' . $nama . '_' . 'RevisiProposalSkripsi' . '.pdf';
                            } elseif ($secondFormat == 'judul') {
                                $fileName = $nim . '_' . 'RevisiProposalSkripsi' . '_' . $nama . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } elseif ($firstFormat == 'nama') {
                            if ($secondFormat == 'nim') {
                                $fileName = $nama . '_' . $nim . '_' . 'RevisiProposalSkripsi' . '.pdf';
                            } elseif ($secondFormat == 'judul') {
                                $fileName = $nama . '_' . 'RevisiProposalSkripsi' . '_' . $nim . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } elseif ($firstFormat == 'judul') {
                            if ($secondFormat == 'nim') {
                                $fileName = 'RevisiProposalSkripsi' . '_' . $nim . '_' . $nama . '.pdf';
                            } elseif ($secondFormat == 'nama') {
                                $fileName = 'RevisiProposalSkripsi' . '_' . $nama . '_' . $nim . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } else {
                            throw new  Exception("Error Processing Request", 1);
                        }

                        if ($request->reupload == 1) {
                            $revisi = RevisiProposal::where('uuid', $request->revisi_id)
                                ->where('revisi_proposal_form_id', $form->id)
                                ->where('mahasiswa_id', $user->id)
                                ->firstOrFail();
                            $revisi->judul_revisi_proposal = $request->judul_revisi_proposal;
                            $revisi->file_revisi_proposal = $fileName;
                            $revisi->file_revisi_proposal_random = $fileNameRandom;
                            $revisi->status = 1;
                            $revisi->status_revisi_approval_penilai1 = 2;
                            $revisi->status_revisi_approval_penilai2 = 2;
                            $revisi->status_revisi_approval_penilai3 = 2;
                            $revisi->save();
                            $file->storeAs('uploads/revisi-proposal', $fileNameRandom);
                        } else {
                            RevisiProposal::create([
                                'proposal_skripsi_id' => $proposal->id,
                                'revisi_proposal_form_id' => $form->id,
                                'mahasiswa_id' => $user->id,
                                'judul_revisi_proposal' => $request->judul_revisi_proposal,
                                'file_revisi_proposal' => $fileName,
                                'file_revisi_proposal_random' => $fileNameRandom,
                                'status' => 1,
                                'penilai1' => $proposal->penilai1,
                                'penilai2' => $proposal->penilai2,
                                'penilai3' => $proposal->penilai3,
                                'status_revisi_approval_penilai1' => 2,
                                'status_revisi_approval_penilai2' => 2,
                                'status_revisi_approval_penilai3' => 2,
                                'status_akhir' => 2,
                            ]);
                            $file->storeAs('uploads/revisi-proposal', $fileNameRandom);
                        }
                    }
                } else {
                    $escJudul = esc($request->judul_revisi_proposal);
                    $countWordJudul = str_word_count($escJudul);

                    if ($countWordJudul < 4) {
                        throw new Exception('Jumlah kata kurang');
                    } else {
                        $file = $request->file('file');
                        $clientName = $file->getClientOriginalName();
                        $mimeType = $file->getClientMimeType();
                        $fileNameRandom = date('YmdHis') . '_' . $file->hashName();

                        if ($request->reupload == 1) {
                            $revisi = RevisiProposal::where('uuid', $request->revisi_id)
                                ->where('revisi_proposal_form_id', $form->id)
                                ->where('mahasiswa_id', $user->id)
                                ->firstOrFail();
                            $revisi->judul_revisi_proposal = $request->judul_revisi_proposal;
                            $revisi->file_revisi_proposal = $clientName;
                            $revisi->file_revisi_proposal_random = $fileNameRandom;
                            $revisi->status = 1;
                            $revisi->status_revisi_approval_penilai1 = 2;
                            $revisi->status_revisi_approval_penilai2 = 2;
                            $revisi->status_revisi_approval_penilai3 = 2;
                            $revisi->save();
                            $file->storeAs('uploads/revisi-proposal', $fileNameRandom);
                        } else {
                            RevisiProposal::create([
                                'proposal_skripsi_id' => $proposal->id,
                                'revisi_proposal_form_id' => $form->id,
                                'mahasiswa_id' => $user->id,
                                'judul_revisi_proposal' => $request->judul_revisi_proposal,
                                'file_revisi_proposal' => $clientName,
                                'file_revisi_proposal_random' => $fileNameRandom,
                                'status' => 1,
                                'penilai1' => $proposal->penilai1,
                                'penilai2' => $proposal->penilai2,
                                'penilai3' => $proposal->penilai3,
                                'status_revisi_approval_penilai1' => 2,
                                'status_revisi_approval_penilai2' => 2,
                                'status_revisi_approval_penilai3' => 2,
                                'status_akhir' => 2,
                            ]);
                            $file->storeAs('uploads/revisi-proposal', $fileNameRandom);
                        }
                    }
                }
            });
            return redirect()->route('revisi-proposal-skripsi.pengumpulan')->with('success', 'Berhasil mengupload file');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupload file');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');

        try {
            $data = RevisiProposal::where('uuid', $uuid)->firstOrFail();
            $path = 'uploads/revisi-proposal/' . $data->file_revisi_proposal_random;

            if (Storage::exists($path)) {
                if (Storage::delete($path)) {
                    $data->judul_revisi_proposal = null;
                    $data->file_revisi_proposal = null;
                    $data->file_revisi_proposal_random = null;
                    $data->status = 0;
                    $data->save();
                }
            }
            return redirect()->route('revisi-proposal-skripsi.pengumpulan')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus file');
        }
    }

    public function getFile($uuid)
    {
        $data = RevisiProposal::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/revisi-proposal/' . $data->file_revisi_proposal_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_revisi_proposal);
        } else {
            abort(404);
        }
    }

    public function fileRevisiPenilai1($uuid)
    {
        $data = RevisiProposal::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-revisi-proposal/' . $data->file_revisi_random_penilai1;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_revisi_penilai1);
        } else {
            abort(404);
        }
    }

    public function fileRevisiPenilai2($uuid)
    {
        $data = RevisiProposal::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-revisi-proposal/' . $data->file_revisi_random_penilai2;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_revisi_penilai2);
        } else {
            abort(404);
        }
    }

    public function fileRevisiPenilai3($uuid)
    {
        $data = RevisiProposal::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-revisi-proposal/' . $data->file_revisi_random_penilai3;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_revisi_penilai3);
        } else {
            abort(404);
        }
    }
}