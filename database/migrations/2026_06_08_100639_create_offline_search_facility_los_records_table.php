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
        Schema::create('offline_search_facility_los_records', function (Blueprint $table) {
            $table->id();
            $table->char('row_hash', 64)->unique();
            $table->string('financing_bank');
            $table->string('rlos_id')->nullable()->index();
            $table->string('status')->nullable();
            $table->dateTime('date_encoded');
            $table->dateTime('decision_date')->nullable();
            $table->longText('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_search_facility_los_records');
    }
};
