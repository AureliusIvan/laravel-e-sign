<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;
        if ($role === 'admin') {
            return redirect()->route('dashboard.admin');
        } elseif ($role === 'mahasiswa') {
            return redirect()->route('dashboard.mahasiswa');
        } elseif ($role === 'dosen') {
            return redirect()->route('dashboard.dosen');
        } elseif ($role === 'kaprodi' || $role === 'sekprodi') {
            return redirect()->route('dashboard.prodi');
        } else {
            return redirect()->route('logout');
        }
    }

    public function admin()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $data = DB::table('berita_acara')
            ->select('*')
            ->where('tahun_ajaran_id', $active->id)
            ->orderBy('tanggal_awal', 'asc')
            ->get()
            ->toArray();
        return view('pages.admin.dashboard', [
            'title' => 'Dashboard',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function mahasiswa()
    {
        $mahasiswa = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        if ($mahasiswa->status_aktif_skripsi === 0) {
            return view('pages.mahasiswa.disabled', [
                'title' => 'Skripsi',
                'subtitle' => '',
            ]);
        } else {
            $active = TahunAjaran::where('status_aktif', 1)->first();
            $data = DB::table('berita_acara')
                ->select('*')
                ->where('tahun_ajaran_id', $active->id)
                ->orderBy('tanggal_awal', 'asc')
                ->get()
                ->toArray();
            return view('pages.mahasiswa.dashboard', [
                'title' => 'Dashboard',
                'subtitle' => '',
                'data' => $data,
            ]);
        }
    }

    public function dosen()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $data = DB::table('berita_acara')
            ->select('*')
            ->where('tahun_ajaran_id', $active->id)
            ->orderBy('tanggal_awal', 'asc')
            ->get()
            ->toArray();
        return view('pages.dosen.dashboard', [
            'title' => 'Dashboard',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function prodi()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $data = DB::table('berita_acara')
            ->select('*')
            ->where('tahun_ajaran_id', $active->id)
            ->orderBy('tanggal_awal', 'asc')
            ->get()
            ->toArray();
        return view('pages.prodi.dashboard', [
            'title' => 'Dashboard',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function daftarSkripsi()
    {
        $proposals = DB::table('proposal_skripsi as ps')
            ->join('mahasiswa as m', 'ps.mahasiswa_id', '=', 'm.id')
            ->join('proposal_skripsi_form as psf', 'ps.proposal_skripsi_form_id', '=', 'psf.id')
            ->join('program_studi as prog', 'm.program_studi_id', '=', 'prog.id')
            ->join('tahun_ajaran as ta', 'psf.tahun_ajaran_id', '=', 'ta.id')
            ->leftJoin('dosen as d1', 'ps.penilai1', '=', 'd1.id')
            ->leftJoin('dosen as d2', 'ps.penilai2', '=', 'd2.id')
            ->leftJoin('dosen as d3', 'ps.penilai3', '=', 'd3.id')
            ->select(
                'ps.uuid',
                'ps.judul_proposal',
                'ps.judul_proposal_en',
                'ps.status',
                'ps.status_approval_penilai1',
                'ps.status_approval_penilai2', 
                'ps.status_approval_penilai3',
                'ps.status_akhir',
                'ps.tanggal_approval_penilai1',
                'ps.tanggal_approval_penilai2',
                'ps.tanggal_approval_penilai3',
                'ps.created_at',
                'm.nama as mahasiswa_nama',
                'm.nim',
                'prog.program_studi',
                'ta.tahun',
                'ta.semester',
                'psf.judul_form',
                'd1.nama as penilai1_nama',
                'd2.nama as penilai2_nama',
                'd3.nama as penilai3_nama'
            )
            ->orderBy('ps.created_at', 'desc')
            ->get();

        return view('pages.admin.daftar-skripsi.daftar-skripsi', [
            'title' => 'Daftar Skripsi',
            'subtitle' => '',
            'proposals' => $proposals,
        ]);
    }
}
