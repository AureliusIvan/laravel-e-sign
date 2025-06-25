<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use App\Models\Mahasiswa;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $data = DB::table('tahun_ajaran')
            ->select('*')
            ->orderBy('tahun', 'asc')
            ->orderByRaw("CASE WHEN semester = 'genap' THEN 1 ELSE 2 END")
            ->get();
        return view('pages.admin.tahun-ajaran.tahun-ajaran', compact('data'), [
            'title' => 'Tahun Ajaran',
            'subtitle' => '',
        ]);
    }

    public function create()
    {
        return view('pages.admin.tahun-ajaran.add-tahun-ajaran', [
            'title' => 'Tahun Ajaran',
            'subtitle' => 'Tambah',
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'tahun' => ['required', 'numeric'],
            'semester' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('tahun_ajaran')->lockForUpdate()->get();

                TahunAjaran::create([
                    'tahun' => $request->tahun,
                    'semester' => $request->semester,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('tahunajaran')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('tahunajaran');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali');
        }
    }

    public function edit($uuid)
    {
        try {
            $data = TahunAjaran::where('uuid', $uuid)->firstOrFail();
            $isLinked = Mahasiswa::where('tahun_ajaran_id', $data->id)->exists();
            if (!$isLinked) {
                $isLinked = BeritaAcara::where('tahun_ajaran_id', $data->id)->exists();
            }

            return view('pages.admin.tahun-ajaran.edit-tahun-ajaran', compact('data'), [
                'title' => 'Tahun Ajaran',
                'subtitle' => 'Edit',
                'isLinked' => $isLinked,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'tahun' => ['required', 'numeric'],
            'semester' => ['required'],
        ]);

        try {
            $tahunajaran = TahunAjaran::where('uuid', $uuid)->firstOrFail();
            $tahunajaran->update($validated);
            return redirect()->route('tahunajaran')->with('success', 'Data berhasil diubah');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $tahunajaran = TahunAjaran::where('uuid', $uuid)->firstOrFail();
            $tahunajaran->delete();
            return redirect()->route('tahunajaran')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }

    public function updateStatus($uuid)
    {
        try {
            TahunAjaran::query()->update(['status_aktif' => false]);

            $tahunajaran = TahunAjaran::where('uuid', $uuid)->firstOrFail();
            $tahunajaran->status_aktif = !$tahunajaran->status_aktif;
            $tahunajaran->save();

            DB::table('mahasiswa')->where('tahun_ajaran_id', '!=', $tahunajaran->id)
                ->update(['status_aktif_skripsi' => 0]);

            DB::table('mahasiswa')->where('tahun_ajaran_id', $tahunajaran->id)
                ->update(['status_aktif_skripsi' => 1]);

            $message = 'Tahun ajaran ' . $tahunajaran->tahun . ' semester ' . ucfirst($tahunajaran->semester) . ' berhasil diaktifkan.';
            return redirect()->back()->with('success', $message);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }
}