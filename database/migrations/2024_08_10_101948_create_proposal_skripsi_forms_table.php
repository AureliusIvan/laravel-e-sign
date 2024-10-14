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
        Schema::create('proposal_skripsi_form', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(TahunAjaran::class);
            $table->foreignIdFor(ProgramStudi::class);
            $table->text('judul_form');
            $table->text('keterangan')->nullable();
            $table->dateTime('dibuka');
            $table->dateTime('ditutup');
            $table->dateTime('deadline_penilaian')->nullable();
            $table->tinyInteger('publish_dosen')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_skripsi_form');
    }
};
