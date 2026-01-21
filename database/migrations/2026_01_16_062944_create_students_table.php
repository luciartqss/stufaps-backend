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
        Schema::create('students', function (Blueprint $table) {
            $table->id('seq');
            $table->string('in_charge')->nullable();
            $table->year('award_year')->nullable();
            $table->string('scholarship_program')->nullable();
            $table->string('award_number')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('extension')->nullable();
            $table->enum('sex', ['Male', 'Female'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('street_brgy')->nullable();
            $table->string('municipality_city')->nullable();
            $table->string('province')->nullable();
            $table->string('congressional_district')->nullable();
            $table->string('zip_code')->nullable();
            $table->enum('special_group', ['IP', 'PWD', 'Solo Parent'])->nullable();
            $table->string('certification_number')->nullable();
            $table->string('name_of_institution')->nullable();
            $table->string('uii')->nullable();
            $table->string('institutional_type')->nullable();
            $table->string('region')->nullable();
            $table->string('degree_program')->nullable();
            $table->string('program_major')->nullable();
            $table->string('program_discipline')->nullable();
            $table->enum('program_degree_level', ['Pre-baccalaureate', 'Baccalaureate', 'Post Baccalaureate', 'Masters', 'Doctorate'])->nullable();
            $table->enum('authority_type', ['GP', 'GR', 'RRPA', 'COPC'])->nullable();
            $table->string('authority_number')->nullable();
            $table->string('series')->nullable();
            $table->boolean('is_priority')->nullable();
            $table->string('basis_cmo')->nullable();
            $table->enum('scholarship_status', ['On-going', 'Graduated', 'Terminated'])->nullable();
            $table->text('replacement_info')->nullable();
            $table->text('termination_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
