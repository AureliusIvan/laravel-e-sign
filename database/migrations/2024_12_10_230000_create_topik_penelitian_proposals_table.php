<?php

use App\Models\ProposalSkripsi;
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
        Schema::create('topik_penelitian_proposal', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignIdFor(ProposalSkripsi::class);
            $table->foreignIdFor(ResearchList::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topik_penelitian_proposal');
    }
}; 