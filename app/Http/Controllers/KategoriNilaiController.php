<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\KategoriNilai;
use App\Models\KategoriNilaiDetail;
use App\Models\TahunAjaran;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KategoriNilaiController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = KategoriNilai::with('kategoriNilaiDetail')
            ->where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->get();
        $persentasePembimbing = 0;
        $persentasePenguji = 0;
        $persentaseKetuaSidang = 0;

        foreach ($data as $row) {
            if ($row->user === 'pembimbing') {
                $persentasePembimbing += (int)$row->persentase;
            } elseif ($row->user === 'penguji') {
                $persentasePenguji += (int)$row->persentase;
            } elseif ($row->user === 'ketua_sidang') {
                $persentaseKetuaSidang += (int)$row->persentase;
            }
        }

        return view('pages.prodi.kategori-nilai.kategori-nilai', [
            'title' => 'Kategori Nilai',
            'subtitle' => '',
            'data' => $data,
            'pembimbing' => $persentasePembimbing,
            'penguji' => $persentasePenguji,
            'ketuaSidang' => $persentaseKetuaSidang,
        ]);
    }

    public function create()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        return view('pages.prodi.kategori-nilai.add-kategori-nilai', [
            'title' => 'Kategori Nilai',
            'subtitle' => 'Tambah',
            'prodi' => $prodi->uuid,
        ]);
    }

    public function store(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'program_studi' => ['required'],
            'kategori' => ['required'],
            'persentase' => ['required', 'numeric'],
            'user' => ['required'],
            'action' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();

                KategoriNilai::create([
                    'tahun_ajaran_id' => $active->id,
                    'program_studi_id' => $program->id,
                    'kategori' => $request->kategori,
                    'persentase' => $request->persentase,
                    'user' => $request->user,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('kategori.nilai')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('kategori.nilai');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    public function edit($uuid)
    {
        try {
            $data = KategoriNilai::where('uuid', $uuid)->firstOrFail();
            $isLinked = KategoriNilaiDetail::where('kategori_nilai_id', $data->id)->exists();
            return view('pages.prodi.kategori-nilai.edit-kategori-nilai', [
                'title' => 'Kategori Nilai',
                'subtitle' => 'Edit',
                'data' => $data,
                'isLinked' => $isLinked,
            ]);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'kategori' => ['required'],
            'persentase' => ['required', 'numeric'],
            'user' => ['required'],
            'action' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request, $uuid) {
                DB::table('kategori_nilai')->lockForUpdate()->get();
                $kategori = KategoriNilai::where('uuid', $uuid)->firstOrFail();
                $kategori->kategori = $request->kategori;
                $kategori->persentase = $request->persentase;
                $kategori->user = $request->user;
                $kategori->save();
            });
            return redirect()->route('kategori.nilai')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali')->withInput();
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $kategori = KategoriNilai::where('uuid', $uuid)->firstOrFail();
            $kategori->delete();
            return redirect()->route('kategori.nilai')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
