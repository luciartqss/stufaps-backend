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
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_seq');
            $table->string('academic_year');
            $table->string('semester');
            $table->enum('curriculum_year_level', ['I', 'II', 'III', 'IV', 'V', 'VI']);
            $table->string('nta');
            $table->string('fund_source');
            $table->decimal('amount', 12, 2);
            $table->string('voucher_number');
            $table->enum('mode_of_payment', ['ATM', 'Cheque', 'Through the HEI']);
            $table->string('account_check_no')->nullable();
            $table->decimal('payment_amount', 12, 2);
            $table->string('lddap_number')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('student_seq')
                  ->references('seq')
                  ->on('students')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
