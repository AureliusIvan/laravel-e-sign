<?php

namespace App\Http\Controllers\Form;

use App\Models\Dosen;
use App\Models\TahunAjaran;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\RevisiProposal;
use App\Models\RevisiProposalForm;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProposalSkripsiForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RevisiProposalFormController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = RevisiProposalForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.prodi.form.revisi-proposal-form.revisi-proposal-form', [
            'title' => 'Form',
            'subtitle' => 'Revisi Proposal Skripsi',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        $proposalForm = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $prodi->id)
            ->get();
        return view('pages.prodi.form.revisi-proposal-form.add-revisi-proposal-form', [
            'title' => 'Form',
            'subtitle' => 'Tambah Form Revisi Proposal Skripsi',
            'tahun' => $active->uuid,
            'prodi' => $prodi->uuid,
            'form' => $proposalForm,
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
            'proposal_form' => ['required'],
            'judul_form' => ['required'],
            'keterangan' => ['required'],
            'dibuka' => ['required'],
            'ditutup' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $tahun = TahunAjaran::where('uuid', $request->tahun_ajaran)->firstOrFail();
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();
                $form = ProposalSkripsiForm::where('uuid', $request->proposal_form)->firstOrFail();
                $dibuka = date('Y-m-d H:i:s', strtotime($request->dibuka));
                $ditutup = date('Y-m-d H:i:s', strtotime($request->ditutup));

                RevisiProposalForm::create([
                    'tahun_ajaran_id' => $tahun->id,
                    'program_studi_id' => $program->id,
                    'proposal_skripsi_form_id' => $form->id,
                    'judul_form' => $request->judul_form,
                    'keterangan' => $request->keterangan,
                    'dibuka' => $dibuka,
                    'ditutup' => $ditutup,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('revisi-proposal.skripsi.form')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('revisi-proposal.skripsi.form');
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
            $data = RevisiProposalForm::with('proposalSkripsiForm')->where('uuid', $uuid)->firstOrFail();
            $active = TahunAjaran::where('status_aktif', 1)->first();
            $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
            $isLinked = RevisiProposal::where('revisi_proposal_form_id', $data->id)->exists();
            $proposalForm = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
                ->where('program_studi_id', $user->program_studi_id)
                ->get();

            return view('pages.prodi.form.revisi-proposal-form.edit-revisi-proposal-form', [
                'title' => 'Form',
                'subtitle' => 'Edit Form Revisi Proposal Skripsi',
                'data' => $data,
                'isLinked' => $isLinked,
                'form' => $proposalForm,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'proposal_form' => ['required'],
            'judul_form' => ['required'],
            'keterangan' => ['required'],
            'dibuka' => ['required'],
            'ditutup' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request, $uuid) {
                DB::table('permintaan_mahasiswa_form')->lockForUpdate()->get();
                $form = RevisiProposalForm::where('uuid', $uuid)->firstOrFail();
                $proposalForm = ProposalSkripsiForm::where('uuid', $request->proposal_form)->firstOrFail();

                $dibuka = date('Y-m-d H:i:s', strtotime($request->dibuka));
                $ditutup = date('Y-m-d H:i:s', strtotime($request->ditutup));

                $form->proposal_skripsi_form_id = $proposalForm->id;
                $form->judul_form = $request->judul_form;
                $form->keterangan = $request->keterangan;
                $form->dibuka = $dibuka;
                $form->ditutup = $ditutup;
                $form->save();
            });
            return redirect()->route('revisi-proposal.skripsi.form')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $form = RevisiProposalForm::where('uuid', $uuid)->firstOrFail();
            $form->delete();
            return redirect()->route('revisi-proposal.skripsi.form')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
