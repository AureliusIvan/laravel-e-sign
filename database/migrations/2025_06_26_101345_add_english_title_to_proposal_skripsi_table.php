<?php

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
        Schema::table('proposal_skripsi', function (Blueprint $table) {
            $table->text('judul_proposal_en')->nullable()->after('judul_proposal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposal_skripsi', function (Blueprint $table) {
            $table->dropColumn('judul_proposal_en');
        });
    }
};
