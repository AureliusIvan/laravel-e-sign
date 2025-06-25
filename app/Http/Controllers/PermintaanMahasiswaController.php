<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PembimbingMahasiswa;
use App\Models\PermintaanMahasiswa;
use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanMahasiswaForm;
use App\Models\ProposalSkripsi;
use App\Models\ProposalSkripsiRTI;
use Illuminate\Support\Facades\Storage;

class PermintaanMahasiswaController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = PermintaanMahasiswaForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();
        $result = DB::table('permintaan_mahasiswa')
            ->join('permintaan_mahasiswa_form', 'permintaan_mahasiswa_form.id', '=', 'permintaan_mahasiswa.permintaan_mahasiswa_form_id')
            ->join('mahasiswa', 'mahasiswa.id', '=', 'permintaan_mahasiswa.mahasiswa_id')
            ->join('research_list', 'research_list.id', '=', 'permintaan_mahasiswa.research_list_id')
            ->select('mahasiswa.nama', 'mahasiswa.nim', 'research_list.topik_penelitian', 'permintaan_mahasiswa.uuid', 'permintaan_mahasiswa.status', 'permintaan_mahasiswa.uuid', 'permintaan_mahasiswa.status_pembimbing', 'permintaan_mahasiswa.created_at')
            ->where('permintaan_mahasiswa.dosen_id', $user->id)
            ->where('permintaan_mahasiswa_form.tahun_ajaran_id', $active->id)
            ->where('permintaan_mahasiswa_form.program_studi_id', $user->program_studi_id)
            ->orderBy('permintaan_mahasiswa.status', 'desc')
            // New
            ->orderBy('permintaan_mahasiswa.created_at', 'asc')
            // 
            ->get()
            ->toArray();
        $countPembimbingPertama = 0;
        $countPembimbingKedua = 0;

        $pembimbingPertama = PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('pembimbing1', $user->id)
            ->get();
        $pembimbingKedua = PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('pembimbing2', $user->id)
            ->get();
        if ($pembimbingPertama) {
            $countPembimbingPertama = count($pembimbingPertama);
        }
        if ($pembimbingKedua) {
            $countPembimbingKedua = count($pembimbingKedua);
        }

        return view('pages.dosen.permintaan.mahasiswa', [
            'title' => 'Mahasiswa',
            'subtitle' => 'Permintaan Menjadi Pembimbing',
            'data' => $data,
            'result' => $result,
            'totalPertama' => $countPembimbingPertama,
            'totalKedua' => $countPembimbingKedua,
        ]);
    }

    public function show($uuid)
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = DB::table('permintaan_mahasiswa')
            ->join('permintaan_mahasiswa_form', 'permintaan_mahasiswa_form.id', '=', 'permintaan_mahasiswa.permintaan_mahasiswa_form_id')
            ->join('mahasiswa', 'mahasiswa.id', '=', 'permintaan_mahasiswa.mahasiswa_id')
            ->join('research_list', 'research_list.id', '=', 'permintaan_mahasiswa.research_list_id')
            ->select('mahasiswa.nama', 'mahasiswa.nim', 'research_list.topik_penelitian', 'permintaan_mahasiswa.uuid', 'permintaan_mahasiswa.status_pembimbing', 'permintaan_mahasiswa.status', 'permintaan_mahasiswa.note_mahasiswa', 'permintaan_mahasiswa.note_dosen', 'permintaan_mahasiswa.is_rti', 'permintaan_mahasiswa.is_uploaded', 'permintaan_mahasiswa.file_pendukung', 'permintaan_mahasiswa.file_pendukung_random', 'permintaan_mahasiswa.created_at')
            ->where('permintaan_mahasiswa.uuid', $uuid)
            ->where('permintaan_mahasiswa_form.tahun_ajaran_id', $active->id)
            ->where('permintaan_mahasiswa_form.program_studi_id', $user->program_studi_id)
            ->orderBy('permintaan_mahasiswa.status', 'desc')
            ->first();

        // New 05-09-2024
        $permintaan = PermintaanMahasiswa::where('uuid', $uuid)->firstOrFail();
        $form = PermintaanMahasiswaForm::where('id', $permintaan->permintaan_mahasiswa_form_id)->firstOrFail();

        return view('pages.dosen.permintaan.detail-mahasiswa', [
            'title' => 'Mahasiswa',
            'subtitle' => 'Detail Permintaan Menjadi Pembimbing',
            'data' => $data,
            'form' => $form,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'permintaan_mahasiswa' => ['required'],
            'status' => ['required'],
            'note_dosen' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('pembimbing_mahasiswa')->lockForUpdate()->get();
                $permintaan = PermintaanMahasiswa::where('uuid', $request->permintaan_mahasiswa)->firstOrFail();
                $permintaan->update([
                    'status' => $request->status,
                    'note_dosen' => $request->note_dosen,
                ]);

                if ($request->status == 1) {
                    $active = TahunAjaran::where('status_aktif', 1)->first();
                    $prodi = PermintaanMahasiswa::with('permintaanMahasiswaForm')
                        ->where('uuid', $request->permintaan_mahasiswa)->firstOrFail();
                    if ($permintaan->status_pembimbing === 1) {
                        $data = DB::table('pembimbing_mahasiswa')
                            ->where('mahasiswa', $permintaan->mahasiswa_id)
                            ->first();
                        if (!$data) {
                            PembimbingMahasiswa::create([
                                'tahun_ajaran_id' => $active->id,
                                'program_studi_id' => $prodi->permintaanMahasiswaForm->program_studi_id,
                                'mahasiswa' => $permintaan->mahasiswa_id,
                                'pembimbing1' => $permintaan->dosen_id,
                                'pembimbing2' => null,
                            ]);
                        } else {
                            return redirect()->back()->with('error', 'Gagal membalas permintaan. Silahkan coba kembali')->withInput();
                        }
                    } elseif ($permintaan->status_pembimbing === 2) {
                        $data = PembimbingMahasiswa::where('mahasiswa', $permintaan->mahasiswa_id)->firstOrFail();
                        $data->update([
                            'pembimbing2' => $permintaan->dosen_id,
                        ]);
                        $data->save();
                    } else {
                        return redirect()->back()->with('error', 'Gagal membalas permintaan. Silahkan coba kembali')->withInput();
                    }
                }
                $permintaan->save();
            });

            return redirect()->route('permintaan.mahasiswa')->with('success', 'Berhasil membalas permintaan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membalas permintaan. Silahkan coba kembali')->withInput();
        }
    }

    public function getFile($uuid)
    {
        $data = PermintaanMahasiswa::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/pendukung/' . $data->file_pendukung_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_pendukung);
        } else {
            abort(404);
        }
    }
}
