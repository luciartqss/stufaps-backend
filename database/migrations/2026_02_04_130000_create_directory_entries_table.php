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
        Schema::create('directory_entries', function (Blueprint $table) {
            $table->id();
            $table->string('uii', 50)->unique();
            $table->string('name');
            $table->string('name_registered_sec')->nullable();
            $table->text('former_names')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->string('remarks_status')->nullable();
            $table->string('institutional_type')->nullable()->index();
            $table->string('sector', 50)->nullable();
            $table->unsignedSmallInteger('year_established')->nullable();
            $table->string('autonomous_status')->nullable();
            $table->string('autonomous_validity')->nullable();
            $table->text('complete_address')->nullable();
            $table->string('street_brgy')->nullable();
            $table->string('municipality_city')->nullable()->index();
            $table->string('province')->nullable()->index();
            $table->string('district')->nullable();
            $table->string('contact_numbers')->nullable();
            $table->string('mobile_numbers')->nullable();
            $table->string('email_address')->nullable();
            $table->string('head_name')->nullable();
            $table->string('head_designation')->nullable();
            $table->string('head_telephone')->nullable();
            $table->string('head_mobile')->nullable();
            $table->string('head_email')->nullable();
            $table->string('registrar_name')->nullable();
            $table->string('registrar_telephone')->nullable();
            $table->string('registrar_mobile')->nullable();
            $table->string('registrar_email')->nullable();
            $table->text('additional_remarks')->nullable();
            // optional alternate fields present in the JSON
            $table->string('head_name_alt')->nullable();
            $table->string('head_designation_alt')->nullable();
            $table->string('name_alt')->nullable();
            $table->text('complete_address_alt')->nullable();
            $table->string('email_address_alt')->nullable();
            $table->string('head_email_alt')->nullable();
            $table->string('registrar_email_alt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directory_entries');
    }
};
