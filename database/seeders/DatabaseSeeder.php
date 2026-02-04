<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DirectoryEntriesSeeder;
use Database\Seeders\ProgramOfferingEntriesSeeder;
use Database\Seeders\ScholarshipSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            DirectoryEntriesSeeder::class,
            ProgramOfferingEntriesSeeder::class,
        ]);
    }
}