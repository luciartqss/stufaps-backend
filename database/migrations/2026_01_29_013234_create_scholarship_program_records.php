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
        Schema::create('scholarship_program_records', function (Blueprint $table) {
            $table->id();
            $table->string('scholarship_program_name')->nullable();
            $table->string('description')->nullable();
            $table->integer('total_slot')->nullable();
            $table->string('Academic_year')->nullable();
            $table->timestamps();

        });
    
        DB::table('scholarship_program_records')->insert([
            ['scholarship_program_name' => 'CMSP', 'description' => 'CHED Merit Scholarship Program', 'total_slot' => 100, 'Academic_year' => '2021-2022'],
            ['scholarship_program_name' => 'Estatistikolar', 'description' => 'Statistics-focused scholarship', 'total_slot' => 50, 'Academic_year' => '2021-2022'],
            ['scholarship_program_name' => 'CoScho', 'description' => 'College Scholarship Program', 'total_slot' => 200, 'Academic_year' => '2021-2022'],
            ['scholarship_program_name' => 'MSRS', 'description' => 'Medical Scholarship and Return Service', 'total_slot' => 75, 'Academic_year' => '2021-2022'],
            ['scholarship_program_name' => 'SIDA-SGP', 'description' => 'Sugarcane Industry Devt. Act', 'total_slot' => 150, 'Academic_year' => '2021-2022'],
            ['scholarship_program_name' => 'ACEF-GIAHEP', 'description' => 'Agricultural Competitiveness Enhancement Fund', 'total_slot' => 125, 'Academic_year' => '2021-2022' ],
            ['scholarship_program_name' => 'MTP-SP', 'description' => 'Maritime Training Program', 'total_slot' => 100, 'Academic_year' => '2021-2022'],
            ['scholarship_program_name' => 'CGMS-SUCs', 'description' => 'CHED Grants for SUCs', 'total_slot' => 250, 'Academic_year' => '2021-2022'],
            ['scholarship_program_name' => 'SNPLP', 'description' => 'Student Loan Program', 'total_slot' => 300, 'Academic_year' => '2021-2022'],
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
