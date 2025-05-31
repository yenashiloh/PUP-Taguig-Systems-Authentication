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
        Schema::create('batch_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->string('admin_email');
            $table->string('admin_name');
            $table->string('upload_type'); // 'students' or 'faculty'
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('total_rows');
            $table->integer('successful_imports')->default(0);
            $table->integer('failed_imports')->default(0);
            $table->json('import_summary')->nullable();
            $table->json('errors')->nullable();
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['admin_email', 'created_at']);
            $table->index(['upload_type', 'status']);
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_uploads');
    }
};
