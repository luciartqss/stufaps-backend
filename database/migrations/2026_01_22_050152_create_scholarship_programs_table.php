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
                    
            $table->timestamps();

        });

    
        DB::table('scholarship_programs')->insert([
            ['scholarship_program_name' => 'CMSP', 'description' => 'CHED Merit Scholarship Program', 'total_slot' => 100],
            ['scholarship_program_name' => 'Estatistikolar', 'description' => 'Statistics-focused scholarship', 'total_slot' => 50],
            ['scholarship_program_name' => 'CoScho', 'description' => 'College Scholarship Program', 'total_slot' => 70],
            ['scholarship_program_name' => 'MSRS', 'description' => 'Medical Scholarship and Return Service', 'total_slot' => 80],
            ['scholarship_program_name' => 'SIDA-SGP', 'description' => 'Sugarcane Industry Devt. Act', 'total_slot' => 60],
            ['scholarship_program_name' => 'ACEF-GIAHEP', 'description' => 'Agricultural Competitiveness Enhancement Fund', 'total_slot' => 90],
            ['scholarship_program_name' => 'MTP-SP', 'description' => 'Maritime Training Program', 'total_slot' => 75],
            ['scholarship_program_name' => 'CGMS-SUCs', 'description' => 'CHED Grants for SUCs', 'total_slot' => 85],
            ['scholarship_program_name' => 'SNPLP', 'description' => 'Student Loan Program', 'total_slot' => 120],
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
