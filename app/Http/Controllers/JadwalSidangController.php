<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\JadwalSidang;
use App\Models\LaporanAkhir;
use App\Models\LaporanAkhirForm;
use App\Models\Mahasiswa;
use App\Models\TahunAjaran;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JadwalSidangController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = LaporanAkhirForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.prodi.jadwal-sidang.jadwal-sidang', [
            'title' => 'Buat Jadwal Sidang',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function show($uuid)
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $dosen = Dosen::where('program_studi_id', $user->program_studi_id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->get()
            ->toArray();
        return view('pages.prodi.jadwal-sidang.detail-jadwal-sidang', [
            'title' => 'Buat Jadwal Sidang',
            'subtitle' => '',
            'segment' => $uuid,
            'dosen' => $dosen,
        ]);
    }

    public function dosen()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $user = Dosen::where('user_id', Auth::user()->id)
            ->select('id', 'uuid', 'nama', 'program_studi_id')
            ->firstOrFail();

        $data = JadwalSidang::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where(function ($query) use ($user) {
                $query->where('pembimbing1', $user->id)
                    ->orWhere('pembimbing2', $user->id)
                    ->orWhere('penguji', $user->id)
                    ->orWhere('ketua_sidang', $user->id);
            })
            ->with('laporanAkhir', function ($query) {
                $query->select('id', 'uuid', 'judul_laporan', 'file_kaprodi');
            })
            ->with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
            ->with('pembimbingPertama', function ($query) {
                $query->select('id', 'uuid', 'nama');
            })
            ->with('pembimbingKedua', function ($query) {
                $query->select('id', 'uuid', 'nama');
            })
            ->with('pengujiSidang', function ($query) {
                $query->select('id', 'uuid', 'nama');
            })
            ->with('ketuaSidang', function ($query) {
                $query->select('id', 'uuid', 'nama');
            })
            ->get();
        return view('pages.dosen.jadwal-sidang.jadwal-sidang', [
            'title' => 'Jadwal Sidang',
            'subtitle' => '',
            'data' => $data,
            'dosen' => $user,
        ]);
    }

    public function fetchData($segment)
    {
        try {
            $form = LaporanAkhirForm::where('uuid', $segment)->firstOrFail();
            $data = LaporanAkhir::with('jadwalSidang')
                ->with('mahasiswa', function ($query) {
                    $query->select('id', 'nim', 'nama');
                })
                ->with('pembimbingPertama', function ($query) {
                    $query->select('id', 'uuid', 'nama');
                })
                ->with('pembimbingKedua', function ($query) {
                    $query->select('id', 'uuid', 'nama');
                })
                ->with('jadwalSidang.penguji', function ($query) {
                    $query->select('id', 'uuid', 'nama');
                })
                ->with('jadwalSidang.ketuaSidang', function ($query) {
                    $query->select('id', 'uuid', 'nama');
                })
                ->where('status_akhir', 1)
                ->where('laporan_akhir_form_id', $form->id)
                ->get();

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data',
            ]);
        }
    }

    public function fetchDetail($segment)
    {
        try {
            $data = JadwalSidang::with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
                ->with('pembimbingPertama', function ($query) {
                    $query->select('id', 'uuid', 'nama');
                })
                ->with('pembimbingKedua', function ($query) {
                    $query->select('id', 'uuid', 'nama');
                })
                ->where('uuid', $segment)->firstOrFail();
            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data',
            ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'laporan' => ['required'],
            'jadwal_sidang' => ['required'],
            'ruang_sidang' => ['required'],
            'penguji' => ['required'],
            'ketua_sidang' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            $laporan = LaporanAkhir::where('uuid', $request->laporan)->firstOrFail();
            $jadwalPembimbing1 = JadwalSidang::where('jadwal_sidang', $request->jadwal_sidang)
                ->where('pembimbing1', $laporan->pembimbing1)
                ->get();
            $jadwalPembimbing2 = null;
            if ($laporan->pembimbing2 != null) {
                $jadwalPembimbing2 = JadwalSidang::where('jadwal_sidang', $request->jadwal_sidang)
                    ->where('pembimbing2', $laporan->pembimbing2)
                    ->get();
            }
            $jadwalPenguji = JadwalSidang::where('jadwal_sidang', $request->jadwal_sidang)
                ->where('penguji', $request->penguji)
                ->get();
            $jadwalKetuaSidang = JadwalSidang::where('jadwal_sidang', $request->jadwal_sidang)
                ->where('ketua_sidang', $request->ketua_sidang)
                ->get();

            if (count($jadwalPembimbing1) > 0 || count($jadwalPembimbing2) > 0 || count($jadwalPenguji) > 0 || count($jadwalKetuaSidang) > 0) {
                $message = '';
                if (count($jadwalPembimbing1) > 0) {
                    $message .= 'Pembimbing 1, ';
                }
                if (count($jadwalPembimbing2) > 0) {
                    $message .= 'Pembimbing 2, ';
                }
                if (count($jadwalPenguji) > 0) {
                    $message .= 'Penguji, ';
                }
                if (count($jadwalKetuaSidang) > 0) {
                    $message .= 'Ketua Sidang, ';
                }
                $message .= 'memiliki jadwal pada waktu yang ditentukan.';
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ]);
            } else {
                DB::transaction(function () use ($request) {
                    $laporan = LaporanAkhir::where('uuid', $request->laporan)->firstOrFail();
                    $deadline = new DateTime($request->jadwal_sidang);
                    $deadline->setTime(23, 59, 59);
                    $deadline->modify('+2 weeks');
                    $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
                    $user = Mahasiswa::where('id', $laporan->mahasiswa_id)->firstOrFail();

                    JadwalSidang::create([
                        'tahun_ajaran_id' => $active->id,
                        'program_studi_id' => $user->program_studi_id,
                        'laporan_akhir_id' => $laporan->id,
                        'mahasiswa_id' => $laporan->mahasiswa_id,
                        'jadwal_sidang' => $request->jadwal_sidang,
                        'ruang_sidang' => $request->ruang_sidang,
                        'pembimbing1' => $laporan->pembimbing1,
                        'pembimbing2' => $laporan->pembimbing2,
                        'ketua_sidang' => $request->ketua_sidang,
                        'penguji' => $request->penguji,
                        'pengumpulan_laporan_dibuka' => $request->jadwal_sidang,
                        'pengumpulan_laporan_ditutup' => $deadline,
                    ]);
                });
                return response()->json([
                    'status' => 'success',
                    'message' => 'Jadwal sidang berhasil dibuat',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan jadwal sidang. Silahkan coba kembali',
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            DB::transaction(function () use ($request) {
                $jadwal = JadwalSidang::where('uuid', $request->slug)->firstOrFail();
                $jadwal->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal sidang berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus jadwal sidang. Silahkan coba kembali',
            ]);
        }
    }
}
