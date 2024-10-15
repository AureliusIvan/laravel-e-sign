<?php

use App\Models\Dosen;
use App\Models\LaporanAkhir;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAjaran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_sidang', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(TahunAjaran::class);
            $table->foreignIdFor(ProgramStudi::class);
            $table->foreignIdFor(LaporanAkhir::class);
            $table->foreignIdFor(Mahasiswa::class)->nullable();
            $table->dateTime('jadwal_sidang')->nullable();
            $table->dateTime('ruang_sidang')->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing1')->nullable();
            $table->string('file_pembimbing1', 510)->nullable();
            $table->string('file_random_pembimbing1', 510)->nullable();
            $table->tinyInteger('keputusan_sidang_pembimbing1')->nullable();
            $table->text('berita_acara_pembimbing1')->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing2')->nullable();
            $table->string('file_pembimbing2', 510)->nullable();
            $table->string('file_random_pembimbing2', 510)->nullable();
            $table->tinyInteger('keputusan_sidang_pembimbing2')->nullable();
            $table->text('berita_acara_pembimbing2')->nullable();
            $table->foreignIdFor(Dosen::class, 'ketua_sidang')->nullable();
            $table->string('file_ketua_sidang', 510)->nullable();
            $table->string('file_random_ketua_sidang', 510)->nullable();
            $table->tinyInteger('keputusan_sidang_ketua_sidang')->nullable();
            $table->text('berita_acara_ketua_sidang')->nullable();
            $table->foreignIdFor(Dosen::class, 'penguji')->nullable();
            $table->string('file_penguji', 510)->nullable();
            $table->string('file_random_penguji', 510)->nullable();
            $table->tinyInteger('keputusan_sidang_penguji')->nullable();
            $table->text('berita_acara_penguji')->nullable();
            $table->tinyInteger('keputusan_akhir')->nullable();
            $table->boolean('ganti_judul')->nullable();
            $table->dateTime('pengumpulan_laporan_dibuka')->nullable();
            $table->dateTime('pengumpulan_laporan_ditutup')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_sidang');
    }
};
