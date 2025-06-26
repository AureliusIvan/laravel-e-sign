<?php

namespace App\Http\Controllers\Form;

use App\Models\Dosen;
use App\Models\TahunAjaran;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProposalSkripsiForm;
use App\Http\Controllers\Controller;
use App\Models\ProposalSkripsi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProposalSkripsiFormController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.prodi.form.proposal-skripsi.proposal-form', [
            'title' => 'Form',
            'subtitle' => 'Skripsi',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        return view('pages.prodi.form.proposal-skripsi.add-proposal-form', [
            'title' => 'Form',
            'subtitle' => 'Tambah Form Skripsi',
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
            'deadline_penilaian' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $tahun = TahunAjaran::where('uuid', $request->tahun_ajaran)->firstOrFail();
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();
                $dibuka = date('Y-m-d H:i:s', strtotime($request->dibuka));
                $ditutup = date('Y-m-d H:i:s', strtotime($request->ditutup));
                $deadline = date('Y-m-d H:i:s', strtotime($request->deadline_penilaian));

                ProposalSkripsiForm::create([
                    'tahun_ajaran_id' => $tahun->id,
                    'program_studi_id' => $program->id,
                    'judul_form' => $request->judul_form,
                    'keterangan' => $request->keterangan,
                    'dibuka' => $dibuka,
                    'ditutup' => $ditutup,
                    'deadline_penilaian' => $deadline,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('proposal.skripsi.form')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('proposal.skripsi.form');
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
            $data = ProposalSkripsiForm::where('uuid', $uuid)->firstOrFail();
            $isLinked = ProposalSkripsi::where('proposal_skripsi_form_id', $data->id)->exists();

            return view('pages.prodi.form.proposal-skripsi.edit-proposal-form', [
                'title' => 'Form',
                'subtitle' => 'Edit Form Skripsi',
                'data' => $data,
                'isLinked' => $isLinked,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'judul_form' => ['required'],
            'keterangan' => ['required'],
            'dibuka' => ['required'],
            'ditutup' => ['required'],
            'deadline_penilaian' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request, $uuid) {
                DB::table('permintaan_mahasiswa_form')->lockForUpdate()->get();
                $form = ProposalSkripsiForm::where('uuid', $uuid)->firstOrFail();

                $dibuka = date('Y-m-d H:i:s', strtotime($request->dibuka));
                $ditutup = date('Y-m-d H:i:s', strtotime($request->ditutup));
                $deadline = date('Y-m-d H:i:s', strtotime($request->deadline_penilaian));

                $form->judul_form = $request->judul_form;
                $form->keterangan = $request->keterangan;
                $form->dibuka = $dibuka;
                $form->ditutup = $ditutup;
                $form->deadline_penilaian = $deadline;
                $form->save();
            });
            return redirect()->route('proposal.skripsi.form')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $form = ProposalSkripsiForm::where('uuid', $uuid)->firstOrFail();
            $form->delete();
            return redirect()->route('proposal.skripsi.form')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
