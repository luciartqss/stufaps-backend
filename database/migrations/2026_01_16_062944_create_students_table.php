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
            $table->string('in_charge');
            $table->year('award_year');
            $table->string('scholarship_program');
            $table->string('award_number');
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('extension')->nullable();
            $table->enum('sex', ['Male', 'Female']);
            $table->date('date_of_birth');
            $table->string('contact_number');
            $table->string('email_address');
            $table->string('street_brgy');
            $table->string('municipality_city');
            $table->string('province');
            $table->string('congressional_district');
            $table->string('zip_code');
            $table->enum('special_group', ['IP', 'PWD', 'Solo Parent'])->nullable();
            $table->string('certification_number')->nullable();
            $table->string('name_of_institution');
            $table->string('uii');
            $table->string('institutional_type');
            $table->string('region');
            $table->string('degree_program');
            $table->string('program_major');
            $table->string('program_discipline');
            $table->enum('program_degree_level', ['Pre-baccalaureate', 'Baccalaureate', 'Post Baccalaureate', 'Masters', 'Doctorate']);
            $table->enum('authority_type', ['GP', 'GR', 'RRPA', 'COPC']);
            $table->string('authority_number');
            $table->string('series');
            $table->boolean('is_priority')->default(false);
            $table->string('basis_cmo')->nullable();
            $table->enum('scholarship_status', ['On-going', 'Graduated', 'Terminated'])->default('On-going');
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
