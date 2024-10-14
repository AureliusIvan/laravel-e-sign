<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProposalSkripsiForm;
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
        Schema::create('proposal_skripsi', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(ProposalSkripsiForm::class);
            $table->foreignIdFor(Mahasiswa::class);
            $table->text('judul_proposal');
            $table->string('file_proposal', 510);
            $table->string('file_proposal_random', 510);
            $table->tinyInteger('status');
            $table->foreignIdFor(Dosen::class, 'penilai1')->nullable();
            $table->string('file_penilai1', 510)->nullable();
            $table->string('file_random_penilai1', 510)->nullable();
            $table->tinyInteger('status_approval_penilai1')->nullable();
            $table->date('tanggal_approval_penilai1')->nullable();
            $table->foreignIdFor(Dosen::class, 'penilai2')->nullable();
            $table->string('file_penilai2', 510)->nullable();
            $table->string('file_random_penilai2', 510)->nullable();
            $table->tinyInteger('status_approval_penilai2')->nullable();
            $table->date('tanggal_approval_penilai2')->nullable();
            $table->foreignIdFor(Dosen::class, 'penilai3')->nullable();
            $table->string('file_penilai3', 510)->nullable();
            $table->string('file_random_penilai3', 510)->nullable();
            $table->tinyInteger('status_approval_penilai3')->nullable();
            $table->date('tanggal_approval_penilai3')->nullable();
            $table->tinyInteger('status_akhir')->nullable();
            $table->string('available_at', 20)->nullable();
            $table->string('available_until', 20)->nullable();
            $table->boolean('is_expired')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_skripsi');
    }
};
