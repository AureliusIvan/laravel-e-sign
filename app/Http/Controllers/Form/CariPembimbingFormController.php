<?php

namespace App\Http\Controllers\Form;

use App\Models\Dosen;
use App\Models\TahunAjaran;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BeritaAcara;
use App\Models\PermintaanMahasiswa;
use App\Models\PermintaanMahasiswaForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CariPembimbingFormController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = PermintaanMahasiswaForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.prodi.form.cari-pembimbing.cari-pembimbing', [
            'title' => 'Form',
            'subtitle' => 'Cari Pembimbing',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        return view('pages.prodi.form.cari-pembimbing.add-cari-pembimbing', [
            'title' => 'Form',
            'subtitle' => 'Tambah Cari Pembimbing',
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
            'judul_form' => ['required'],
            'keterangan' => ['required'],
            'dibuka' => ['required'],
            'ditutup' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $tahun = TahunAjaran::where('uuid', $request->tahun_ajaran)->firstOrFail();
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();
                $dibuka = date('Y-m-d H:i:s', strtotime($request->dibuka));
                $ditutup = date('Y-m-d H:i:s', strtotime($request->ditutup));

                PermintaanMahasiswaForm::create([
                    'tahun_ajaran_id' => $tahun->id,
                    'program_studi_id' => $program->id,
                    'judul_form' => $request->judul_form,
                    'keterangan' => $request->keterangan,
                    'dibuka' => $dibuka,
                    'ditutup' => $ditutup,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('caripembimbing.form')->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('caripembimbing.form');
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
            $data = PermintaanMahasiswaForm::where('uuid', $uuid)->firstOrFail();
            $isLinked = PermintaanMahasiswa::where('permintaan_mahasiswa_form_id', $data->id)->exists();

            return view('pages.prodi.form.cari-pembimbing.edit-cari-pembimbing', compact('data'), [
                'title' => 'Form',
                'subtitle' => 'Edit Cari Pembimbing',
                'isLinked' => $isLinked,
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

        $request->validate([
            'judul_form' => ['required'],
            'keterangan' => ['required'],
            'dibuka' => ['required'],
            'ditutup' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request, $uuid) {
                DB::table('permintaan_mahasiswa_form')->lockForUpdate()->get();
                $form = PermintaanMahasiswaForm::where('uuid', $uuid)->firstOrFail();

                $dibuka = date('Y-m-d H:i:s', strtotime($request->dibuka));
                $ditutup = date('Y-m-d H:i:s', strtotime($request->ditutup));

                $form->judul_form = $request->judul_form;
                $form->keterangan = $request->keterangan;
                $form->dibuka = $dibuka;
                $form->ditutup = $ditutup;
                $form->save();
            });
            return redirect()->route('caripembimbing.form')->with('success', 'Data berhasil diubah');
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
            $form = PermintaanMahasiswaForm::where('uuid', $uuid)->firstOrFail();
            $form->delete();
            return redirect()->route('caripembimbing.form')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
