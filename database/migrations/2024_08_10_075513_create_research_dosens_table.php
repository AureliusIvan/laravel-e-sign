<?php

use App\Models\Dosen;
use App\Models\ProgramStudi;
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
        Schema::create('research_dosen', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(ProgramStudi::class);
            $table->foreignIdFor(Dosen::class);
            $table->foreignIdFor(ResearchList::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_dosen');
    }
};
