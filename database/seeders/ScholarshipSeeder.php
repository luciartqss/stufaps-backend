<?php

namespace Database\Seeders;

use App\Models\Disbursement;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ScholarshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 students with varying statuses
        Student::factory()
            ->count(30)
            ->ongoing()
            ->create()
            ->each(function ($student) {
                // Create 1-4 disbursements for each ongoing student
                Disbursement::factory()
                    ->count(rand(1, 4))
                    ->create(['student_seq' => $student->seq]);
            });

        Student::factory()
            ->count(15)
            ->graduated()
            ->create()
            ->each(function ($student) {
                // Create 4-8 disbursements for graduated students
                Disbursement::factory()
                    ->count(rand(4, 8))
                    ->create(['student_seq' => $student->seq]);
            });

        Student::factory()
            ->count(5)
            ->terminated()
            ->create()
            ->each(function ($student) {
                // Create 1-3 disbursements for terminated students
                Disbursement::factory()
                    ->count(rand(1, 3))
                    ->create(['student_seq' => $student->seq]);
            });
    }
}