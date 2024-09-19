<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
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
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignIdFor(Mahasiswa::class);
            $table->string('file_jurnal', 510);
            $table->string('file_jurnal_random', 510);
            $table->string('file_jurnal_mime', 510);
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
        Schema::dropIfExists('jurnal');
    }
};
