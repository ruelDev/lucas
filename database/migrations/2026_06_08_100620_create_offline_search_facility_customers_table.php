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
        Schema::create('offline_search_facility_customers', function (Blueprint $table) {
            $table->id();
            $table->char('row_hash', 64)->unique();
            $table->string('customer_id')->index();
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->date('dob');
            $table->longText('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_search_facility_customers');
    }
};
