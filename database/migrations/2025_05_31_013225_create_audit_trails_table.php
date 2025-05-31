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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('admin_email');
            $table->string('admin_name');
            $table->string('action'); // 'add_student', 'batch_upload_students', 'add_faculty', 'batch_upload_faculty', etc.
            $table->string('target_type'); // 'user', 'department', 'course', etc.
            $table->string('target_id')->nullable(); // ID of the affected record
            $table->string('target_name')->nullable(); // Name of the affected record
            $table->json('details')->nullable(); // Additional details about the action
            $table->text('description'); // Human-readable description
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['admin_email', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
