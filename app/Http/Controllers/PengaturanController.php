<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Pengaturan;
use App\Models\PengaturanDetail;
use App\Models\ProgramStudi;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    // Untuk admin
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $data = Pengaturan::with('programStudi')->where('tahun_ajaran_id', $active->id)->get();
        return view('pages.admin.pengaturan.pengaturan', compact('data'), [
            'title' => 'Pengaturan',
            'subtitle' => '',
        ]);
    }

    // Untuk prodi
    public function prodi()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $program = ProgramStudi::where('id', $user->program_studi_id)->firstOrFail();
        $available = DB::table('pengaturan')
            ->select('program_studi_id')
            ->where('tahun_ajaran_id', $active->id)
            ->get()
            ->toArray();
        $tahunActive = TahunAjaran::where('status_aktif', 1)->first();
        $data = Pengaturan::with(['pengaturanDetail', 'programStudi'])
            ->where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->first();
        return view('pages.prodi.pengaturan.pengaturan', [
            'title' => 'Pengaturan',
            'subtitle' => '',
            'data' => $data,
            'program' => $program,
            'tahun' => $tahunActive,
            'available' => $available,
        ]);
    }

    public function create()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        // $program = ProgramStudi::all();
        $program = DB::table('program_studi')->get()->toArray();
        $available = DB::table('pengaturan')
            ->select('program_studi_id')
            ->where('tahun_ajaran_id', $active->id)
            ->get()
            ->toArray();
        $tahunActive = TahunAjaran::where('status_aktif', 1)->first();
        return view('pages.admin.pengaturan.add-pengaturan', [
            'title' => 'Pengaturan',
            'subtitle' => '',
            'program' => $program,
            'tahun' => $tahunActive,
            'available' => $available,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_studi' => ['required'],
            'kuota_pembimbing_pertama' => ['required', 'numeric'],
            'kuota_pembimbing_kedua' => ['required', 'numeric'],
            'minimum_jumlah_bimbingan' => ['required', 'numeric'],
            'minimum_jumlah_bimbingan_kedua' => ['required', 'numeric'],
            'tahun_rti_tersedia_sampai' => ['required', 'numeric'],
            'semester_rti_tersedia_sampai' => ['required'],
            'penamaan_proposal' => ['required'],
            'penamaan_revisi_proposal' => ['required'],
            'penamaan_laporan' => ['required'],
            'penamaan_revisi_laporan' => ['required'],
            'jumlah_setuju_proposal' => ['required'],
            'jumlah_setuju_sidang_satupembimbing' => ['required'],
            'jumlah_setuju_sidang_duapembimbing' => ['required'],
            'action' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('pengaturan')->lockForUpdate()->get();

                $tahun = TahunAjaran::where('status_aktif', true)->firstOrFail();
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();

                $pengaturan = Pengaturan::create([
                    'tahun_ajaran_id' => $tahun->id,
                    'program_studi_id' => $program->id,
                    'penamaan_proposal' => $request->penamaan_proposal,
                    'penamaan_revisi_proposal' => $request->penamaan_revisi_proposal,
                    'penamaan_laporan' => $request->penamaan_laporan,
                    'penamaan_revisi_laporan' => $request->penamaan_revisi_laporan,
                ]);
                $lastId = $pengaturan->id;

                $proposal = null;
                $revisiProposal = null;
                $laporan = null;
                $revisiLaporan = null;

                if ($request->penamaan_proposal == 1) {
                    $proposal = $request->penamaan_proposal_part1 . '_' . $request->penamaan_proposal_part2 . '_' . $request->penamaan_proposal_part3;
                }
                if ($request->penamaan_revisi_proposal == 1) {
                    $revisiProposal = $request->penamaan_revisi_proposal_part1 . '_' . $request->penamaan_revisi_proposal_part2 . '_' . $request->penamaan_revisi_proposal_part3;
                }
                if ($request->penamaan_laporan == 1) {
                    $laporan = $request->penamaan_laporan_part1 . '_' . $request->penamaan_laporan_part2 . '_' . $request->penamaan_laporan_part3;
                }
                if ($request->penamaan_revisi_laporan == 1) {
                    $revisiLaporan = $request->penamaan_revisi_laporan_part1 . '_' . $request->penamaan_revisi_laporan_part2 . '_' . $request->penamaan_revisi_laporan_part3;
                }

                PengaturanDetail::create([
                    'pengaturan_id' => $lastId,
                    'kuota_pembimbing_pertama' => $request->kuota_pembimbing_pertama,
                    'kuota_pembimbing_kedua' => $request->kuota_pembimbing_kedua,
                    'minimum_jumlah_bimbingan' => $request->minimum_jumlah_bimbingan,
                    'minimum_jumlah_bimbingan_kedua' => $request->minimum_jumlah_bimbingan_kedua,
                    'tahun_rti_tersedia_sampai' => $request->tahun_rti_tersedia_sampai,
                    'semester_rti_tersedia_sampai' => $request->semester_rti_tersedia_sampai,
                    'tahun_proposal_tersedia_sampai' => $request->tahun_proposal_tersedia_sampai,
                    'semester_proposal_tersedia_sampai' => $request->semester_proposal_tersedia_sampai,
                    'penamaan_proposal' => $proposal,
                    'penamaan_revisi_proposal' => $revisiProposal,
                    'penamaan_laporan' => $laporan,
                    'penamaan_revisi_laporan' => $revisiLaporan,
                    'jumlah_setuju_proposal' => $request->jumlah_setuju_proposal,
                    'jumlah_setuju_sidang_satupembimbing' => $request->jumlah_setuju_sidang_satupembimbing,
                    'jumlah_setuju_sidang_duapembimbing' => $request->jumlah_setuju_sidang_duapembimbing,
                ]);
            });
            return redirect()->route('pengaturan')->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat pengaturan. Silahkan coba kembali');
            // return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($uuid)
    {
        //
    }

    public function updateProdi(Request $request, $uuid)
    {
        $request->validate([
            'program_studi' => ['required'],
            'kuota_bimbingan' => ['required', 'numeric'],
            'kuota_bimbingan_kedua' => ['required', 'numeric'],
            'minimum_jumlah_bimbingan' => ['required', 'numeric'],
            'minimum_jumlah_bimbingan_kedua' => ['required', 'numeric'],
            'tahun_pembimbing_tersedia_sampai' => ['required', 'numeric'],
            'semester_pembimbing_tersedia_sampai' => ['required'],
            'tahun_rti_tersedia_sampai' => ['required', 'numeric'],
            'semester_rti_tersedia_sampai' => ['required'],
            'upload_proposal_lama' => ['required'],
            'tahun_proposal_tersedia_sampai' => ['required', 'numeric'],
            'semester_proposal_tersedia_sampai' => ['required'],
            'penamaan_proposal' => ['required'],
            'penamaan_revisi_proposal' => ['required'],
            'penamaan_laporan' => ['required'],
            'penamaan_revisi_laporan' => ['required'],
            'jumlah_setuju_proposal' => ['required'],
            'jumlah_setuju_sidang_satupembimbing' => ['required'],
            'jumlah_setuju_sidang_duapembimbing' => ['required'],
            'action' => ['required'],
        ]);
    }
}
