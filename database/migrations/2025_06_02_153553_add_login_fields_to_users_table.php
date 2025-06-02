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
        // Add API authentication fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->text('api_session_token')->nullable()->after('password');
            $table->timestamp('last_login_at')->nullable()->after('updated_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });

        // Update API keys permissions to include login_user
        Schema::table('api_keys', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['api_session_token', 'last_login_at', 'last_login_ip']);
        });
    }
};
