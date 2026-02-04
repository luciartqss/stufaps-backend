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
        Schema::create('program_offering_entries', function (Blueprint $table) {
            $table->id();
            $table->string('uii', 50)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('hei_name');
            $table->string('municipality_city')->nullable()->index();
            $table->string('province')->nullable()->index();
            $table->string('institutional_type')->nullable();
            $table->string('program')->nullable();
            $table->string('major_specialization')->nullable();
            $table->string('discipline_group')->nullable();
            $table->string('program_level')->nullable();
            $table->text('ga_level_i')->nullable();
            $table->text('ga_level_ii')->nullable();
            $table->text('ga_level_iii')->nullable();
            $table->text('ga_level_iv')->nullable();
            $table->text('ga_level_v')->nullable();
            $table->text('ga_level_vi')->nullable();
            $table->string('accreditation_level')->nullable();
            $table->string('accreditation_accreditor')->nullable();
            $table->string('accreditation_validity')->nullable();
            $table->string('coe_cod')->nullable();
            $table->string('validity')->nullable();
            $table->string('gpr')->nullable();
            $table->string('gp_gr_no')->nullable();
            $table->string('series')->nullable();
            $table->string('issued_by')->nullable();
            $table->text('remarks')->nullable();
            $table->text('remarks2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_offering_entries');
    }
};
