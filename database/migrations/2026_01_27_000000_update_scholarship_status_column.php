<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function () {
            // noop to keep Schema facade import; raw statements below
        });

        // Use raw SQL to avoid doctrine/dbal requirement for changing column types
        DB::statement("ALTER TABLE students MODIFY award_year VARCHAR(191) NULL");
        DB::statement("ALTER TABLE students MODIFY sex VARCHAR(191) NULL");
        DB::statement("ALTER TABLE students MODIFY date_of_birth VARCHAR(191) NULL");
        DB::statement("ALTER TABLE students MODIFY special_group VARCHAR(191) NULL");
        DB::statement("ALTER TABLE students MODIFY program_degree_level VARCHAR(191) NULL");
        DB::statement("ALTER TABLE students MODIFY authority_type VARCHAR(191) NULL");
        DB::statement("ALTER TABLE students MODIFY is_priority VARCHAR(191) NULL");
        DB::statement("ALTER TABLE students MODIFY scholarship_status VARCHAR(191) NULL");
    }

    public function down(): void
    {
        Schema::table('students', function () {
            // noop to keep Schema facade import; raw statements below
        });

        DB::statement("ALTER TABLE students MODIFY award_year YEAR NULL");
        DB::statement("ALTER TABLE students MODIFY sex ENUM('Male','Female') NULL");
        DB::statement("ALTER TABLE students MODIFY date_of_birth DATE NULL");
        DB::statement("ALTER TABLE students MODIFY special_group ENUM('IP','PWD','Solo Parent') NULL");
        DB::statement("ALTER TABLE students MODIFY program_degree_level ENUM('Pre-baccalaureate','Baccalaureate','Post Baccalaureate','Masters','Doctorate') NULL");
        DB::statement("ALTER TABLE students MODIFY authority_type ENUM('GP','GR','RRPA','COPC') NULL");
        DB::statement("ALTER TABLE students MODIFY is_priority TINYINT(1) NULL");
        DB::statement("ALTER TABLE students MODIFY scholarship_status ENUM('On-going','Graduated','Terminated') NULL");
    }
};
