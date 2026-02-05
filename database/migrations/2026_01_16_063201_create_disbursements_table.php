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
            $table->string('academic_year')->nullable();
            $table->string('semester')->nullable();
            $table->enum('curriculum_year_level', ['I', 'II', 'III', 'IV', 'V', 'VI'])->nullable();
            $table->string('nta')->nullable();
            $table->string('fund_source')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('voucher_number')->nullable();
            $table->enum('mode_of_payment', ['ATM', 'Cheque', 'Through the HEI'])->nullable();
            $table->string('account_check_no')->nullable();
            $table->decimal('payment_amount', 12, 2)->nullable();
            $table->string('lddap_number')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->string('status')->nullable();
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
