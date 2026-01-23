<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScholarshipProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('scholarship_programs')->insert([
            [
                'scholarship_program_name' => 'CMSP',
                'description' => 'CHED Merit Scholarship Program',
                'total_slot' => 100,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'ESTATISKOLAR',
                'description' => 'Scholarship Program for Future Statisticians',
                'total_slot' => 50,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'CoScho',
                'description' => 'Scholarship Program for Coconut Farmers and their Families',
                'total_slot' => 70,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'MSRS',
                'description' => 'Medical Scholarship and Return Service',
                'total_slot' => 80,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'SIDA-SGP',
                'description' => 'Scholarship for Children and Dependents of Sugarcane Industry Workers and Small Sugarcane Farmers',
                'total_slot' => 60,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'ACEF-GIAHEP',
                'description' => 'Agricultural Competitiveness Enhancement Fund – Grants-in-Aid for Higher Education Program',
                'total_slot' => 90,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'MTP-SP',
                'description' => 'Medical Technologists and Pharmacists Scholarship Program',
                'total_slot' => 40,
                
    
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'CGMS-SUCs',
                'description' => 'Cash Grant to Medical Students Enrolled in State Universities and Colleges',
                'total_slot' => 75,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'SNPLP',
                'description' => 'Cash Grant to Medical Students Enrolled in State Universities and Colleges',
                'total_slot' => 30,
                
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
