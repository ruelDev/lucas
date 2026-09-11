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
        Schema::create('offline_search_facility_payment_records', function (Blueprint $table) {
            $table->id();
            $table->char('row_hash', 64)->unique();
            $table->string('loan_id')->nullable()->index();
            $table->string('payment_type');
            $table->string('dealing_bank_id')->nullable();
            $table->string('payment_mode');
            $table->string('receipt_no')->nullable();
            $table->decimal('payment_amount');
            $table->string('pdc_flag');
            $table->string('status');
            $table->dateTime('payment_date');
            $table->dateTime('deposit_date')->nullable();
            $table->string('bp_type');
            $table->string('bp_id')->nullable();
            $table->longText('remarks')->nullable();
            $table->string('branch_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_search_facility_payment_records');
    }
};
