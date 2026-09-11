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
        Schema::create('offline_search_los_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lms_record_id')->nullable();
            $table->string('rlos_id')->unique();
            $table->enum('financing', ['Newgen BMI', 'Newgen BFC']);
            $table->date('date_encoded');
            $table->dateTime('date_decision')->nullable();
            $table->longText('status')->nullable();
            $table->longText('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_search_los_records');
    }
};
