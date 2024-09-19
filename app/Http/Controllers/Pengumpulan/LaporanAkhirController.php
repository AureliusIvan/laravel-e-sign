<?php

namespace App\Http\Controllers\Pengumpulan;

use Exception;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\LaporanAkhir;
use Illuminate\Http\Request;
use App\Models\RevisiProposal;
use App\Models\LaporanAkhirForm;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LaporanAkhirController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = LaporanAkhirForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();

        $result = LaporanAkhir::with('laporanAkhirForm')
            ->where('mahasiswa_id', $user->id)
            ->get();
        $pertama = DB::table('pembimbing_mahasiswa')
            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing1')
            ->select(
                'dosen.nama as pembimbing',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->first();
        $kedua = DB::table('pembimbing_mahasiswa')
            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing2')
            ->select(
                'dosen.nama as pembimbing',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->first();
        $bimbinganPembimbingPertama = DB::table('bimbingan')
            ->join('dosen', 'dosen.id', '=', 'bimbingan.dosen_id')
            ->where('mahasiswa_id', $user->id)
            ->where('status', 1)
            ->where('is_expired', 0)
            ->get();
        $jumlahBimbinganPembimbingPertama = count($bimbinganPembimbingPertama);

        $bimbinganPembimbingKedua = DB::table('bimbingan')
            ->join('dosen', 'dosen.id', '=', 'bimbingan.dosen_id')
            ->where('mahasiswa_id', $user->id)
            ->where('is_expired', 0)
            ->get();
        $jumlahBimbinganPembimbingKedua = count($bimbinganPembimbingKedua);
        $pengaturan = Pengaturan::with('pengaturanDetail')
            ->where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->first();
        if (count($result) <= 0) {
            $result = [];
        }
        return view('pages.mahasiswa.laporan-akhir.laporan-akhir', [
            'title' => 'Laporan Skripsi',
            'subtitle' => 'Pengumpulan',
            'data' => $data,
            'result' => $result,
            'pembimbingPertama' => $pertama,
            'jumlahBimbinganPembimbingPertama' => $jumlahBimbinganPembimbingPertama,
            'minimumBimbinganPertama' => $pengaturan->pengaturanDetail->minimum_jumlah_bimbingan,
            'pembimbingKedua' => $kedua,
            'jumlahBimbinganPembimbingKedua' => $jumlahBimbinganPembimbingKedua,
            'minimumBimbinganKedua' => $pengaturan->pengaturanDetail->minimum_jumlah_bimbingan_kedua,
        ]);
    }

    public function fetchRevisiProposal()
    {
        try {
            $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
            $revisi = DB::table('revisi_proposal')
                ->join('proposal_skripsi', 'proposal_skripsi.id', '=', 'revisi_proposal.proposal_skripsi_id')
                ->select('revisi_proposal.uuid', 'revisi_proposal.judul_revisi_proposal')
                ->where('proposal_skripsi.mahasiswa_id', $user->id)
                ->where('proposal_skripsi.status_akhir', 1)
                ->where('proposal_skripsi.is_expired', 0)
                ->where('revisi_proposal.mahasiswa_id', $user->id)
                ->where('revisi_proposal.status_akhir', 1)
                ->get();
            if (!$revisi) {
                throw new Exception('Revisi Proposal not found');
            } else {
                return response()->json([
                    'data' => $revisi,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => ['Gagal mendapatkan data'],
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'revisi_proposal' => ['required'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:30720'],
            'reupload' => ['required'],
            'laporan_id' => ['sometimes', 'required_if:reupload,1'],
            'id_form' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('laporan_akhir')->lockForUpdate()->get();
                $active = TahunAjaran::where('status_aktif', 1)->first();
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $form = LaporanAkhirForm::where('uuid', $request->id_form)->firstOrFail();
                $isPenamaan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->first();
                $revisi = RevisiProposal::where('uuid', $request->revisi_proposal)->firstOrFail();

                if ($isPenamaan->penamaan_laporan == 1) {
                    $format = explode('_', $isPenamaan->pengaturanDetail->penamaan_laporan);
                    $countFormat = count($format);

                    if ($countFormat != 3) {
                        throw new Exception('Format invalid');
                    }

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
                            $fileName = $nim . '_' . $nama . '_' . 'LaporanSkripsi' . '.pdf';
                        } elseif ($secondFormat == 'judul') {
                            $fileName = $nim . '_' . 'LaporanSkripsi' . '_' . $nama . '.pdf';
                        } else {
                            throw new  Exception("Error Processing Request", 1);
                        }
                    } elseif ($firstFormat == 'nama') {
                        if ($secondFormat == 'nim') {
                            $fileName = $nama . '_' . $nim . '_' . 'LaporanSkripsi' . '.pdf';
                        } elseif ($secondFormat == 'judul') {
                            $fileName = $nama . '_' . 'LaporanSkripsi' . '_' . $nim . '.pdf';
                        } else {
                            throw new  Exception("Error Processing Request", 1);
                        }
                    } elseif ($firstFormat == 'judul') {
                        if ($secondFormat == 'nim') {
                            $fileName = 'LaporanSkripsi' . '_' . $nim . '_' . $nama . '.pdf';
                        } elseif ($secondFormat == 'nama') {
                            $fileName = 'LaporanSkripsi' . '_' . $nama . '_' . $nim . '.pdf';
                        } else {
                            throw new  Exception("Error Processing Request", 1);
                        }
                    } else {
                        throw new  Exception("Error Processing Request", 1);
                    }

                    if ($request->reupload == 1) {
                        $laporan = LaporanAkhir::where('uuid', $request->laporan_id)
                            ->where('laporan_akhir_form_id', $form->id)
                            ->where('mahasiswa_id', $user->id)
                            ->firstOrFail();
                        $laporan->file_laporan = $fileName;
                        $laporan->file_laporan_random = $fileNameRandom;
                        $laporan->status = 1;
                        $laporan->status_approval_pembimbing1 = 2;
                        $laporan->status_approval_pembimbing2 = 2;
                        $laporan->status_approval_kaprodi = 2;
                        $laporan->save();
                        $file->storeAs('uploads/laporan-akhir', $fileNameRandom);
                    } else {
                        $pembimbingPertama = DB::table('pembimbing_mahasiswa')
                            ->leftJoin('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing1')
                            ->select(
                                'dosen.id',
                                'dosen.nama as pembimbing',
                            )
                            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
                            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
                            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
                            ->first();
                        $pembimbingKedua = DB::table('pembimbing_mahasiswa')
                            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing2')
                            ->select(
                                'dosen.id',
                                'dosen.nama as pembimbing',
                            )
                            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
                            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
                            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
                            ->first();
                        if (!$pembimbingKedua) {
                            LaporanAkhir::create([
                                'laporan_akhir_form_id' => $form->id,
                                'revisi_proposal_id' => $revisi->id,
                                'mahasiswa_id' => $user->id,
                                'judul_laporan' => $revisi->judul_revisi_proposal,
                                'file_laporan' => $clientName,
                                'file_laporan_random' => $fileNameRandom,
                                'status' => 1,
                                'pembimbing1' => $pembimbingPertama->id,
                                'pembimbing2' => null,
                                'status_approval_pembimbing1' => 2,
                                'status_approval_kaprodi' => 2,
                            ]);
                            $file->storeAs('uploads/laporan-akhir', $fileNameRandom);
                        } else {
                            LaporanAkhir::create([
                                'laporan_akhir_form_id' => $form->id,
                                'revisi_proposal_id' => $revisi->id,
                                'mahasiswa_id' => $user->id,
                                'judul_laporan' => $revisi->judul_revisi_proposal,
                                'file_laporan' => $clientName,
                                'file_laporan_random' => $fileNameRandom,
                                'status' => 1,
                                'pembimbing1' => $pembimbingPertama->id,
                                'pembimbing2' => $pembimbingKedua->id,
                                'status_approval_pembimbing1' => 2,
                                'status_approval_pembimbing2' => 2,
                                'status_approval_kaprodi' => 2,
                            ]);
                            $file->storeAs('uploads/laporan-akhir', $fileNameRandom);
                        }
                    }
                } else {
                    $file = $request->file('file');
                    $clientName = $file->getClientOriginalName();
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();

                    if ($request->reupload == 1) {
                        $laporan = LaporanAkhir::where('uuid', $request->laporan_id)
                            ->where('laporan_akhir_form_id', $form->id)
                            ->where('mahasiswa_id', $user->id)
                            ->firstOrFail();
                        $laporan->file_laporan = $clientName;
                        $laporan->file_laporan_random = $fileNameRandom;
                        $laporan->status = 1;
                        $laporan->status_approval_pembimbing1 = 2;
                        $laporan->status_approval_pembimbing2 = 2;
                        $laporan->status_approval_kaprodi = 2;
                        $laporan->save();
                        $file->storeAs('uploads/laporan-akhir', $fileNameRandom);
                    } else {
                        $pembimbingPertama = DB::table('pembimbing_mahasiswa')
                            ->leftJoin('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing1')
                            ->select(
                                'dosen.id',
                                'dosen.nama as pembimbing',
                            )
                            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
                            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
                            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
                            ->first();
                        $pembimbingKedua = DB::table('pembimbing_mahasiswa')
                            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing2')
                            ->select(
                                'dosen.id',
                                'dosen.nama as pembimbing',
                            )
                            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
                            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
                            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
                            ->first();
                        if (!$pembimbingKedua) {
                            LaporanAkhir::create([
                                'laporan_akhir_form_id' => $form->id,
                                'revisi_proposal_id' => $revisi->id,
                                'mahasiswa_id' => $user->id,
                                'judul_laporan' => $revisi->judul_revisi_proposal,
                                'file_laporan' => $clientName,
                                'file_laporan_random' => $fileNameRandom,
                                'status' => 1,
                                'pembimbing1' => $pembimbingPertama->id,
                                'pembimbing2' => null,
                                'status_approval_pembimbing1' => 2,
                                'status_approval_kaprodi' => 2,
                            ]);
                            $file->storeAs('uploads/laporan-akhir', $fileNameRandom);
                        } else {
                            LaporanAkhir::create([
                                'laporan_akhir_form_id' => $form->id,
                                'revisi_proposal_id' => $revisi->id,
                                'mahasiswa_id' => $user->id,
                                'judul_laporan' => $revisi->judul_revisi_proposal,
                                'file_laporan' => $clientName,
                                'file_laporan_random' => $fileNameRandom,
                                'status' => 1,
                                'pembimbing1' => $pembimbingPertama->id,
                                'pembimbing2' => $pembimbingKedua->id,
                                'status_approval_pembimbing1' => 2,
                                'status_approval_pembimbing2' => 2,
                                'status_approval_kaprodi' => 2,
                            ]);
                            $file->storeAs('uploads/laporan-akhir', $fileNameRandom);
                        }
                    }
                }
            });
            return redirect()->back()->with('success', 'Berhasil mengupload file');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal');
        }

        dd($request->input());
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');

        try {
            $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
            $path = 'uploads/laporan-akhir/' . $data->file_laporan_random;

            if (Storage::exists($path)) {
                if (Storage::delete($path)) {
                    $data->file_laporan = null;
                    $data->file_laporan_random = null;
                    $data->status = 0;
                    $data->save();
                }
            }
            return redirect()->back()->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus file');
        }
    }

    public function getFile($uuid)
    {
        $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/laporan-akhir/' . $data->file_laporan_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_laporan);
        } else {
            abort(404);
        }
    }

    public function filePembimbing1($uuid)
    {
        $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-laporan-akhir/' . $data->file_random_pembimbing1;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_pembimbing1);
        } else {
            abort(404);
        }
    }

    public function filePembimbing2($uuid)
    {
        $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-laporan-akhir/' . $data->file_random_pembimbing2;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_pembimbing2);
        } else {
            abort(404);
        }
    }

    public function fileKaprodi($uuid)
    {
        $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/approve-laporan-akhir/' . $data->file_random_kaprodi;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_kaprodi);
        } else {
            abort(404);
        }
    }
}
