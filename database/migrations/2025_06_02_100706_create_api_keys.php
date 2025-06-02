<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_name')->unique();
            $table->string('key_hash'); // Hashed version of the actual key
            $table->string('application_name');
            $table->string('developer_name');
            $table->string('developer_email');
            $table->text('description')->nullable();
            $table->json('allowed_domains')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('request_limit_per_minute')->default(100);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->integer('total_requests')->default(0);
            $table->unsignedBigInteger('created_by'); 
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('admins');
            $table->index(['key_hash', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
};
