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
                'filled_slot' => 40,
                'unfilled_slot' => 60,
                'in_charge' => 'Coordinator A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'ESTATISKOLAR',
                'description' => 'Scholarship Program for Future Statisticians',
                'total_slot' => 50,
                'filled_slot' => 20,
                'unfilled_slot' => 30,
                'in_charge' => 'Coordinator B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'CoScho',
                'description' => 'Scholarship Program for Coconut Farmers and their Families',
                'total_slot' => 70,
                'filled_slot' => 35,
                'unfilled_slot' => 35,
                'in_charge' => 'Coordinator C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'MSRS',
                'description' => 'Medical Scholarship and Return Service',
                'total_slot' => 80,
                'filled_slot' => 50,
                'unfilled_slot' => 30,
                'in_charge' => 'Coordinator D',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'SIDA-SGP',
                'description' => 'Scholarship for Children and Dependents of Sugarcane Industry Workers and Small Sugarcane Farmers',
                'total_slot' => 60,
                'filled_slot' => 25,
                'unfilled_slot' => 35,
                'in_charge' => 'Coordinator E',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'ACEF-GIAHEP',
                'description' => 'Agricultural Competitiveness Enhancement Fund – Grants-in-Aid for Higher Education Program',
                'total_slot' => 90,
                'filled_slot' => 45,
                'unfilled_slot' => 45,
                'in_charge' => 'Coordinator F',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'MTP-SP',
                'description' => 'Medical Technologists and Pharmacists Scholarship Program',
                'total_slot' => 40,
                'filled_slot' => 15,
                'unfilled_slot' => 25,
                'in_charge' => 'Coordinator G',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'CGMS-SUCs',
                'description' => 'Cash Grant to Medical Students Enrolled in State Universities and Colleges',
                'total_slot' => 75,
                'filled_slot' => 60,
                'unfilled_slot' => 15,
                'in_charge' => 'Coordinator H',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scholarship_program_name' => 'SNPLP',
                'description' => 'Cash Grant to Medical Students Enrolled in State Universities and Colleges',
                'total_slot' => 30,
                'filled_slot' => 10,
                'unfilled_slot' => 20,
                'in_charge' => 'Coordinator I',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
