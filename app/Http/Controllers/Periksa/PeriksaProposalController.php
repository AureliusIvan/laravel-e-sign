<?php

namespace App\Http\Controllers\Periksa;

use Exception;
use ZipArchive;
use App\Models\Dosen;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\ProposalSkripsi;
use Illuminate\Support\Facades\DB;
use App\Models\ProposalSkripsiForm;
use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PeriksaProposalController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('ditutup', '<', $now)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.dosen.periksa-proposal.periksa-proposal', [
            'title' => 'Periksa Proposal',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function show($segment)
    {
        $form = ProposalSkripsiForm::where('uuid', $segment)->first();
        $user = Dosen::where('user_id', Auth::user()->id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->firstOrFail();
        $data = ProposalSkripsi::with('proposalSkripsiForm')
            ->with('penilaiPertama', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKedua', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKetiga', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
            ->with('kodePenelitianProposal.areaPenelitian')
            ->where('proposal_skripsi_form_id', $form->id)
            ->where(function ($query) use ($user) {
                $query->where('penilai1', $user->id)
                    ->orWhere('penilai2', $user->id)
                    ->orWhere('penilai3', $user->id);
            })
            ->where(function ($query) {
                $query->where('penilai1', '!=', null)
                    ->where('penilai2', '!=', null)
                    ->where('penilai3', '!=', null);
            })
            ->get();
        return view('pages.dosen.periksa-proposal.detail-periksa-proposal', [
            'title' => 'Periksa Proposal',
            'subtitle' => 'Detail',
            'data' => $data,
            'form' => $form,
            'dosen' => $user,
        ]);
    }

    public function downloadMultipleFile($uuid = '')
    {
        if ($uuid == '') {
            return redirect()->back()->with('error', 'Gagal mengunduh data');
        }

        $form = ProposalSkripsiForm::where('uuid', $uuid)->first();
        $user = Dosen::where('user_id', Auth::user()->id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->firstOrFail();
        $files = ProposalSkripsi::where('proposal_skripsi_form_id', $form->id)
            ->where(function ($query) use ($user) {
                $query->where('penilai1', $user->id)
                    ->orWhere('penilai2', $user->id)
                    ->orWhere('penilai3', $user->id);
            })
            ->get();

        $zip = new ZipArchive;
        $timestamp = time();
        $zipFileName = 'app/downloads/proposal/' . $timestamp . '_proposal-skripsi_' . $user->nama . '.zip';

        // Ensure the directory exists
        if (!file_exists(storage_path('app/downloads/proposal'))) {
            mkdir(storage_path('app/downloads/proposal'), 0777, true);
        }

        if ($zip->open(storage_path($zipFileName), ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                $filePath = storage_path('app/uploads/proposal/' . $file->file_proposal_random);
                if (file_exists($filePath)) {
                    if ($file->file_proposal_mime == 'application/pdf') {
                        $zip->addFile($filePath, $file->file_proposal . '.pdf');
                    }
                }
            }
            $zip->close();
        }

        return response()->download(storage_path($zipFileName))->deleteFileAfterSend(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'penilai' => ['required', 'numeric'],
            'proposal_id' => ['required'],
            'status' => ['required'],
            'action' => ['required'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:30720']
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('proposal_skripsi')->lockForUpdate()->get();
                $penilai = $request->penilai;
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                $now = date('Y-m-d');

                $proposal = ProposalSkripsi::where('id', $request->proposal_id)->firstOrFail();

                if ($penilai == 1) {
                    $proposal->file_penilai1 = $fileName;
                    $proposal->file_random_penilai1 = $fileNameRandom;
                    $proposal->file_penilai1_mime = $mimeType;
                    $proposal->status_approval_penilai1 = $request->status;
                    $proposal->tanggal_approval_penilai1 = $now;
                    $proposal->save();

                    $file->storeAs('uploads/periksa-proposal', $fileNameRandom);
                } elseif ($penilai == 2) {
                    $proposal->file_penilai2 = $fileName;
                    $proposal->file_random_penilai2 = $fileNameRandom;
                    $proposal->file_penilai2_mime = $mimeType;
                    $proposal->status_approval_penilai2 = $request->status;
                    $proposal->tanggal_approval_penilai2 = $now;
                    $proposal->save();

                    $file->storeAs('uploads/periksa-proposal', $fileNameRandom);
                } elseif ($penilai == 3) {
                    $proposal->file_penilai3 = $fileName;
                    $proposal->file_random_penilai3 = $fileNameRandom;
                    $proposal->file_penilai3_mime = $mimeType;
                    $proposal->status_approval_penilai3 = $request->status;
                    $proposal->tanggal_approval_penilai3 = $now;
                    $proposal->save();

                    $file->storeAs('uploads/periksa-proposal', $fileNameRandom);
                } else {
                    throw new Exception('Gagal mengupload file');
                }

                $proposal = ProposalSkripsi::with('proposalSkripsiForm')->where('id', $request->proposal_id)->firstOrFail();
                if ($proposal->status_approval_penilai1 !== null && $proposal->status_approval_penilai2 !== null && $proposal->status_approval_penilai3 !== null) {
                    $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
                    $pengaturan = Pengaturan::with('pengaturanDetail')
                        ->where('tahun_ajaran_id', $active->id)
                        ->where('program_studi_id', $proposal->proposalSkripsiForm->program_studi_id)
                        ->firstOrFail();
                    $jumlahSetuju = $pengaturan->pengaturanDetail->jumlah_setuju_proposal;
                    $count = 0;
                    if ($proposal->status_approval_penilai1 === 1) {
                        $count++;
                    }
                    if ($proposal->status_approval_penilai2 === 1) {
                        $count++;
                    }
                    if ($proposal->status_approval_penilai3 === 1) {
                        $count++;
                    }

                    if ($count == (int)$jumlahSetuju) {
                        $proposal->status_akhir = 1;
                        $proposal->save();
                    } else {
                        $proposal->status_akhir = false;
                        $proposal->save();
                    }
                }
            });

            return redirect()->back()->with('success', 'Berhasil mengupload file');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupload file')->withInput();
        }
    }

    public function downloadFilePeriksa($uuid, $penilai)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
        $path = '';
        $mime = '';
        $fileName = '';

        if ($penilai == 1) {
            $path = 'uploads/periksa-proposal/' . $data->file_random_penilai1;
            $fileName = $data->file_penilai1;
            $mime = $data->file_penilai1_mime;
        } elseif ($penilai == 2) {
            $path = 'uploads/periksa-proposal/' . $data->file_random_penilai2;
            $fileName = $data->file_penilai2;
            $mime = $data->file_penilai2_mime;
        } elseif ($penilai == 3) {
            $path = 'uploads/periksa-proposal/' . $data->file_random_penilai3;
            $fileName = $data->file_penilai3;
            $mime = $data->file_penilai3_mime;
        } else {
            abort(404);
        }

        if (Storage::exists($path)) {
            // $fileContents = Storage::get($path);
            if ($mime == 'application/pdf') {
                return Storage::download($path, $fileName);
            }
        } else {
            abort(404);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'slug' => ['required'],
            'penilai_proposal' => ['required']
        ]);

        try {
            DB::transaction(function () use ($request) {
                $uuid = $request->input('slug');
                $proposal = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();

                if ($request->penilai_proposal == 1) {
                    $path = 'uploads/periksa-proposal/' . $proposal->file_random_penilai1;
                    if (Storage::exists($path)) {
                        Storage::delete($path);
                        $proposal->file_penilai1 = null;
                        $proposal->file_random_penilai1 = null;
                        $proposal->file_penilai1_mime = null;
                        $proposal->status_approval_penilai1 = null;
                        $proposal->tanggal_approval_penilai1 = null;
                        $proposal->status_akhir = null;
                        $proposal->save();
                    } else {
                        throw new Exception('File not found');
                    }
                } elseif ($request->penilai_proposal == 2) {
                    $path = 'uploads/periksa-proposal/' . $proposal->file_random_penilai2;
                    if (Storage::exists($path)) {
                        Storage::delete($path);
                        $proposal->file_penilai2 = null;
                        $proposal->file_random_penilai2 = null;
                        $proposal->file_penilai2_mime = null;
                        $proposal->status_approval_penilai2 = null;
                        $proposal->tanggal_approval_penilai2 = null;
                        $proposal->status_akhir = null;
                        $proposal->save();
                    } else {
                        throw new Exception('File not found');
                    }
                } elseif ($request->penilai_proposal == 3) {
                    $path = 'uploads/periksa-proposal/' . $proposal->file_random_penilai3;
                    if (Storage::exists($path)) {
                        Storage::delete($path);
                        $proposal->file_penilai3 = null;
                        $proposal->file_random_penilai3 = null;
                        $proposal->file_penilai3_mime = null;
                        $proposal->status_approval_penilai3 = null;
                        $proposal->tanggal_approval_penilai3 = null;
                        $proposal->status_akhir = null;
                        $proposal->save();
                    } else {
                        throw new Exception('File not found');
                    }
                } else {
                    throw new Exception('Penilai not found');
                }
            });

            return redirect()->back()->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal');
        }
    }
}
