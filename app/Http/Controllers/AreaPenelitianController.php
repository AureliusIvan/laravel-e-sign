<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\ResearchList;
use Illuminate\Http\Request;
use App\Models\AreaPenelitian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AreaPenelitianImport;
use App\Exports\AreaPenelitianTemplateExport;
use App\Models\KodePenelitianProposal;

class AreaPenelitianController extends Controller
{
    public function index()
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $data = AreaPenelitian::with('researchList')->get();
        return view('pages.prodi.area-penelitian.area-penelitian', [
            'title' => 'Research',
            'subtitle' => 'Area Penelitian',
            'data' => $data,
        ]);
    }

    public function mahasiswa()
    {
        $data = AreaPenelitian::with('researchList')->get();
        return view('pages.mahasiswa.area-penelitian.area-penelitian', [
            'title' => 'Area Penelitian',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $research = ResearchList::where('program_studi_id', $user->program_studi_id)->get();
        return view('pages.prodi.area-penelitian.add-area-penelitian', [
            'title' => 'Research',
            'subtitle' => 'Tambah Area Penelitian',
            'research' => $research,
        ]);
    }

    public function store(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'topik_penelitian' => ['required'],
            'kode_area_penelitian' => ['required'],
            'keterangan' => ['required'],
            'action' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $research = ResearchList::where('uuid', $request->topik_penelitian)->firstOrFail();

                AreaPenelitian::create([
                    'research_list_id' => $research->id,
                    'kode_area_penelitian'  => $request->kode_area_penelitian,
                    'keterangan'        => $request->keterangan,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('areapenelitian')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('areapenelitian');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    public function edit($uuid)
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = AreaPenelitian::with('researchList')->where('uuid', $uuid)->firstOrFail();
        $research = ResearchList::where('program_studi_id', $user->program_studi_id)->get();
        $isLinked = KodePenelitianProposal::where('area_penelitian_id', $data->id)->exists();

        return view('pages.prodi.area-penelitian.edit-area-penelitian', [
            'title' => 'Research',
            'subtitle' => 'Edit Area Penelitian',
            'data' => $data,
            'research' => $research,
            'isLinked' => $isLinked,
        ]);
    }

    public function update(Request $request, $uuid)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'topik_penelitian' => ['required'],
            'kode_area_penelitian' => ['required'],
            'keterangan' => ['required'],
            'action' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request, $uuid) {
                $penelitian = AreaPenelitian::where('uuid', $uuid)->firstOrFail();
                DB::table('area_penelitian')->lockForUpdate()->get();
                $research = ResearchList::where('uuid', $request->topik_penelitian)->firstOrFail();

                $penelitian->research_list_id = $research->id;
                $penelitian->kode_area_penelitian = $request->kode_area_penelitian;
                $penelitian->keterangan = $request->keterangan;
                $penelitian->save();
            });

            return redirect()->route('areapenelitian')->with('success', 'Data berhasil diubah');;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $penelitian = AreaPenelitian::where('uuid', $uuid)->firstOrFail();
            $penelitian->delete();
            return redirect()->route('areapenelitian')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }

    public function exportTemplate()
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        return Excel::download(new AreaPenelitianTemplateExport, 'area_penelitian_template.xlsx');
    }

    public function import(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:30720']
        ]);

        try {
            Excel::import(new AreaPenelitianImport, $request->file('file'));
            // return redirect()->back()->with('success', 'Data berhasil diimport');
            return response()->json(['message' => 'File uploaded successfully']);
        } catch (\Exception $e) {
            // return redirect()->back()->with('error', 'Gagal mengimport data. Pastikan anda menggunakan template yang disediakan.');
            return response()->json(['message' => 'File is failed to upload']);
        }
    }

    public function export()
    {
        // 
    }
}