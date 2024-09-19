<?php

use App\Models\Pengaturan;
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
        Schema::create('pengaturan_detail', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(Pengaturan::class);
            $table->smallInteger('kuota_pembimbing_pertama')->nullable();
            $table->smallInteger('kuota_pembimbing_kedua')->nullable();
            $table->smallInteger('minimum_jumlah_bimbingan')->nullable();
            $table->smallInteger('tahun_pembimbing_tersedia_sampai')->nullable();
            $table->string('semester_pembimbing_tersedia_sampai', 10)->nullable();
            $table->smallInteger('tahun_rti_tersedia_sampai')->nullable();
            $table->string('semester_rti_tersedia_sampai', 10)->nullable();
            $table->smallInteger('tahun_proposal_lama_tersedia_sampai')->nullable();
            $table->string('semester_proposal_lama_tersedia_sampai', 10)->nullable();
            $table->smallInteger('tahun_proposal_tersedia_sampai')->nullable();
            $table->string('semester_proposal_tersedia_sampai', 10)->nullable();
            $table->string('penamaan_proposal')->nullable();
            $table->string('penamaan_revisi_proposal')->nullable();
            $table->string('penamaan_laporan')->nullable();
            $table->string('penamaan_revisi_laporan')->nullable();
            $table->smallInteger('jumlah_setuju_proposal_satupembimbing')->nullable();
            $table->smallInteger('jumlah_setuju_proposal_duapembimbing')->nullable();
            $table->smallInteger('jumlah_setuju_sidang_satupembimbing')->nullable();
            $table->smallInteger('jumlah_setuju_sidang_duapembimbing')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_detail');
    }
};
