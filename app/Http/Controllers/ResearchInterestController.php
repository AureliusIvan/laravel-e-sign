<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\ResearchList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResearchInterestController extends Controller
{
    public function index()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = DB::table('research_list')
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('topik_penelitian')
            ->get()
            ->toArray();
        return view('pages.prodi.research-interest.research-interest', [
            'title' => 'Research',
            'subtitle' => 'Daftar Research',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        return view('pages.prodi.research-interest.add-research-interest', [
            'title' => 'Research',
            'subtitle' => 'Tambah Daftar Research',
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
            'topik_penelitian' => ['required'],
            'kode_penelitian' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();

                ResearchList::create([
                    'program_studi_id' => $program->id,
                    'topik_penelitian' => $request->topik_penelitian,
                    'kode_penelitian'  => $request->kode_penelitian,
                    'deskripsi'        => $request->deskripsi ?? null,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('research')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('research');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    public function edit($uuid)
    {
        try {
            $data = ResearchList::where('uuid', $uuid)->firstOrFail();
            return view('pages.prodi.research-interest.edit-research-interest', compact('data'), [
                'title' => 'Research',
                'subtitle' => 'Edit Daftar Research',
                'isLinked' => false,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'topik_penelitian' => ['required'],
            'kode_penelitian' => ['required'],
        ]);

        try {
            $research = ResearchList::where('uuid', $uuid)->firstOrFail();
            $research->update($request->except('_token', 'id', 'uuid'));
            return redirect()->route('research')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $research = ResearchList::where('uuid', $uuid)->firstOrFail();
            $research->delete();
            return redirect()->route('research')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}