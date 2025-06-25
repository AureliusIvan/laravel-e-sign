<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProposalSkripsi;
use App\Models\RevisiProposalForm;
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
        Schema::create('revisi_proposal', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(ProposalSkripsi::class);
            $table->foreignIdFor(RevisiProposalForm::class);
            $table->foreignIdFor(Mahasiswa::class);
            $table->text('judul_revisi_proposal');
            $table->string('file_revisi_proposal', 510);
            $table->string('file_revisi_proposal_random', 510);
            $table->tinyInteger('status');
            $table->foreignIdFor(Dosen::class, 'penilai1')->nullable();
            $table->string('file_revisi_penilai1', 510)->nullable();
            $table->string('file_revisi_random_penilai1', 510)->nullable();
            $table->tinyInteger('status_revisi_approval_penilai1')->nullable();
            $table->text('note_revisi_penilai1')->nullable();
            $table->date('tanggal_approval_revisi_penilai1')->nullable();
            $table->foreignIdFor(Dosen::class, 'penilai2')->nullable();
            $table->string('file_revisi_penilai2', 510)->nullable();
            $table->string('file_revisi_random_penilai2', 510)->nullable();
            $table->tinyInteger('status_revisi_approval_penilai2')->nullable();
            $table->text('note_revisi_penilai2')->nullable();
            $table->date('tanggal_approval_revisi_penilai2')->nullable();
            $table->foreignIdFor(Dosen::class, 'penilai3')->nullable();
            $table->string('file_revisi_penilai3', 510)->nullable();
            $table->string('file_revisi_random_penilai3', 510)->nullable();
            $table->tinyInteger('status_revisi_approval_penilai3')->nullable();
            $table->text('note_revisi_penilai3')->nullable();
            $table->date('tanggal_approval_revisi_penilai3')->nullable();
            $table->tinyInteger('status_akhir')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisi_proposal');
    }
};
