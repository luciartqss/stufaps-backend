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
            $table->unsignedBigInteger('program_id');
            $table->string('scholarship_program_name')->nullable(); // ✅ added
            $table->string('academic_year');
            $table->integer('total_slot')->nullable();
            $table->integer('filled_slot')->nullable();
            $table->integer('unfilled_slot')->nullable();
            $table->timestamps();

            // ✅ Correct foreign key reference
            $table->foreign('program_id') 
                ->references('id') 
                ->on('scholarship_program_records') 
                ->onDelete('cascade'); 

            $table->unique(['program_id', 'academic_year']);
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
