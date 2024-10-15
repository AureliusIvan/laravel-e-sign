<?php

use App\Models\Dosen;
use App\Models\KategoriNilaiDetail;
use App\Models\Mahasiswa;
use App\Models\NilaiAkhir;
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
        Schema::create('nilai_sidang', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(Mahasiswa::class);
            $table->foreignIdFor(Dosen::class);
            $table->foreignIdFor(NilaiAkhir::class);
            $table->foreignIdFor(KategoriNilaiDetail::class);
            $table->float('nilai');
            $table->boolean('is_editable')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_sidang');
    }
};
