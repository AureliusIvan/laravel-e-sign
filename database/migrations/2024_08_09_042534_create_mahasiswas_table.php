<?php

use App\Models\ProgramStudi;
use App\Models\TahunAjaran;
use App\Models\User;
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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(User::class);
            $table->string('nim', 20)->unique();
            $table->string('nama');
            $table->foreignIdFor(ProgramStudi::class);
            $table->smallInteger('angkatan');
            $table->foreignIdFor(TahunAjaran::class)->nullable();
            $table->boolean('status_aktif_skripsi')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
