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
        Schema::create('offline_search_facility_lms_records', function (Blueprint $table) {
            $table->id();
            $table->char('row_hash', 64)->unique();
            $table->string('customer_id')->index();
            $table->string('loan_application_id');
            $table->string('agreement_no')->index();
            $table->string('account_number');
            $table->string('agreement_id');
            $table->date('date_sold');
            $table->date('first_due_date');
            $table->date('maturity_date');
            $table->decimal('loan_amount');
            $table->integer('loan_term')->nullable();
            $table->decimal('emi');
            $table->string('loan_status')->nullable();
            $table->string('npa_stage');
            $table->string('account_rating')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_search_facility_lms_records');
    }
};
