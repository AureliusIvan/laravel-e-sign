<?php

use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\User\DosenController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\NilaiSidangController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\JadwalSidangController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\KategoriNilaiController;
use App\Http\Controllers\ResearchDosenController;
use App\Http\Controllers\AreaPenelitianController;
use App\Http\Controllers\CariPembimbingController;
use App\Http\Controllers\PembimbingSayaController;
use App\Http\Controllers\User\MahasiswaController;
use App\Http\Controllers\PenilaiProposalController;
use App\Http\Controllers\AssignPembimbingController;
use App\Http\Controllers\ResearchInterestController;
use App\Http\Controllers\KategoriNilaiDetailController;
use App\Http\Controllers\PermintaanMahasiswaController;
use App\Http\Controllers\Form\LaporanAkhirFormController;
use App\Http\Controllers\Form\CariPembimbingFormController;
use App\Http\Controllers\Form\RevisiProposalFormController;
use App\Http\Controllers\Periksa\PeriksaProposalController;
use App\Http\Controllers\Form\ProposalSkripsiFormController;
use App\Http\Controllers\Pengumpulan\LaporanAkhirController;
use App\Http\Controllers\Pengumpulan\RevisiProposalController;
use App\Http\Controllers\Approve\ApproveLaporanAkhirController;
use App\Http\Controllers\Pengumpulan\ProposalSkripsiController;
use App\Http\Controllers\Periksa\PeriksaLaporanAkhirController;
use App\Http\Controllers\Periksa\PeriksaRevisiProposalController;
use App\Http\Controllers\ProposalRtiController;
use App\Http\Controllers\ProposalRtiFormController;
use App\Http\Controllers\SignatureController;

Route::get('/', function () {
    $active = TahunAjaran::where('status_aktif', 1)->first();
    $data = DB::table('berita_acara')
        ->select('*')
//         ->where('tahun_ajaran_id', $active->id)
        ->orderBy('tanggal_awal', 'asc')
        ->get()
        ->toArray();
    return view('index', [
        'data' => $data,
    ]);
});

Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'user.type:admin'])->group(function () {
    Route::get('/admin/dashboard', [HomeController::class, 'admin'])->name('dashboard.admin');

    // Program Studi Controller
    Route::get('/programstudi', [ProgramStudiController::class, 'index'])->name('programstudi');
    Route::get('/programstudi/edit', [ProgramStudiController::class, 'create'])->name('programstudi.create');
    Route::post('/programstudi', [ProgramStudiController::class, 'store'])->name('programstudi.store');
    Route::get('/programstudi/{uuid}/edit', [ProgramStudiController::class, 'edit'])->name('programstudi.edit');
    Route::put('/programstudi/{programstudi:uuid}', [ProgramStudiController::class, 'update'])->name('programstudi.update');
    Route::delete('/programstudi', [ProgramStudiController::class, 'destroy'])->name('programstudi.destroy');

    // Tahun Ajaran Controller
    Route::get('/tahunajaran', [TahunAjaranController::class, 'index'])->name('tahunajaran');
    Route::get('/tahunajaran/create', [TahunAjaranController::class, 'create'])->name('tahunajaran.create');
    Route::post('/tahunajaran', [TahunAjaranController::class, 'store'])->name('tahunajaran.store');
    Route::get('/tahunajaran/{uuid}/edit', [TahunAjaranController::class, 'edit'])->name('tahunajaran.edit');
    Route::put('/tahunajaran/{uuid}', [TahunAjaranController::class, 'update'])->name('tahunajaran.update');
    Route::delete('/tahunajaran', [TahunAjaranController::class, 'destroy'])->name('tahunajaran.destroy');
    Route::post('/tahunajaran/{uuid}/updatestatus', [TahunAjaranController::class, 'updateStatus'])->name('tahunajaran.update.status');

    // Mahasiswa Controller
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa');
    Route::get('/mahasiswa/{uuid}/edit', [MahasiswaController::class, 'edit'])->name('mahasiswa.edit');
    Route::put('/mahasiswa/{uuid}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::delete('/mahasiswa', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    Route::get('/mahasiswa/createtahunskripsi', [MahasiswaController::class, 'createTahunSkripsi'])->name('mahasiswa.create.tahun.skripsi');
    Route::post('/mahasiswa/storetahunskripsi', [MahasiswaController::class, 'storeTahunSkripsi'])->name('mahasiswa.store.tahun.skripsi');
    Route::post('/mahasiswa/{uuid}/updatestatus', [MahasiswaController::class, 'updateStatus'])->name('mahasiswa.update.status');

    // Dosen Controller
    Route::get('/dosen', [DosenController::class, 'index'])->name('dosen');
    Route::get('/dosen/create', [DosenController::class, 'create'])->name('dosen.create');
    Route::post('/dosen', [DosenController::class, 'store'])->name('dosen.store');
    Route::get('/dosen/{uuid}/edit', [DosenController::class, 'edit'])->name('dosen.edit');
    Route::put('/dosen/{uuid}', [DosenController::class, 'update'])->name('dosen.update');
    Route::delete('/dosen', [DosenController::class, 'destroy'])->name('dosen.destroy');
    Route::post('/dosen/{uuid}/updatestatus', [DosenController::class, 'updateStatus'])->name('dosen.update.status');

    // Pengaturan
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
    Route::get('/pengaturan/create', [PengaturanController::class, 'create'])->name('pengaturan.create');
    Route::post('/pengaturan/store', [PengaturanController::class, 'store'])->name('pengaturan.store');
    Route::get('/pengaturan/{uuid}/edit', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
});

Route::middleware(['auth', 'verified', 'user.type:mahasiswa'])->group(function () {
    Route::get('/mahasiswa/dashboard', [HomeController::class, 'mahasiswa'])->name('dashboard.mahasiswa');
});

Route::middleware(['auth', 'verified', 'user.type:mahasiswa', 'check.status.mahasiswa'])->group(function () {
    // Area Penelitian
    // Route::get('/areapenelitian/list', [AreaPenelitianController::class, 'mahasiswa'])->name('areapenelitian.list');

    // Cari Pembimbing
    Route::get('/caripembimbing', [CariPembimbingController::class, 'index'])->name('caripembimbing');
    Route::post('/caripembimbing/dosen/{uuid?}', [CariPembimbingController::class, 'fetchDosen'])->name('caripembimbing.fetch.dosen');
    Route::post('/caripembimbing/store', [CariPembimbingController::class, 'store'])->name('caripembimbing.store');
    Route::get('/caripembimbing/{uuid}/show', [CariPembimbingController::class, 'show'])->name('caripembimbing.show');
    Route::get('/caripembimbing/{uuid}/file', [CariPembimbingController::class, 'getFile'])->name('caripembimbing.getfile');

    // List Dosen Pembimbing
    Route::get('/caripembimbing/listpembimbing', [CariPembimbingController::class, 'listDosen'])->name('caripembimbing.list.pembimbing');

    // Pembimbing Saya
    Route::get('/pembimbing-saya', [PembimbingSayaController::class, 'mahasiswa'])->name('pembimbing.saya');

    // Proposal RTI
    Route::get('/proposal-rti', [ProposalRtiController::class, 'index'])->name('proposal.rti');
    Route::post('/proposal-rti/store', [ProposalRtiController::class, 'store'])->name('proposal.rti.store');
    Route::get('/proposal-rti/file/{uuid}', [ProposalRtiController::class, 'file'])->name('proposal.rti.file');
    Route::delete('/proposal-rti/destroy', [ProposalRtiController::class, 'destroy'])->name('proposal.rti.destroy');

    // Bimbingan
    // Route::get('/bimbingan', [BimbinganController::class, 'mahasiswa'])->name('bimbingan');
    // Route::post('/bimbingan/mahasiswa', [BimbinganController::class, 'mahasiswaStore'])->name('bimbingan.store');
    // Route::get('/bimbingan/{uuid}/mahasiswa', [BimbinganController::class, 'mahasiswaEdit'])->name('bimbingan.edit');
    // Route::put('/bimbingan/{uuid}', [BimbinganController::class, 'mahasiswaUpdate'])->name('bimbingan.update');

    // Proposal Skripsi
    Route::get('/proposal-skripsi', [ProposalSkripsiController::class, 'index'])->name('proposal.skripsi.pengumpulan');
    Route::post('/proposal-skripsi/store', [ProposalSkripsiController::class, 'store'])->name('proposal.skripsi.pengumpulan.store');
    Route::get('/proposal-skripsi/{uuid}/file', [ProposalSkripsiController::class, 'getFile'])->name('proposal.skripsi.getfile');
    Route::delete('/proposal-skripsi/destroy', [ProposalSkripsiController::class, 'destroy'])->name('proposal.skripsi.pengumpulan.destroy');
    Route::get('/proposal-skripsi/hasil', [ProposalSkripsiController::class, 'hasil'])->name('proposal.skripsi.hasil');
    Route::get('/proposal-skripsi/hasil/{uuid}/download-file-periksa-penilai1', [ProposalSkripsiController::class, 'downloadFilePeriksaPenilai1'])->name('proposal.skripsi.hasil.download-file-periksa-penilai1');
    Route::get('/proposal-skripsi/hasil/{uuid}/download-file-periksa-penilai2', [ProposalSkripsiController::class, 'downloadFilePeriksaPenilai2'])->name('proposal.skripsi.hasil.download-file-periksa-penilai2');
    Route::get('/proposal-skripsi/hasil/{uuid}/download-file-periksa-penilai3', [ProposalSkripsiController::class, 'downloadFilePeriksaPenilai3'])->name('proposal.skripsi.hasil.download-file-periksa-penilai3');

    // Revisi Proposal Skripsi
    // Route::get('/revisi-proposal-skripsi', [RevisiProposalController::class, 'index'])->name('revisi-proposal-skripsi.pengumpulan');
    // Route::get('/revisi-proposal-skripsi/fetch-proposal/{uuid}', [RevisiProposalController::class, 'fetchProposal'])->name('revisi-proposal-skripsi.pengumpulan.fetch-proposal');
    // Route::get('/revisi-proposal-skripsi/fetch-detail-proposal/{uuid}', [RevisiProposalController::class, 'fetchDetailProposal'])->name('revisi-proposal-skripsi.pengumpulan.fetch-detail-proposal');
    // Route::post('/revisi-proposal-skripsi/store', [RevisiProposalController::class, 'store'])->name('revisi-proposal-skripsi.pengumpulan.store');
    // Route::get('/revisi-proposal-skripsi/pengumpulan/{uuid}/file', [RevisiProposalController::class, 'getFile'])->name('revisi-proposal-skripsi.pengumpulan.getfile');
    // Route::delete('/revisi-proposal-skripsi/pengumpulan/destroy', [RevisiProposalController::class, 'destroy'])->name('revisi-proposal-skripsi.pengumpulan.destroy');
    // Route::get('/revisi-proposal-skripsi/pengumpulan/{uuid}/file-revisi-penilai1', [RevisiProposalController::class, 'fileRevisiPenilai1'])->name('revisi-proposal-skripsi.pengumpulan.file-revisi-penilai1');
    // Route::get('/revisi-proposal-skripsi/pengumpulan/{uuid}/file-revisi-penilai2', [RevisiProposalController::class, 'fileRevisiPenilai2'])->name('revisi-proposal-skripsi.pengumpulan.file-revisi-penilai2');
    // Route::get('/revisi-proposal-skripsi/pengumpulan/{uuid}/file-revisi-penilai3', [RevisiProposalController::class, 'fileRevisiPenilai3'])->name('revisi-proposal-skripsi.pengumpulan.file-revisi-penilai3');

    // Laporan Skripsi
    // Route::get('/laporan-skripsi', [LaporanAkhirController::class, 'index'])->name('laporan.skripsi.pengumpulan');
    // Route::get('/laporan-skripsi/fetch-revisi-proposal', [LaporanAkhirController::class, 'fetchRevisiProposal'])->name('laporan.skripsi.pengumpulan.fetch-revisi-proposal');
    // Route::post('/laporan-skripsi/store', [LaporanAkhirController::class, 'store'])->name('laporan.skripsi.pengumpulan.store');
    // Route::delete('/laporan-skripsi/destroy', [LaporanAkhirController::class, 'destroy'])->name('laporan.skripsi.pengumpulan.destroy');
    // Route::get('/laporan-skripsi/{uuid}/file', [LaporanAkhirController::class, 'getFile'])->name('laporan.skripsi.pengumpulan.getfile');
    // Route::get('/laporan-skripsi/{uuid}/file-pembimbing1', [LaporanAkhirController::class, 'filePembimbing1'])->name('laporan.skripsi.pengumpulan.file-pembimbing1');
    // Route::get('/laporan-skripsi/{uuid}/file-pembimbing2', [LaporanAkhirController::class, 'filePembimbing2'])->name('laporan.skripsi.pengumpulan.file-pembimbing2');
    // Route::get('/laporan-skripsi/{uuid}/file-kaprodi', [LaporanAkhirController::class, 'fileKaprodi'])->name('laporan.skripsi.pengumpulan.file-kaprodi');
});

Route::middleware(['auth', 'verified', 'user.type:kaprodi,sekprodi'])->group(function () {
    Route::get('/prodi/dashboard', [HomeController::class, 'prodi'])->name('dashboard.prodi');
});

Route::middleware(['auth', 'verified', 'user.type:kaprodi,sekprodi', 'check.status.dosen'])->group(function () {
    // Route::get('/prodi/dashboard', [HomeController::class, 'prodi'])->name('dashboard.prodi');

    // Pengaturan
    // Route::get('/pengaturan/prodi', [PengaturanController::class, 'prodi'])->name('pengaturan.prodi');
    // Route::put('/pengaturan/prodi/{uuid}', [PengaturanController::class, 'updateProdi'])->name('pengaturan.prodi.update');

    // Berita Acara
    Route::get('/berita', [BeritaAcaraController::class, 'index'])->name('berita');
    Route::get('/berita/create', [BeritaAcaraController::class, 'create'])->name('berita.create');
    Route::post('/berita/store', [BeritaAcaraController::class, 'store'])->name('berita.store');
    Route::get('/berita/{uuid}/edit', [BeritaAcaraController::class, 'edit'])->name('berita.edit');
    Route::put('/berita/{uuid}', [BeritaAcaraController::class, 'update'])->name('berita.update');
    Route::delete('/berita/destroy', [BeritaAcaraController::class, 'destroy'])->name('berita.destroy');

    // Pilihkan Pembimbing
    Route::get('/pilihkan-pembimbing', [AssignPembimbingController::class, 'index'])->name('pilihkan.pembimbing');
    Route::post('/pilihkan-pembimbing/store', [AssignPembimbingController::class, 'store'])->name('pilihkan.pembimbing.store');
    Route::get('/pilihkan-pembimbing/fetchpembimbing/{uuid}', [AssignPembimbingController::class, 'fetchPembimbing'])->name('pilihkan.pembimbing.fetchpembimbing');
    Route::get('/pilihkan-pembimbing/mahasiswatanpapembimbing', [AssignPembimbingController::class, 'mahasiswaTanpaPembimbing'])->name('pilihkan.pembimbing.mahasiswatanpapembimbing');
    Route::get('/pilihkan-pembimbing/mahasiswapembimbing', [AssignPembimbingController::class, 'mahasiswaPembimbing'])->name('pilihkan.pembimbing.mahasiswapembimbing');

    // Research Interest
    Route::get('/research', [ResearchInterestController::class, 'index'])->name('research');
    Route::get('/research/create', [ResearchInterestController::class, 'create'])->name('research.create');
    Route::post('/research/store', [ResearchInterestController::class, 'store'])->name('research.store');
    Route::get('/research/{uuid}/edit', [ResearchInterestController::class, 'edit'])->name('research.edit');
    Route::put('/research/{uuid}', [ResearchInterestController::class, 'update'])->name('research.update');
    Route::delete('/research/destroy', [ResearchInterestController::class, 'destroy'])->name('research.destroy');

    // Research Dosen
    Route::get('/research/dosen', [ResearchDosenController::class, 'index'])->name('research.dosen');
    Route::get('/research/dosen/create', [ResearchDosenController::class, 'create'])->name('research.dosen.create');
    Route::post('/research/dosen/store', [ResearchDosenController::class, 'store'])->name('research.dosen.store');
    Route::get('/research/dosen/{uuid}/edit', [ResearchDosenController::class, 'edit'])->name('research.dosen.edit');
    Route::put('/research/dosen/{uuid}', [ResearchDosenController::class, 'update'])->name('research.dosen.update');
    Route::delete('/research/dosen/destroy', [ResearchDosenController::class, 'destroy'])->name('research.dosen.destroy');

    // Form Cari Pembimbing
    Route::get('/caripembimbing/form', [CariPembimbingFormController::class, 'index'])->name('caripembimbing.form');
    Route::get('/caripembimbing/form/create', [CariPembimbingFormController::class, 'create'])->name('caripembimbing.form.create');
    Route::post('/caripembimbing/form/store', [CariPembimbingFormController::class, 'store'])->name('caripembimbing.form.store');
    Route::get('/caripembimbing/form/{uuid}/edit', [CariPembimbingFormController::class, 'edit'])->name('caripembimbing.form.edit');
    Route::put('/caripembimbing/form/{uuid}', [CariPembimbingFormController::class, 'update'])->name('caripembimbing.form.update');
    Route::delete('/caripembimbing/form/destroy', [CariPembimbingFormController::class, 'destroy'])->name('caripembimbing.form.destroy');

    // Area Penelitian
    Route::get('/areapenelitian', [AreaPenelitianController::class, 'index'])->name('areapenelitian');
    Route::get('/areapenelitian/create', [AreaPenelitianController::class, 'create'])->name('areapenelitian.create');
    Route::post('/areapenelitian/store', [AreaPenelitianController::class, 'store'])->name('areapenelitian.store');
    Route::get('/areapenelitian/{uuid}/edit', [AreaPenelitianController::class, 'edit'])->name('areapenelitian.edit');
    Route::put('/areapenelitian/{uuid}', [AreaPenelitianController::class, 'update'])->name('areapenelitian.update');
    Route::delete('/areapenelitian/destroy', [AreaPenelitianController::class, 'destroy'])->name('areapenelitian.destroy');
    Route::get('/areapenelitian/export-template', [AreaPenelitianController::class, 'exportTemplate'])->name('areapenelitian.export.template');
    Route::post('/areapenelitian/import', [AreaPenelitianController::class, 'import'])->name('areapenelitian.import');

    // Form Proposal RTI
    Route::get('/proposal-rti/form', [ProposalRtiFormController::class, 'index'])->name('proposal.rti.form');
    Route::get('/proposal-rti/form/create', [ProposalRtiFormController::class, 'create'])->name('proposal.rti.form.create');
    Route::post('/proposal-rti/form/store', [ProposalRtiFormController::class, 'store'])->name('proposal.rti.form.store');
    Route::get('/proposal-rti/form/{uuid}/edit', [ProposalRtiFormController::class, 'edit'])->name('proposal.rti.form.edit');
    Route::put('/proposal-rti/form/{uuid}', [ProposalRtiFormController::class, 'update'])->name('proposal.rti.form.update');
    Route::delete('/proposal-rti/form/destroy', [ProposalRtiFormController::class, 'destroy'])->name('proposal.rti.form.destroy');

    // Form Proposal Skripsi
    Route::get('/proposal-skripsi/form', [ProposalSkripsiFormController::class, 'index'])->name('proposal.skripsi.form');
    Route::get('/proposal-skripsi/form/create', [ProposalSkripsiFormController::class, 'create'])->name('proposal.skripsi.form.create');
    Route::post('/proposal-skripsi/form/store', [ProposalSkripsiFormController::class, 'store'])->name('proposal.skripsi.form.store');
    Route::get('/proposal-skripsi/form/{uuid}/edit', [ProposalSkripsiFormController::class, 'edit'])->name('proposal.skripsi.form.edit');
    Route::put('/proposal-skripsi/form/{uuid}', [ProposalSkripsiFormController::class, 'update'])->name('proposal.skripsi.form.update');
    Route::delete('/proposal-skripsi/form/destroy', [ProposalSkripsiFormController::class, 'destroy'])->name('proposal.skripsi.form.destroy');

    // Form Revisi Proposal Skripsi
    // Route::get('/revisi-proposal-skripsi/form', [RevisiProposalFormController::class, 'index'])->name('revisi-proposal.skripsi.form');
    // Route::get('/revisi-proposal-skripsi/form/create', [RevisiProposalFormController::class, 'create'])->name('revisi-proposal.skripsi.form.create');
    // Route::post('/revisi-proposal-skripsi/form/store', [RevisiProposalFormController::class, 'store'])->name('revisi-proposal.skripsi.form.store');
    // Route::get('/revisi-proposal-skripsi/form/{uuid}/edit', [RevisiProposalFormController::class, 'edit'])->name('revisi-proposal.skripsi.form.edit');
    // Route::put('/revisi-proposal-skripsi/form/{uuid}', [RevisiProposalFormController::class, 'update'])->name('revisi-proposal.skripsi.form.update');
    // Route::delete('/revisi-proposal-skripsi/form/destroy', [RevisiProposalFormController::class, 'destroy'])->name('revisi-proposal.skripsi.form.destroy');

    // Penilai Proposal
    Route::get('/proposal-skripsi/penilai', [PenilaiProposalController::class, 'index'])->name('proposal.skripsi.penilai');
    Route::get('/proposal-skripsi/penilai/{uuid}/show', [PenilaiProposalController::class, 'show'])->name('proposal.skripsi.penilai.show');
    Route::get('/proposal-skripsi/penilai/{uuid}/fetchdata', [PenilaiProposalController::class, 'fetchData'])->name('proposal.skripsi.penilai.fetchdata');
    Route::get('/proposal-skripsi/penilai/fetchdosen', [PenilaiProposalController::class, 'fetchDosen'])->name('proposal.skripsi.penilai.fetchdosen');
    Route::post('/proposal-skripsi/penilai/store', [PenilaiProposalController::class, 'store'])->name('proposal.skripsi.penilai.store');

    // Form Laporan Akhir
    // Route::get('/laporan-skripsi/form', [LaporanAkhirFormController::class, 'index'])->name('laporan.skripsi.form');
    // Route::get('/laporan-skripsi/form/create', [LaporanAkhirFormController::class, 'create'])->name('laporan.skripsi.form.create');
    // Route::post('/laporan-skripsi/form/store', [LaporanAkhirFormController::class, 'store'])->name('laporan.skripsi.form.store');
    // Route::get('/laporan-skripsi/form/{uuid}/edit', [LaporanAkhirFormController::class, 'edit'])->name('laporan.skripsi.form.edit');
    // Route::put('/laporan-skripsi/form/{uuid}', [LaporanAkhirFormController::class, 'update'])->name('laporan.skripsi.form.update');
    // Route::delete('/laporan-skripsi/form/destroy', [LaporanAkhirFormController::class, 'destroy'])->name('laporan.skripsi.form.destroy');

    // Kategori Nilai
    // Route::get('/kategori-nilai', [KategoriNilaiController::class, 'index'])->name('kategori.nilai');
    // Route::get('/kategori-nilai/create', [KategoriNilaiController::class, 'create'])->name('kategori.nilai.create');
    // Route::post('/kategori-nilai/store', [KategoriNilaiController::class, 'store'])->name('kategori.nilai.store');
    // Route::get('/kategori-nilai/{uuid}/edit', [KategoriNilaiController::class, 'edit'])->name('kategori.nilai.edit');
    // Route::put('/kategori-nilai/{uuid}', [KategoriNilaiController::class, 'update'])->name('kategori.nilai.update');
    // Route::delete('/kategori-nilai/destroy', [KategoriNilaiController::class, 'destroy'])->name('kategori.nilai.destroy');

    // Kategori Nilai Detail
    // Route::get('/kategori-nilai/detail/relation/{uuid}', [KategoriNilaiDetailController::class, 'fetchRelation'])->name('kategori.nilai.detail.relation');
    // Route::post('/kategori-nilai/detail/store', [KategoriNilaiDetailController::class, 'store'])->name('kategori.nilai.detail.store');
    // Route::put('/kategori-nilai/detail/update', [KategoriNilaiDetailController::class, 'update'])->name('kategori.nilai.detail.update');
    // Route::delete('/kategori-nilai/detail/destroy', [KategoriNilaiDetailController::class, 'destroy'])->name('kategori.nilai.detail.destroy');

    // Jadwal Sidang
    // Route::get('/jadwal-sidang/prodi', [JadwalSidangController::class, 'index'])->name('jadwal.sidang.prodi');
    // Route::get('/jadwal-sidang/prodi/{uuid}/show', [JadwalSidangController::class, 'show'])->name('jadwal.sidang.prodi.show');
    // Route::get('/jadwal-sidang/prodi/fetch-data/{uuid}', [JadwalSidangController::class, 'fetchData'])->name('jadwal.sidang.prodi.fetch-data');
    // Route::get('/jadwal-sidang/prodi/fetch-detail/{uuid}', [JadwalSidangController::class, 'fetchDetail'])->name('jadwal.sidang.prodi.fetch-detail');
    // Route::post('/jadwal-sidang/prodi/store', [JadwalSidangController::class, 'store'])->name('jadwal.sidang.prodi.store');
    // Route::post('/jadwal-sidang/prodi/destroy', [JadwalSidangController::class, 'destroy'])->name('jadwal.sidang.prodi.destroy');
});

Route::middleware(['auth', 'verified', 'user.type:kaprodi', 'check.status.dosen'])->group(function () {
    // Approve Laporan Akhir
    // Route::get('laporan-skripsi/approve', [ApproveLaporanAkhirController::class, 'index'])->name('laporan.skripsi.approve');
    // Route::get('laporan-skripsi/approve/{uuid}/show', [ApproveLaporanAkhirController::class, 'show'])->name('laporan.skripsi.approve.show');
    // Route::post('laporan-skripsi/approve/store', [ApproveLaporanAkhirController::class, 'store'])->name('laporan.skripsi.approve.store');
    // Route::delete('laporan-skripsi/approve/destroy', [ApproveLaporanAkhirController::class, 'destroy'])->name('laporan.skripsi.approve.destroy');
    // Route::get('/laporan-skripsi/approve/{uuid}/file', [ApproveLaporanAkhirController::class, 'getFile'])->name('laporan.skripsi.approve.getfile');
    // Route::get('/laporan-skripsi/approve/{uuid}/file-pembimbing1', [ApproveLaporanAkhirController::class, 'filePembimbing1'])->name('laporan.skripsi.approve.file-pembimbing1');
    // Route::get('/laporan-skripsi/approve/{uuid}/file-pembimbing2', [ApproveLaporanAkhirController::class, 'filePembimbing2'])->name('laporan.skripsi.approve.file-pembimbing2');
    // Route::get('/laporan-skripsi/approve/{uuid}/file-kaprodi', [ApproveLaporanAkhirController::class, 'fileKaprodi'])->name('laporan.skripsi.approve.file-kaprodi');
});

Route::middleware(['auth', 'verified', 'user.type:dosen'])->group(function () {
    Route::get('/dosen/dashboard', [HomeController::class, 'dosen'])->name('dashboard.dosen');
});

Route::middleware(['auth', 'verified', 'user.type:dosen,kaprodi,sekprodi', 'check.status.dosen'])->group(function () {
    // Permintaan Mahasiswa
    Route::get('/permintaan-mahasiswa', [PermintaanMahasiswaController::class, 'index'])->name('permintaan.mahasiswa');
    Route::get('/permintaan-mahasiswa/{uuid}/show', [PermintaanMahasiswaController::class, 'show'])->name('permintaan.mahasiswa.show');
    Route::post('/permintaan-mahasiswa/store', [PermintaanMahasiswaController::class, 'store'])->name('permintaan.mahasiswa.store');
    Route::get('/permintaan-mahasiswa/{uuid}/file', [CariPembimbingController::class, 'getFile'])->name('permintaan.mahasiswa.getfile');

    // Topik Penelitian Saya
    Route::get('/topik-penelitian-saya', [ResearchDosenController::class, 'show'])->name('topik.penelitian.saya');

    // Mahasiswa Bimbingan
    Route::get('/list-mahasiswa-bimbingan', [PembimbingSayaController::class, 'dosen'])->name('list.mahasiswa.bimbingan');
    Route::get('/check-thesis', [SignatureController::class, 'checkThesis'])->name('check.thesis');
    Route::post('/check-thesis/sign', [SignatureController::class, 'signThesis'])->name('sign.thesis');
    Route::get('/proposal/convert/{filename}', [SignatureController::class, 'convertPdfToImages'])
        ->name('proposal.convert');
    Route::get('/proposal/download/{filename}', [SignatureController::class, 'downloadSignedProposal'])
        ->name('proposal.signed.download');


    // Bimbingan
    // Route::get('/bimbingan/dosen', [BimbinganController::class, 'dosen'])->name('bimbingan.dosen');
    // Route::get('/bimbingan/dosen/fetch-all/{uuid}', [BimbinganController::class, 'dosenFetchAll'])->name('bimbingan.dosen.fetch.all');
    // Route::get('/bimbingan/dosen/{uuid}/show', [BimbinganController::class, 'dosenShow'])->name('bimbingan.dosen.show');
    // Route::post('/bimbingan/dosen/update-status', [BimbinganController::class, 'dosenUpdateStatus'])->name('bimbingan.dosen.update.status');

    // Periksa Proposal RTI
    Route::get('/proposal-rti/periksa', [ProposalRtiController::class, 'dosen'])->name('proposal.rti.periksa');
    Route::get('/proposal-rti/periksa/{uuid}/show', [ProposalRtiController::class, 'dosenShow'])->name('proposal.rti.periksa.show');
    Route::get('/proposal-rti/file-proposal-rti/{uuid}', [ProposalRtiController::class, 'fileDosen'])->name('proposal.rti.file.dosen');
    Route::post('/proposal-rti/periksa/store', [ProposalRtiController::class, 'dosenStore'])->name('proposal.rti.periksa.store');
    Route::delete('/proposal-rti/periksa/destroy', [ProposalRtiController::class, 'dosenDeleteApproval'])->name('proposal.rti.periksa.destroy');

    // Periksa Proposal
    Route::get('/proposal-skripsi/periksa', [PeriksaProposalController::class, 'index'])->name('proposal.skripsi.periksa');
    Route::get('/proposal-skripsi/periksa/{uuid}/show', [PeriksaProposalController::class, 'show'])->name('proposal.skripsi.periksa.show');
    Route::post('/proposal-skripsi/periksa/store', [PeriksaProposalController::class, 'store'])->name('proposal.skripsi.periksa.store');
    Route::get('/proposal-skripsi/periksa/{uuid}/download-multiple-file', [PeriksaProposalController::class, 'downloadMultipleFile'])->name('proposal.skripsi.periksa.store.download-multiple-file');
    Route::get('/proposal-skripsi/periksa/{uuid}/{segment}/download-file-periksa', [PeriksaProposalController::class, 'downloadFilePeriksa'])->name('proposal.skripsi.periksa.store.download-file-periksa');
    Route::delete('/proposal-skripsi/periksa/destroy', [PeriksaProposalController::class, 'destroy'])->name('proposal.skripsi.periksa.destroy');

    // Periksa Revisi Proposal
    // Route::get('/revisi-proposal-skripsi/periksa', [PeriksaRevisiProposalController::class, 'index'])->name('revisi-proposal-skripsi.periksa');
    // Route::get('/revisi-proposal-skripsi/periksa/{uuid}/file', [PeriksaRevisiProposalController::class, 'getFile'])->name('revisi-proposal-skripsi.periksa.getfile');
    // Route::get('/revisi-proposal-skripsi/periksa/{uuid}/show', [PeriksaRevisiProposalController::class, 'show'])->name('revisi-proposal-skripsi.periksa.show');
    // Route::post('/revisi-proposal-skripsi/periksa/store', [PeriksaRevisiProposalController::class, 'store'])->name('revisi-proposal-skripsi.periksa.store');
    // Route::get('/revisi-proposal-skripsi/periksa/{uuid}/file-revisi-penilai1', [PeriksaRevisiProposalController::class, 'fileRevisiPenilai1'])->name('revisi-proposal-skripsi.periksa.file-revisi-penilai1');
    // Route::get('/revisi-proposal-skripsi/periksa/{uuid}/file-revisi-penilai2', [PeriksaRevisiProposalController::class, 'fileRevisiPenilai2'])->name('revisi-proposal-skripsi.periksa.file-revisi-penilai2');
    // Route::get('/revisi-proposal-skripsi/periksa/{uuid}/file-revisi-penilai3', [PeriksaRevisiProposalController::class, 'fileRevisiPenilai3'])->name('revisi-proposal-skripsi.periksa.file-revisi-penilai3');
    // Route::delete('/revisi-proposal-skripsi/periksa/destroy', [PeriksaRevisiProposalController::class, 'destroy'])->name('revisi-proposal-skripsi.periksa.destroy');

    // Periksa Laporan Akhir
    // Route::get('/laporan-skripsi/periksa', [PeriksaLaporanAkhirController::class, 'index'])->name('laporan.skripsi.periksa');
    // Route::get('/laporan-skripsi/periksa/{uuid}/show', [PeriksaLaporanAkhirController::class, 'show'])->name('laporan.skripsi.periksa.show');
    // Route::post('/laporan-skripsi/periksa/store', [PeriksaLaporanAkhirController::class, 'store'])->name('laporan.skripsi.periksa.store');
    // Route::delete('/laporan-skripsi/periksa/destroy', [PeriksaLaporanAkhirController::class, 'destroy'])->name('laporan.skripsi.periksa.destroy');
    // Route::get('/laporan-skripsi/periksa/{uuid}/file', [PeriksaLaporanAkhirController::class, 'getFile'])->name('laporan.skripsi.periksa.getfile');
    // Route::get('/laporan-skripsi/periksa/{uuid}/file-pembimbing1', [PeriksaLaporanAkhirController::class, 'filePembimbing1'])->name('laporan.skripsi.periksa.file-pembimbing1');
    // Route::get('/laporan-skripsi/periksa/{uuid}/file-pembimbing2', [PeriksaLaporanAkhirController::class, 'filePembimbing2'])->name('laporan.skripsi.periksa.file-pembimbing2');
    // Route::get('/laporan-skripsi/periksa/{uuid}/file-kaprodi', [PeriksaLaporanAkhirController::class, 'fileKaprodi'])->name('laporan.skripsi.periksa.file-kaprodi');

    // Jadwal Sidang
    // Route::get('/jadwal-sidang', [JadwalSidangController::class, 'dosen'])->name('jadwal.sidang');

    // Input Nilai Mahasiswa
    // Route::get('/nilai-sidang', [NilaiSidangController::class, 'index'])->name('nilai.sidang');
    // Route::get('/nilai-sidang/{uuid}/show', [NilaiSidangController::class, 'show'])->name('nilai.sidang.show');
    // Route::get('/nilai-sidang/{uuid}/file-laporan', [NilaiSidangController::class, 'fileKaprodi'])->name('nilai.sidang.file-laporan');
});

// E-SIGN Routes - Available for admin, dosen, kaprodi, sekprodi
Route::middleware(['auth', 'verified', 'user.type:admin,dosen,kaprodi,sekprodi'])->group(function () {
    Route::get('/verify-signed-document', [SignatureController::class, 'verifyThesis'])->name('verify');
    Route::post('/verify-signed-document/upload', [SignatureController::class, 'uploadVerifyThesis'])->name('verify.upload');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

//    Route::get('/proposal/{filename}', [ProposalController::class, 'serveFile'])
//        ->name('proposal.serve')
//        ->middleware('auth'); // Optional: restrict access
    Route::get('/proposal/{filename}', [SignatureController::class, 'serveFile'])
        ->name('proposal.serve');
});

Route::fallback(function () {
    abort(404);
});

require __DIR__ . '/auth.php';
