<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PembimbingMahasiswa;
use App\Models\PermintaanMahasiswaForm;
use App\Models\ProgramStudi;
use App\Models\ProposalRtiForm;
use App\Models\ProposalSkripsiRTI;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalRtiFormController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalRtiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('dibuka', 'asc')
            ->get();
        // Mengambil semua data mahasiswa yang memiliki status aktif
        $allMahasiswa = Mahasiswa::where('status_aktif_skripsi', 1)->pluck('id');
        // Mengambil semua data mahasiswa yang memiliki setidaknya satu pembimbing
        $pembimbingMahasiswa = PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
            ->pluck('mahasiswa');
        // Mencari perbedaan antara $allMahasiswa dan $pembimbingMahasiswa
        $findDifference = $allMahasiswa->diff($pembimbingMahasiswa);

        // Variable untuk status pengecekan apakah mahasiswa memiliki pembimbing
        $isMahasiswaWithoutPembimbing = true;

        // Mengecek apakah ada mahasiswa yang belum mendapatkan pembimbing
        if (count($findDifference) == 0) {
            $isMahasiswaWithoutPembimbing = false;
        } else {
            $isMahasiswaWithoutPembimbing = true;
        }

        // Mendapatkan form cari pembimbing
        $form = PermintaanMahasiswaForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('ditutup', 'desc')
            ->first();

        // Variable untuk status pengecekan apakah form cari pembimbing sedang dibuka
        $isFormOpen = true;

        // Mengecek apakah ada mahasiswa yang belum mendapatkan pembimbing
        if ($form->ditutup < $now) {
            $isFormOpen = false;
        } else {
            $isFormOpen = true;
        }

        return view('pages.prodi.form.proposal-rti.proposal-rti-form', [
            'title' => 'Form',
            'subtitle' => 'Proposal RTI',
            'isMahasiswaWithoutPembimbing' => $isMahasiswaWithoutPembimbing,
            'isFormOpen' => $isFormOpen,
            'data' => $data,
        ]);
    }

    public function create()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        return view('pages.prodi.form.proposal-rti.add-proposal-rti-form', [
            'title' => 'Form',
            'subtitle' => 'Tambah Form Proposal RTI',
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

                ProposalRtiForm::create([
                    'tahun_ajaran_id' => $tahun->id,
                    'program_studi_id' => $program->id,
                    'judul_form' => $request->judul_form,
                    'keterangan' => $request->keterangan,
                    'dibuka' => $dibuka,
                    'ditutup' => $ditutup,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('proposal.rti.form')->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('proposal.rti.form');
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
            $data = ProposalRtiForm::where('uuid', $uuid)->firstOrFail();
            $isLinked = ProposalSkripsiRTI::where('proposal_skripsi_rti_form_id', $data->id)->exists();

            return view('pages.prodi.form.proposal-rti.edit-proposal-rti-form', [
                'title' => 'Form',
                'subtitle' => 'Edit Form Proposal RTI',
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
        ]);

        try {
            DB::transaction(function () use ($request, $uuid) {
                DB::table('proposal_skripsi_rti_form')->lockForUpdate()->get();
                $form = ProposalRtiForm::where('uuid', $uuid)->firstOrFail();

                $dibuka = date('Y-m-d H:i:s', strtotime($request->dibuka));
                $ditutup = date('Y-m-d H:i:s', strtotime($request->ditutup));

                $form->judul_form = $request->judul_form;
                $form->keterangan = $request->keterangan;
                $form->dibuka = $dibuka;
                $form->ditutup = $ditutup;
                $form->save();
            });

            return redirect()->route('proposal.rti.form')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $form = ProposalRtiForm::where('uuid', $uuid)->firstOrFail();
            $form->delete();
            return redirect()->route('proposal.rti.form')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
