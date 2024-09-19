<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BeritaAcaraController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = DB::table('berita_acara')
            ->select('*')
            ->where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('tanggal_awal', 'asc')
            ->get()
            ->toArray();
        return view('pages.prodi.berita-acara.berita-acara', [
            'title' => 'Berita Acara',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        return view('pages.prodi.berita-acara.add-berita-acara', [
            'title' => 'Berita Acara',
            'subtitle' => '',
            'tahun' => $active->uuid,
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
            'tahun_ajaran' => ['required'],
            'program_studi' => ['required'],
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date'],
            'isi_berita' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $tahun = TahunAjaran::where('uuid', $request->tahun_ajaran)->firstOrFail();
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();
                $dosen = Dosen::where('user_id', Auth::user()->id)->firstOrFail();

                BeritaAcara::create([
                    'tahun_ajaran_id' => $tahun->id,
                    'program_studi_id' => $program->id,
                    'dosen_id' => $dosen->id,
                    'tanggal_awal' => $request->tanggal_awal,
                    'tanggal_akhir' => $request->tanggal_akhir,
                    'isi_berita' => $request->isi_berita,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('berita')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('berita');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    public function edit($uuid)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        try {
            $data = BeritaAcara::where('uuid', $uuid)->firstOrFail();

            return view('pages.prodi.berita-acara.edit-berita-acara', compact('data'), [
                'title' => 'Berita Acara',
                'subtitle' => 'Edit',
                'isLinked' => false,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $validated = $request->validate([
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date'],
            'isi_berita' => ['required'],
        ]);

        try {
            $berita = BeritaAcara::where('uuid', $uuid)->firstOrFail();
            $berita->update($validated);
            return redirect()->route('berita')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $uuid = $request->input('slug');
        try {
            $berita = BeritaAcara::where('uuid', $uuid)->firstOrFail();
            $berita->delete();
            return redirect()->route('berita')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
