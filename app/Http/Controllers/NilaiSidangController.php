<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\TahunAjaran;
use App\Models\JadwalSidang;
use App\Models\LaporanAkhir;
use Illuminate\Http\Request;
use App\Models\KategoriNilai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NilaiSidangController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $user = Dosen::where('user_id', Auth::user()->id)
            ->select('id', 'uuid', 'nama', 'program_studi_id')
            ->firstOrFail();
        $kategori = KategoriNilai::with('kategoriNilaiDetail')
            ->where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->get();
        $persentasePembimbing = 0;
        $persentasePenguji = 0;
        $persentaseKetuaSidang = 0;

        $kategoriCheck = null;
        foreach ($kategori as $row) {
            if ($row->user === 'pembimbing') {
                $persentasePembimbing += (int)$row->persentase;
            } elseif ($row->user === 'penguji') {
                $persentasePenguji += (int)$row->persentase;
            } elseif ($row->user === 'ketua_sidang') {
                $persentaseKetuaSidang += (int)$row->persentase;
            }
        }

        if ($persentasePembimbing != 100 || $persentasePenguji != 100 || $persentaseKetuaSidang != 100) {
            $kategoriCheck = false;
        } else {
            $kategoriCheck = true;
        }

        $detailCheck = null;
        $kategori = KategoriNilai::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->get();
        $missingDetails = $kategori->filter(function ($item) {
            return !$item->kategoriNilaiDetail()->exists();
        });

        if ($missingDetails->isNotEmpty()) {
            $detailCheck = false;
        } else {
            $detailCheck = true;
        }

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

        return view('pages.dosen.input-nilai.input-nilai', [
            'title' => 'Input Nilai Mahasiswa',
            'subtitle' => '',
            'kategori' => $kategoriCheck,
            'detail' => $detailCheck,
            'data' => $data,
            'dosen' => $user,
        ]);
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

    public function show($uuid)
    {
        $data = JadwalSidang::where('uuid', $uuid)
            ->with('laporanAkhir', function ($query) {
                $query->select('id', 'judul_laporan');
            })
            ->with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
            ->firstOrFail();
        return view('pages.dosen.input-nilai.detail-input-nilai', [
            'title' => 'Input Nilai Mahasiswa',
            'subtitle' => 'Detail',
            'data' => $data,
        ]);
    }
}
