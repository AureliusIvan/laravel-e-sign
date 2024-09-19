<?php

namespace App\Http\Controllers\Periksa;

use App\Models\Dosen;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\RevisiProposal;
use App\Models\RevisiProposalForm;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PeriksaRevisiProposalController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = RevisiProposalForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.dosen.periksa-revisi-proposal.periksa-revisi-proposal', [
            'title' => 'Periksa Revisi Proposal',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function show($segment)
    {
        $form = RevisiProposalForm::where('uuid', $segment)->first();
        $user = Dosen::where('user_id', Auth::user()->id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->firstOrFail();
        $data = RevisiProposal::with('revisiProposalForm')
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
            ->where('revisi_proposal_form_id', $form->id)
            ->where(function ($query) use ($user) {
                $query->where('penilai1', $user->id)
                    ->orWhere('penilai2', $user->id)
                    ->orWhere('penilai3', $user->id);
            })
            ->get();
        return view('pages.dosen.periksa-revisi-proposal.detail-periksa-revisi-proposal', [
            'title' => 'Periksa Revisi Proposal',
            'subtitle' => 'Detail',
            'data' => $data,
            'form' => $form,
            'dosen' => $user,
        ]);
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

    public function store(Request $request)
    {
        $request->validate([
            'penilai' => ['required', 'integer'],
            'revisi_id' => ['required'],
            'status' => ['required', 'integer'],
            'action' => ['required'],
            'note' => ['sometimes', 'required_if:status,0,3,4'],
            'file_upload' => ['sometimes', 'required_if:status,1', 'file', 'mimes:pdf', 'max:30720']
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('revisi_proposal')->lockForUpdate()->get();
                if ($request->hasFile('file_upload')) {
                    $file = $request->file('file_upload');
                    $penilai = $request->penilai;
                    $fileName = $file->getClientOriginalName();
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                    $now = date('Y-m-d');
                    $revisi = RevisiProposal::where('uuid', $request->revisi_id)->firstOrFail();

                    if ($penilai == 1) {
                        $path = 'uploads/periksa-revisi-proposal/' . $revisi->file_revisi_random_penilai1;
                        if ($revisi->file_revisi_random_penilai1 != null) {
                            if (Storage::exists($path)) {
                                Storage::delete($path);
                            }
                        }
                        $revisi->file_revisi_penilai1 = $fileName;
                        $revisi->file_revisi_random_penilai1 = $fileNameRandom;
                        $revisi->status_revisi_approval_penilai1 = $request->status;
                        $revisi->tanggal_approval_revisi_penilai1 = $now;
                        $revisi->note_revisi_penilai1 = $request->note;
                        if ($request->status == 1 && $revisi->status_revisi_approval_penilai2 == 3) {
                            $revisi->status_revisi_approval_penilai2 = 2;
                        }
                        $revisi->save();

                        $file->storeAs('uploads/periksa-revisi-proposal', $fileNameRandom);
                    } elseif ($penilai == 2) {
                        $path = 'uploads/periksa-revisi-proposal/' . $revisi->file_revisi_random_penilai2;
                        if ($revisi->file_revisi_random_penilai2 != null) {
                            if (Storage::exists($path)) {
                                Storage::delete($path);
                            }
                        }
                        $revisi->file_revisi_penilai2 = $fileName;
                        $revisi->file_revisi_random_penilai2 = $fileNameRandom;
                        $revisi->status_revisi_approval_penilai2 = $request->status;
                        $revisi->tanggal_approval_revisi_penilai2 = $now;
                        $revisi->note_revisi_penilai2 = $request->note;
                        if ($request->status == 1 && $revisi->status_revisi_approval_penilai3 == 4) {
                            $revisi->status_revisi_approval_penilai3 = 2;
                        }
                        $revisi->save();

                        $file->storeAs('uploads/periksa-revisi-proposal', $fileNameRandom);
                    } elseif ($penilai == 3) {
                        $path = 'uploads/periksa-revisi-proposal/' . $revisi->file_revisi_random_penilai3;
                        if ($revisi->file_revisi_random_penilai3 != null) {
                            if (Storage::exists($path)) {
                                Storage::delete($path);
                            }
                        }
                        $revisi->file_revisi_penilai3 = $fileName;
                        $revisi->file_revisi_random_penilai3 = $fileNameRandom;
                        $revisi->status_revisi_approval_penilai3 = $request->status;
                        $revisi->tanggal_approval_revisi_penilai3 = $now;
                        $revisi->note_revisi_penilai3 = $request->note;

                        if ($request->status == 1) {
                            $revisi->status_akhir = 1;
                        }

                        $revisi->save();

                        $file->storeAs('uploads/periksa-revisi-proposal', $fileNameRandom);
                    } else {
                        throw new Exception('Penilai not found');
                    }
                } elseif (!$request->hasFile('file_upload')) {
                    $penilai = $request->penilai;
                    $revisi = RevisiProposal::where('uuid', $request->revisi_id)->firstOrFail();
                    if ($request->status == 1) {
                        throw new Exception('Unknown error');
                    }

                    if ($penilai == 1) {
                        $path = 'uploads/periksa-revisi-proposal/' . $revisi->file_revisi_random_penilai1;
                        if (Storage::exists($path)) {
                            Storage::delete($path);
                        }
                        $revisi->file_revisi_penilai1 = null;
                        $revisi->file_revisi_random_penilai1 = null;
                        $revisi->status_revisi_approval_penilai1 = $request->status;
                        $revisi->note_revisi_penilai1 = $request->note;
                        $revisi->save();
                    } elseif ($penilai == 2) {
                        $path = 'uploads/periksa-revisi-proposal/' . $revisi->file_revisi_random_penilai2;
                        if (Storage::exists($path)) {
                            Storage::delete($path);
                        }
                        $revisi->file_revisi_penilai2 = null;
                        $revisi->file_revisi_random_penilai2 = null;
                        $revisi->status_revisi_approval_penilai2 = $request->status;
                        $revisi->note_revisi_penilai2 = $request->note;
                        $revisi->save();
                    } elseif ($penilai == 3) {
                        $path = 'uploads/periksa-revisi-proposal/' . $revisi->file_revisi_random_penilai3;
                        if (Storage::exists($path)) {
                            Storage::delete($path);
                        }
                        $revisi->file_revisi_penilai3 = null;
                        $revisi->file_revisi_random_penilai3 = null;
                        $revisi->status_revisi_approval_penilai3 = $request->status;
                        $revisi->note_revisi_penilai3 = $request->note;
                        $revisi->save();
                    } else {
                        throw new Exception('Penilai not found');
                    }
                } else {
                    throw new Exception("Error Processing Request");
                }
            });

            return redirect()->back()->with('success', 'Berhasil');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal. Silahkan coba kembali')->withInput();
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

    public function destroy(Request $request)
    {
        $request->validate([
            'slug' => ['required'],
            'penilai_revisi' => ['required', 'integer'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $data = RevisiProposal::where('uuid', $request->slug)->firstOrFail();
                $penilai = (int)$request->penilai_revisi;

                if ($penilai === 1) {
                    $path = 'uploads/periksa-revisi-proposal/' . $data->file_revisi_random_penilai1;
                    if (Storage::exists($path)) {
                        if (Storage::delete($path)) {
                            $data->file_revisi_penilai1 = null;
                            $data->file_revisi_random_penilai1 = null;
                            $data->status_revisi_approval_penilai1 = 2;
                            $data->tanggal_approval_revisi_penilai1 = null;
                            $data->save();
                        }
                    }
                } elseif ($penilai === 2) {
                    $path = 'uploads/periksa-revisi-proposal/' . $data->file_revisi_random_penilai2;
                    if (Storage::exists($path)) {
                        if (Storage::delete($path)) {
                            $data->file_revisi_penilai2 = null;
                            $data->file_revisi_random_penilai2 = null;
                            $data->status_revisi_approval_penilai2 = 2;
                            $data->tanggal_approval_revisi_penilai2 = null;
                            $data->save();
                        }
                    }
                } elseif ($penilai === 3) {
                    $path = 'uploads/periksa-revisi-proposal/' . $data->file_revisi_random_penilai3;
                    if (Storage::exists($path)) {
                        if (Storage::delete($path)) {
                            $data->file_revisi_penilai3 = null;
                            $data->file_revisi_random_penilai3 = null;
                            $data->status_revisi_approval_penilai3 = 2;
                            $data->tanggal_approval_revisi_penilai3 = null;
                            $data->save();
                        }
                    }
                } else {
                    throw new Exception("Penilai not found");
                }
            });
            return redirect()->back()->with('success', 'File berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal. Silahkan coba kembali')->withInput();
        }
    }
}