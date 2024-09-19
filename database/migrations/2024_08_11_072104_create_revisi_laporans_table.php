<?php

use App\Models\Dosen;
use App\Models\JadwalSidang;
use App\Models\LaporanAkhir;
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
        Schema::create('revisi_laporan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(JadwalSidang::class);
            $table->foreignIdFor(LaporanAkhir::class);
            $table->text('judul_revisi_laporan')->nullable();
            $table->string('file_revisi_laporan', 510)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->foreignIdFor(Dosen::class, 'penguji')->nullable();
            $table->string('file_penguji', 510)->nullable();
            $table->string('file_random_penguji', 510)->nullable();
            $table->tinyInteger('status_approval_penguji')->nullable();
            $table->text('note_penguji')->nullable();
            $table->date('tanggal_approval_penguji')->nullable();
            $table->foreignIdFor(Dosen::class, 'ketua_sidang')->nullable();
            $table->string('file_ketua_sidang', 510)->nullable();
            $table->string('file_random_ketua_sidang', 510)->nullable();
            $table->tinyInteger('status_approval_ketua_sidang')->nullable();
            $table->text('note_ketua_sidang')->nullable();
            $table->date('tanggal_approval_ketua_sidang')->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing1')->nullable();
            $table->string('file_pembimbing1', 510)->nullable();
            $table->string('file_random_pembimbing1', 510)->nullable();
            $table->tinyInteger('status_approval_pembimbing1')->nullable();
            $table->text('note_pembimbing1')->nullable();
            $table->date('tanggal_approval_pembimbing1')->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing2')->nullable();
            $table->string('file_pembimbing2', 510)->nullable();
            $table->string('file_random_pembimbing2', 510)->nullable();
            $table->tinyInteger('status_approval_pembimbing2')->nullable();
            $table->text('note_pembimbing2')->nullable();
            $table->date('tanggal_approval_pembimbing2')->nullable();
            $table->string('file_kaprodi', 510)->nullable();
            $table->string('file_random_kaprodi', 510)->nullable();
            $table->tinyInteger('status_approval_kaprodi')->nullable();
            $table->text('note_kaprodi')->nullable();
            $table->date('tanggal_approval_kaprodi')->nullable();
            $table->tinyInteger('status_akhir')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisi_laporan');
    }
};