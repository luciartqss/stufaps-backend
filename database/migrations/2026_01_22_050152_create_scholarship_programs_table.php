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
        Schema::create('scholarship_programs', function (Blueprint $table) {
            $table->id();
            $table->string('scholarship_program_name')->unique();
            $table->string('description')->nullable();
            $table->integer('total_slot')->nullable();
            $table->integer('filled_slot')->nullable();
            $table->integer('unfilled_slot')->nullable();
            $table->string('in_charge')->nullable();    

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_programs');
    }
};
