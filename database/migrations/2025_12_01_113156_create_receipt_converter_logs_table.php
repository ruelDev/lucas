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
        Schema::create('receipt_converter_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('old_filename');
            $table->string('converted_filename');
            $table->integer('series');
            $table->date('date');
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_converter_logs');
    }
};
