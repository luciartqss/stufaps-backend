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
            $table->timestamps();

        });
    
        DB::table('scholarship_program_records')->insert([
            ['scholarship_program_name' => 'CMSP', 'description' => 'CHED Merit Scholarship Program'],
            ['scholarship_program_name' => 'Estatistikolar', 'description' => 'Statistics-focused scholarship'],
            ['scholarship_program_name' => 'CoScho', 'description' => 'College Scholarship Program'],
            ['scholarship_program_name' => 'MSRS', 'description' => 'Medical Scholarship and Return Service'],
            ['scholarship_program_name' => 'SIDA-SGP', 'description' => 'Sugarcane Industry Devt. Act'],
            ['scholarship_program_name' => 'ACEF-GIAHEP', 'description' => 'Agricultural Competitiveness Enhancement Fund'],
            ['scholarship_program_name' => 'MTP-SP', 'description' => 'Maritime Training Program'],
            ['scholarship_program_name' => 'CGMS-SUCs', 'description' => 'CHED Grants for SUCs'],
            ['scholarship_program_name' => 'SNPLP', 'description' => 'Student Loan Program' ],
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
