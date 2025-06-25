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
}
