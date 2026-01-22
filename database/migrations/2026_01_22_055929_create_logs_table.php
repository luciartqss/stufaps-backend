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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('model'); // 'Student' or 'Disbursement'
            $table->unsignedBigInteger('model_id'); // ID of the affected record
            $table->enum('action', ['create', 'update', 'delete']); // CRUD action
            $table->json('old_data')->nullable(); // Data before change
            $table->json('new_data')->nullable(); // Data after change
            $table->string('changed_fields')->nullable(); // Comma-separated list of changed fields
            $table->unsignedBigInteger('user_id')->nullable(); // User who made the change
            $table->string('ip_address')->nullable(); // IP address of the user
            $table->timestamps();

            // Indexes for faster queries
            $table->index('model');
            $table->index('model_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
