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
        Schema::create('user_validations', function (Blueprint $table) {
            $table->id();
            $table->string('validation_type'); // 'student_number' or 'employee_number'
            $table->integer('min_digits')->default(1);
            $table->integer('max_digits')->default(20);
            $table->boolean('numbers_only')->default(true);
            $table->boolean('letters_only')->default(false);
            $table->boolean('letters_symbols_numbers')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_validations');
    }
};
