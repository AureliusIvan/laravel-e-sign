<?php

use App\Models\Dosen;
use App\Models\JadwalSidang;
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
        Schema::create('nilai_akhir', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(TahunAjaran::class);
            $table->foreignIdFor(ProgramStudi::class);
            $table->foreignIdFor(JadwalSidang::class)->nullable();
            $table->foreignIdFor(Mahasiswa::class)->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing1')->nullable();
            $table->float('nilai_pembimbing1')->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing2')->nullable();
            $table->float('nilai_pembimbing2')->nullable();
            $table->float('total_nilai_pembimbing')->nullable();
            $table->foreignIdFor(Dosen::class, 'penguji')->nullable();
            $table->float('nilai_penguji')->nullable();
            $table->foreignIdFor(Dosen::class, 'ketua_sidang')->nullable();
            $table->float('nilai_ketua_sidang')->nullable();
            $table->float('nilai_akhir')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir');
    }
};
