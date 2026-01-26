<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            ScholarshipSeeder::class,
            
        ]);

        //Jed added separate seeder for scholarship programs
        $this->call([
            ScholarshipProgramsSeeder::class
        ]);
    
    }
}