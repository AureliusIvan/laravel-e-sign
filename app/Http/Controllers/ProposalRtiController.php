<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\AreaPenelitian;
use App\Models\ProposalSkripsiRTI;
use App\Models\PembimbingMahasiswa;
use App\Models\ProposalRtiForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\VarDumper\Dumper\esc;

class ProposalRtiController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalRtiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();
        $result = ProposalSkripsiRTI::with('proposalRtiForm')
            ->where('mahasiswa_id', $user->id)
            ->orderBy('proposal_skripsi_rti_form_id', 'desc')
            ->get();
        $pembimbing = PembimbingMahasiswa::where('mahasiswa', $user->id)
            ->where('tahun_ajaran_id', $active->id)
            ->first();

        if (count($result) <= 0) {
            $result = [];
        }

        return view('pages.mahasiswa.proposal-rti.proposal-rti', [
            'title' => 'Pembimbing',
            'subtitle' => 'Proposal RTI',
            'data' => $data,
            'result' => $result,
            'pembimbing' => $pembimbing,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_proposal' => ['required'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:30720'],
            'reupload' => ['required'],
            'proposal_id' => ['sometimes', 'required_if:reupload,1'],
            'id_form' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $active = TahunAjaran::where('status_aktif', 1)->first();
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $form = ProposalRtiForm::where('uuid', $request->id_form)->firstOrFail();
                $pembimbing = PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->where('mahasiswa', $user->id)
                    ->first();
                $pembimbingPertama = null;
                $pembimbingKedua = null;
                if ($pembimbing !== null) {
                    $pembimbingPertama = $pembimbing->pembimbing1;
                    $pembimbingKedua = $pembimbing->pembimbing2;
                } else {
                    throw new Exception('Pembimbing not found');
                }

                $statusPembimbing1 = 2;
                $statusPembimbing2 = null;
                if ($pembimbingKedua != null) {
                    $statusPembimbing2 = 2;
                }

                $isPenamaan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->first();

                if ($isPenamaan->penamaan_proposal == 1) {
                    $format = explode("_", $isPenamaan->pengaturanDetail->penamaan_proposal);
                    $countFormat = count($format);

                    if ($countFormat != 3) {
                        throw new Exception('Format invalid');
                    }

                    $escJudul = esc($request->judul_proposal);
                    $countWordJudul = str_word_count($escJudul);

                    if ($countWordJudul < 4) {
                        throw new Exception('Jumlah kata kurang');
                    } else {
                        $file = $request->file('file');
                        $clientName = $file->getClientOriginalName();
                        $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                        $nim = $user->nim;
                        $nama = $user->nama;

                        if ($nama == trim($nama) && str_contains($nama, ' ')) {
                            $nama = str_replace(' ', '', $nama);
                        }
                        $firstFormat = $format[0];
                        $secondFormat = $format[1];
                        $thridFormat = $format[2];
                        $fileName = '';

                        if ($firstFormat == 'nim') {
                            if ($secondFormat == 'nama') {
                                $fileName = $nim . '_' . $nama . '_' . 'ProposalSkripsi' . '.pdf';
                            } elseif ($secondFormat == 'judul') {
                                $fileName = $nim . '_' . 'ProposalSkripsi' . '_' . $nama . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } elseif ($firstFormat == 'nama') {
                            if ($secondFormat == 'nim') {
                                $fileName = $nama . '_' . $nim . '_' . 'ProposalSkripsi' . '.pdf';
                            } elseif ($secondFormat == 'judul') {
                                $fileName = $nama . '_' . 'ProposalSkripsi' . '_' . $nim . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } elseif ($firstFormat == 'judul') {
                            if ($secondFormat == 'nim') {
                                $fileName = 'ProposalSkripsi' . '_' . $nim . '_' . $nama . '.pdf';
                            } elseif ($secondFormat == 'nama') {
                                $fileName = 'ProposalSkripsi' . '_' . $nama . '_' . $nim . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } else {
                            throw new  Exception("Error Processing Request", 1);
                        }

                        // $pengaturan = Pengaturan::with('pengaturanDetail')
                        //     ->where('tahun_ajaran_id', $active->id)
                        //     ->where('program_studi_id', $user->program_studi_id)
                        //     ->firstOrFail();

                        if ($request->reupload == 1) {
                            $proposalRti = ProposalSkripsiRTI::where('uuid', $request->proposal_id)
                                ->where('proposal_skripsi_rti_form_id', $form->id)
                                ->where('mahasiswa_id', $user->id)
                                ->firstOrFail();
                            $proposalRti->judul_proposal = $request->judul_proposal;
                            $proposalRti->file_proposal = $fileName;
                            $proposalRti->file_proposal_random = $fileNameRandom;
                            $proposalRti->status = 1;
                            $proposalRti->status_approval_pembimbing1 = $statusPembimbing1;
                            $proposalRti->status_approval_pembimbing2 = $statusPembimbing2;
                            $proposalRti->status_akhir = 2;
                            $proposalRti->save();
                            $file->storeAs('uploads/proposal-rti', $fileNameRandom);
                        } else {
                            ProposalSkripsiRTI::create([
                                'proposal_skripsi_rti_form_id' => $form->id,
                                'mahasiswa_id' => $user->id,
                                'judul_proposal' => $request->judul_proposal,
                                'file_proposal' => $fileName,
                                'file_proposal_random' => $fileNameRandom,
                                'status' => 1,
                                'pembimbing1' => $pembimbingPertama,
                                'status_approval_pembimbing1' => $statusPembimbing1,
                                'pembimbing2' => $pembimbingKedua,
                                'status_approval_pembimbing2' => $statusPembimbing2,
                                'status_akhir' => 2,
                            ]);
                            $file->storeAs('uploads/proposal-rti', $fileNameRandom);
                        }
                    }
                } else {
                    $escJudul = esc($request->judul_proposal);
                    $countWordJudul = str_word_count($escJudul);

                    if ($countWordJudul < 4) {
                        throw new Exception('Jumlah kata kurang');
                    } else {
                        $file = $request->file('file');
                        $clientName = $file->getClientOriginalName();
                        $fileNameRandom = date('YmdHis') . '_' . $file->hashName();

                        $pengaturan = Pengaturan::with('pengaturanDetail')
                            ->where('tahun_ajaran_id', $active->id)
                            ->where('program_studi_id', $user->program_studi_id)
                            ->firstOrFail();

                        if ($request->reupload == 1) {
                            $proposalRti = ProposalSkripsiRTI::where('uuid', $request->proposal_id)
                                ->where('proposal_skripsi_rti_form_id', $form->id)
                                ->where('mahasiswa_id', $user->id)
                                ->firstOrFail();
                            $proposalRti->judul_proposal = $request->judul_proposal;
                            $proposalRti->file_proposal = $clientName;
                            $proposalRti->file_proposal_random = $fileNameRandom;
                            $proposalRti->status = 1;
                            $proposalRti->status_approval_pembimbing1 = $statusPembimbing1;
                            $proposalRti->status_approval_pembimbing2 = $statusPembimbing2;
                            $proposalRti->status_akhir = 2;
                            $proposalRti->save();
                            $file->storeAs('uploads/proposal-rti', $fileNameRandom);
                        } else {
                            ProposalSkripsiRTI::create([
                                'proposal_skripsi_rti_form_id' => $form->id,
                                'mahasiswa_id' => $user->id,
                                'judul_proposal' => $request->judul_proposal,
                                'file_proposal' => $clientName,
                                'file_proposal_random' => $fileNameRandom,
                                'status' => 1,
                                'pembimbing1' => $pembimbingPertama,
                                'status_approval_pembimbing1' => $statusPembimbing1,
                                'pembimbing2' => $pembimbingKedua,
                                'status_approval_pembimbing2' => $statusPembimbing2,
                                'status_akhir' => 2,
                            ]);

                            $file->storeAs('uploads/proposal-rti', $fileNameRandom);
                        }
                    }
                }
            });
            return redirect()->back()->with('success', 'Berhasil mengupload file');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupload file');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            DB::transaction(function () use ($uuid) {
                $data = ProposalSkripsiRTI::where('uuid', $uuid)->firstOrFail();
                $path = 'uploads/proposal-rti/' . $data->file_proposal_random;

                if (Storage::exists($path)) {
                    Storage::delete($path);
                    // $data->delete();
                    // New
                    $data->judul_proposal = null;
                    $data->file_proposal = null;
                    $data->file_proposal_random = null;
                    $data->status = 0;
                    $data->save();
                }
            });
            return redirect()->route('proposal.rti')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus file');
        }
    }

    public function dosen()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalRtiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.dosen.periksa-proposal-rti.periksa-proposal-rti', [
            'title' => 'Mahasiswa',
            'subtitle' => 'Approve Proposal RTI',
            'data' => $data
        ]);
    }

    public function dosenShow($segment)
    {
        $form = ProposalRtiForm::where('uuid', $segment)->firstOrFail();
        $user = Dosen::where('user_id', Auth::user()->id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->firstOrFail();
        $data = ProposalSkripsiRTI::with('proposalRtiForm')
            ->where(function ($query) use ($user) {
                $query->where('pembimbing1', $user->id)
                    ->orWhere('pembimbing2', $user->id);
            })
            ->with('pembimbingPertama', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('pembimbingKedua', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
            ->orderBy('updated_at', 'asc')
            ->where('proposal_skripsi_rti_form_id', $form->id)
            ->get();
        return view('pages.dosen.periksa-proposal-rti.detail-periksa-proposal-rti', [
            'title' => 'Mahasiswa',
            'subtitle' => 'Approve Proposal RTI',
            'dosen' => $user,
            'data' => $data,
            'form' => $form,
        ]);
    }

    public function dosenStore(Request $request)
    {
        $request->validate([
            'pembimbing' => ['required', 'integer'],
            'proposal_id' => ['required'],
            'status' => ['required', 'integer'],
            'action' => ['required'],
            'note' => ['sometimes', 'required_if:status,0,3,4'],
        ]);

        // dd($request->all());

        try {
            DB::transaction(function () use ($request) {
                DB::table('proposal_skripsi_rti')->lockForUpdate()->get();
                $pembimbing = $request->pembimbing;
                $proposal = ProposalSkripsiRTI::where('uuid', $request->proposal_id)->firstOrFail();
                $now = date('Y-m-d');

                if ($request->status == 1) {
                    if ($pembimbing == 1) {
                        $proposal->status_approval_pembimbing1 = $request->status;
                        $proposal->tanggal_approval_pembimbing1 = $now;
                        $proposal->note_pembimbing1 = $request->note;
                        if ($proposal->pembimbing2 == null) {
                            $proposal->status_akhir = $request->status;
                        }
                        $proposal->save();
                    } elseif ($pembimbing == 2) {
                        $proposal->status_approval_pembimbing2 = $request->status;
                        $proposal->tanggal_approval_pembimbing2 = $now;
                        $proposal->note_pembimbing2 = $request->note;
                        $proposal->status_akhir = $request->status;
                        $proposal->save();
                    } else {
                        throw new Exception('Pembimbing not found');
                    }
                } elseif ($request->status == 0) {
                    if ($pembimbing == 1) {
                        $proposal->status_approval_pembimbing1 = $request->status;
                        $proposal->note_pembimbing1 = $request->note;
                        $proposal->status_akhir = $request->status;
                        $proposal->save();
                    } elseif ($pembimbing == 2) {
                        $proposal->status_approval_pembimbing2 = $request->status;
                        $proposal->note_pembimbing2 = $request->note;
                        $proposal->status_akhir = $request->status;
                        $proposal->save();
                    } else {
                        throw new Exception('Pembimbing not found');
                    }
                } else {
                    throw new Exception('Status not found');
                }
            });
            return redirect()->back()->with('success', 'Berhasil');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal. Silahkan coba kembali')->withInput();
        }
    }

    public function file($uuid)
    {
        $data = ProposalSkripsiRTI::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/proposal-rti/' . $data->file_proposal_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_proposal);
        } else {
            abort(404);
        }
    }

    public function fileDosen($uuid)
    {
        $data = ProposalSkripsiRTI::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/proposal-rti/' . $data->file_proposal_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_proposal);
        } else {
            abort(404);
        }
    }

    public function dosenDeleteApproval(Request $request)
    {
        $request->validate([
            'pembimbing' => ['required'],
            'slug' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $data = ProposalSkripsiRTI::where('uuid', $request->slug)->firstOrFail();
                $pembimbing = (int)$request->pembimbing;

                if ($pembimbing === 1) {
                    if ($data->pembimbing2 == null) {
                        $data->status_approval_pembimbing1 = 2;
                        $data->tanggal_approval_pembimbing1 = null;
                        $data->note_pembimbing1 = null;
                        $data->status_akhir = 2;
                        $data->save();
                    } else {
                        throw new Exception('Invalid request');
                    }
                } else if ($pembimbing === 2) {
                    $data->status_approval_pembimbing2 = 2;
                    $data->tanggal_approval_pembimbing2 = null;
                    $data->note_pembimbing2 = null;
                    $data->status_akhir = 2;
                    $data->save();
                } else {
                    throw new Exception('Pembimbing not found.');
                }
            });

            return redirect()->back()->with('success', 'Approval berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal. Silahkan coba kembali');
        }
    }
}
