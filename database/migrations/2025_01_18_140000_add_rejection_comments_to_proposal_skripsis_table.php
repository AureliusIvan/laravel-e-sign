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
            $table->text('rejection_comment_penilai1')->nullable()->after('tanggal_approval_penilai1');
            $table->text('rejection_comment_penilai2')->nullable()->after('tanggal_approval_penilai2');
            $table->text('rejection_comment_penilai3')->nullable()->after('tanggal_approval_penilai3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposal_skripsi', function (Blueprint $table) {
            $table->dropColumn(['rejection_comment_penilai1', 'rejection_comment_penilai2', 'rejection_comment_penilai3']);
        });
    }
}; 