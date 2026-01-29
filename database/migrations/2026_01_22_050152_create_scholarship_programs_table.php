<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scholarship_programs', function (Blueprint $table) {
            $table->id();
            $table->string('scholarship_program_name');
            $table->string('description')->nullable();
            $table->integer('total_slot')->nullable();
            $table->integer('filled_slot')->nullable();
            $table->integer('unfilled_slot')->nullable();
            $table->string('academic_year')->nullable();
            $table->timestamps();

            // Short index name to avoid MySQL identifier length limits
            $table->unique(['scholarship_program_name', 'academic_year'], 'sp_name_ay_unique');
            $table->string('academic_year')->nullable();;     
            $table->timestamps();

   

        });

    
        DB::table('scholarship_programs')->insert([
            ['scholarship_program_name' => 'CMSP', 'description' => 'CHED Merit Scholarship Program', 'total_slot' => 100, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'Estatistikolar', 'description' => 'Statistics-focused scholarship', 'total_slot' => 50, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'CoScho', 'description' => 'College Scholarship Program', 'total_slot' => 70, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'MSRS', 'description' => 'Medical Scholarship and Return Service', 'total_slot' => 80, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'SIDA-SGP', 'description' => 'Sugarcane Industry Devt. Act', 'total_slot' => 60, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'ACEF-GIAHEP', 'description' => 'Agricultural Competitiveness Enhancement Fund', 'total_slot' => 90, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'MTP-SP', 'description' => 'Maritime Training Program', 'total_slot' => 75, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'CGMS-SUCs', 'description' => 'CHED Grants for SUCs', 'total_slot' => 85, 'academic_year' => '2024-2025'],
            ['scholarship_program_name' => 'SNPLP', 'description' => 'Student Loan Program', 'total_slot' => 120, 'academic_year' => '2024-2025'],
        ]);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_programs');
    }
};
