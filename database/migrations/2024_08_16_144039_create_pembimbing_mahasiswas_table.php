<?php

use App\Models\Dosen;
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
        Schema::create('pembimbing_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(TahunAjaran::class);
            $table->foreignIdFor(ProgramStudi::class);
            $table->foreignIdFor(Mahasiswa::class, 'mahasiswa')->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing1')->nullable();
            $table->foreignIdFor(Dosen::class, 'pembimbing2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembimbing_mahasiswa');
    }
};
