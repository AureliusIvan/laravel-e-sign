<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriNilai;
use App\Models\KategoriNilaiDetail;
use App\Models\NilaiSidang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KategoriNilaiDetailController extends Controller
{
    public function store(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'slug' => ['required'],
            'detail_kategori' => ['required'],
            'detail_persentase' => ['required', 'numeric'],
            'action' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('kategori_nilai_detail')->lockForUpdate()->get();
                $kategori = KategoriNilai::where('uuid', $request->slug)->firstOrFail();

                KategoriNilaiDetail::create([
                    'kategori_nilai_id' => $kategori->id,
                    'detail_kategori' => $request->detail_kategori,
                    'detail_persentase' => $request->detail_persentase,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    public function fetchRelation($uuid)
    {
        try {
            $data = KategoriNilaiDetail::where('uuid', $uuid)->firstOrFail();
            $isLinked = NilaiSidang::where('kategori_nilai_detail_id', $data->id)->exists();
            return response()->json([
                'data' => $data,
                'is_linked' => $isLinked,
            ]);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function update(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'edit_slug' => ['required'],
            'edit_detail_kategori' => ['required'],
            'edit_detail_persentase' => ['required', 'numeric'],
            'action' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $data = KategoriNilaiDetail::where('uuid', $request->edit_slug)->firstOrFail();
                $data->detail_kategori = $request->edit_detail_kategori;
                $data->detail_persentase = $request->edit_detail_persentase;
                $data->save();
            });
            return redirect()->back()->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali')->withInput();
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'delete_slug' => ['required'],
        ]);

        $uuid = $request->input('delete_slug');

        try {
            $kategori = KategoriNilaiDetail::where('uuid', $uuid)->firstOrFail();
            $kategori->delete();
            return redirect()->back()->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
