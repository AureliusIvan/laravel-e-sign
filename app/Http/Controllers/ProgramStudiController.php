<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgramStudiController extends Controller
{
    public function index()
    {
        $data = ProgramStudi::all();
        return view('pages.admin.program-studi.program-studi', compact('data'), [
            'title' => 'Program Studi',
            'subtitle' => '',
        ]);
    }

    // Return add view
    public function create()
    {
        return view('pages.admin.program-studi.add-program-studi', [
            'title' => 'Program Studi',
            'subtitle' => 'Tambah'
        ]);
    }

    // Storing new data
    public function store(Request $request)
    {
        // Check if the authenticaed user is true
        if (Auth::user()->role !== 'admin') {
            abort(404);
        }

        // Validated user input
        $request->validate([
            'program_studi' => 'required',
            'action' => 'required',
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('program_studi')->lockForUpdate()->get();

                ProgramStudi::create([
                    'program_studi' => $request->program_studi,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('programstudi')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('programstudi');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    // Fetch specific data
    public function edit($uuid)
    {
        try {
            $program = ProgramStudi::where('uuid', $uuid)->firstorFail();

            $isLinked = Dosen::where('program_studi_id', $program->id)->exists();
            if (!$isLinked) {
                $isLinked = Mahasiswa::where('program_studi_id', $program->id)->exists();
            }
            return view('pages.admin.program-studi.edit-program-studi', compact('program'), [
                'title' => 'Program Studi',
                'subtitle' => 'Edit',
                'isLinked' => $isLinked,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    // Edit data
    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'program_studi' => 'required',
        ]);

        try {
            $programStudi = ProgramStudi::where('uuid', $uuid)->firstOrFail();
            $programStudi->update($validated);
            return redirect()->route('programstudi')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    // Delete data
    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $programStudi = ProgramStudi::where('uuid', $uuid)->firstOrFail();
            $programStudi->delete();
            return redirect()->route('programstudi')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
