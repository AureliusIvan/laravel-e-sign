<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\RevisiProposal;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_akhir', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(RevisiProposal::class);
            $table->foreignIdFor(Mahasiswa::class);
            $table->text('judul_laporan');
            $table->string('file_laporan', 510);
            $table->string('file_laporan_random', 510);
            $table->tinyInteger('status');
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
        Schema::dropIfExists('laporan_akhir');
    }
};
