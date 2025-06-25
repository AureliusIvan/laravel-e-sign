<?php

use App\Models\Dosen;
use App\Models\TahunAjaran;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('proposal_skripsi_rti', function (Blueprint $table) {
    //         $table->id();
    //         $table->uuid('uuid')->unique();
    //         $table->foreignIdFor(TahunAjaran::class);
    //         $table->foreignIdFor(ProgramStudi::class);
    //         $table->text('judul_proposal');
    //         $table->string('file_proposal', 510);
    //         $table->string('file_proposal_random', 510);
    //         $table->tinyInteger('status');
    //         $table->foreignIdFor(Dosen::class, 'pembimbing1')->nullable();
    //         $table->foreignIdFor(Dosen::class, 'pembimbing2')->nullable();
    //         $table->tinyInteger('status_akhir')->nullable();
    //         $table->smallInteger('available_at_tahun')->nullable();
    //         $table->string('available_at_semester', 20)->nullable();
    //         $table->smallInteger('available_until_tahun')->nullable();
    //         $table->string('available_until_semester', 20)->nullable();
    //         $table->boolean('is_expired')->nullable();
    //         $table->timestamps();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::dropIfExists('proposal_skripsi_rti');
    // }
};