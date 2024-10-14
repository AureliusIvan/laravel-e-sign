<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
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
        Schema::create('posters', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(Mahasiswa::class);
            $table->string('file_poster', 510);
            $table->string('file_poster_random', 510);
            $table->string('file_poster_mime', 510);
            $table->tinyInteger('status');
            $table->foreignIdFor(Dosen::class, 'pembimbing1');
            $table->tinyInteger('status_approval_pembimbing1')->nullable();
            $table->timestamps();
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('pembimbing1')->references('id')->on('dosen')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posters');
    }
};
