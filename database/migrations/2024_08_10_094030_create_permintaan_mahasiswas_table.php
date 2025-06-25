<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PermintaanMahasiswaForm;
use App\Models\ResearchList;
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
        Schema::create('permintaan_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(PermintaanMahasiswaForm::class);
            $table->foreignIdFor(Mahasiswa::class);
            $table->foreignIdFor(Dosen::class);
            $table->foreignIdFor(ResearchList::class);
            $table->tinyInteger('is_rti')->nullable();
            $table->tinyInteger('is_uploaded')->nullable();
            $table->string('file_pendukung', 510)->nullable();
            $table->string('file_pendukung_random')->nullable();
            $table->text('note_mahasiswa')->nullable();
            $table->tinyInteger('status_pembimbing')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->text('note_dosen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_mahasiswa');
    }
};
