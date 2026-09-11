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
        Schema::create('offline_search_lms_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clientacct_id')->nullable();
            $table->bigInteger('mis_no')->unique();
            $table->string('reference_no')->unique();
            $table->string('agreement_id');
            $table->string('agreement_no');
            $table->date('date_sold');
            $table->date('first_due_date');
            $table->date('maturity_date');
            $table->date('last_payment_date')->nullable();
            $table->float('loan_amount');
            $table->string('loan_term');
            $table->string('emi');
            $table->string('loan_status');
            $table->string('npa_stage');
            $table->enum('financing', ['Newgen BMI', 'Newgen BFC']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_search_lms_records');
    }
};
