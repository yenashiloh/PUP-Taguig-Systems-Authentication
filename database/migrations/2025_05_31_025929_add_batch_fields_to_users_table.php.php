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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('batch_number')->nullable()->after('status');
            $table->year('school_year')->nullable()->after('batch_number');
            
            $table->index(['batch_number', 'school_year']);
            $table->index('school_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['batch_number', 'school_year']);
            $table->dropIndex(['school_year']);
            $table->dropColumn(['batch_number', 'school_year']);
        });
    }
};
