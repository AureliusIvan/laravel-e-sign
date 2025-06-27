<?php

namespace App\Http\Controllers\Periksa;

use App\Http\Controllers\SignatureController;
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
//            ->where('program_studi_id', $user->program_studi_id)
//            ->where('ditutup', '<', $now)
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
//            ->where('proposal_skripsi_form_id', $form->id)
//            ->where(function ($query) use ($user) {
//                $query->where('penilai1', $user->id)
//                    ->orWhere('penilai2', $user->id)
//                    ->orWhere('penilai3', $user->id);
//            })
//            ->where(function ($query) {
//                $query->where('penilai1', '!=', null)
//                    ->where('penilai2', '!=', null)
//                    ->where('penilai3', '!=', null);
//            })
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
        // Base validation rules
        $rules = [
            'penilai' => ['required', 'numeric'],
            'proposal_id' => ['required'],
            'status' => ['required'],
            'action' => ['required'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:30720']
        ];

        // If status is rejection (0), make comment mandatory
        if ($request->status == '0') {
            $rules['rejection_comment'] = ['required', 'string', 'min:10'];
        }

        $request->validate($rules);

        try {
            DB::transaction(function () use ($request) {
                DB::table('proposal_skripsi')->lockForUpdate()->get();
                $penilai = $request->penilai;
                $now = date('Y-m-d');

                $proposal = ProposalSkripsi::where('id', $request->proposal_id)->firstOrFail();

                // Base approval fields
                $penilaiFields = [
                    'status_approval_penilai' . $penilai => $request->status,
                    'tanggal_approval_penilai' . $penilai => $now,
                ];

                // Add rejection comment if status is rejection (0)
                if ($request->status == '0' && $request->has('rejection_comment')) {
                    $penilaiFields['rejection_comment_penilai' . $penilai] = $request->rejection_comment;
                }

                // Handle file upload if provided (optional)
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $fileName = $file->getClientOriginalName();
                    $mimeType = $file->getClientMimeType();
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                    
                    $penilaiFields['file_penilai' . $penilai] = $fileName;
                    $penilaiFields['file_random_penilai' . $penilai] = $fileNameRandom;
                    $penilaiFields['file_penilai' . $penilai . '_mime'] = $mimeType;
                }

                // Check if the penilai is valid
                if (in_array($penilai, [1, 2, 3])) {
                    // Update proposal fields dynamically
                    foreach ($penilaiFields as $field => $value) {
                        $proposal->{$field} = $value;
                    }

                    $proposal->save();

                    // Store the file if uploaded
                    if ($request->hasFile('file')) {
                        $file->storeAs('uploads/periksa-proposal', $fileNameRandom);
                    }
                } else {
                    throw new Exception('Gagal menyimpan data');
                }


                // Refresh proposal data and update status_akhir
                $proposal = ProposalSkripsi::with('proposalSkripsiForm')->where('id', $request->proposal_id)->firstOrFail();
                
                // If current evaluation is a rejection, immediately set status to rejected
                if ($request->status == '0') {
                    $proposal->status_akhir = 0;
                    $proposal->save();
                } else {
                    // Check if any existing evaluation is a rejection
                    $hasRejection = ($proposal->status_approval_penilai1 === 0) || 
                                   ($proposal->status_approval_penilai2 === 0) || 
                                   ($proposal->status_approval_penilai3 === 0);
                    
                    if ($hasRejection) {
                        $proposal->status_akhir = 0;
                        $proposal->save();
                    } elseif ($proposal->status_approval_penilai1 !== null && $proposal->status_approval_penilai2 !== null && $proposal->status_approval_penilai3 !== null) {
                        // All evaluators have completed their reviews and none rejected
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

                        if ($count >= (int)$jumlahSetuju) {
                            $proposal->status_akhir = 1;
                            $proposal->save();
                        } else {
                            $proposal->status_akhir = 0;
                            $proposal->save();
                        }
                    }
                    // If not all evaluations are complete and no rejections, leave status_akhir as null (in progress)
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
                        $proposal->rejection_comment_penilai1 = null;
                        
                        // Recalculate status_akhir after deletion
                        $this->recalculateProposalStatus($proposal);
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
                        $proposal->rejection_comment_penilai2 = null;
                        
                        // Recalculate status_akhir after deletion
                        $this->recalculateProposalStatus($proposal);
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
                        $proposal->rejection_comment_penilai3 = null;
                        
                        // Recalculate status_akhir after deletion
                        $this->recalculateProposalStatus($proposal);
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

    /**
     * Recalculate proposal status based on current evaluations
     */
    private function recalculateProposalStatus($proposal)
    {
        // Check if any evaluation is a rejection
        $hasRejection = ($proposal->status_approval_penilai1 === 0) || 
                       ($proposal->status_approval_penilai2 === 0) || 
                       ($proposal->status_approval_penilai3 === 0);
        
        if ($hasRejection) {
            $proposal->status_akhir = 0;
        } elseif ($proposal->status_approval_penilai1 !== null && $proposal->status_approval_penilai2 !== null && $proposal->status_approval_penilai3 !== null) {
            // All evaluators have completed their reviews and none rejected
            $active = TahunAjaran::where('status_aktif', 1)->first();
            if ($active) {
                $pengaturan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $proposal->proposalSkripsiForm->program_studi_id)
                    ->first();
                
                if ($pengaturan && $pengaturan->pengaturanDetail) {
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

                    if ($count >= (int)$jumlahSetuju) {
                        $proposal->status_akhir = 1;
                    } else {
                        $proposal->status_akhir = 0;
                    }
                }
            }
        } else {
            // Not all evaluations are complete and no rejections, leave as in progress
            $proposal->status_akhir = null;
        }
    }
}
