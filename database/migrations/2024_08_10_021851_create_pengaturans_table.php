<?php

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
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(TahunAjaran::class);
            $table->foreignIdFor(ProgramStudi::class);
            $table->boolean('kuota_bimbingan_kedua')->nullable();
            $table->boolean('upload_proposal_lama')->nullable();
            $table->boolean('proposal_lama_expired')->nullable();
            $table->boolean('proposal_expired')->nullable();
            $table->boolean('penamaan_proposal')->nullable();
            $table->boolean('penamaan_revisi_proposal')->nullable();
            $table->boolean('penamaan_laporan')->nullable();
            $table->boolean('penamaan_revisi_laporan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
