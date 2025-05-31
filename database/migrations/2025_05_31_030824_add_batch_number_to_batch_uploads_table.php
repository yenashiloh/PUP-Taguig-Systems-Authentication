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
        Schema::table('batch_uploads', function (Blueprint $table) {
            $table->integer('batch_number')->nullable()->after('total_rows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_uploads', function (Blueprint $table) {
            $table->dropColumn('batch_number');
        });
    }
};
