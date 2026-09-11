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
        Schema::create('etl_process_logs', function (Blueprint $table) {
            $table->id();
            $table->string('process_name')->unique();
            $table->unsignedBigInteger('last_successful_page')->default(0);
            $table->unsignedBigInteger('last_attempted_page')->default(0);
            $table->string('last_run_status')->default('idle');
            $table->unsignedBigInteger('total_processed')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etl_process_logs');
    }
};
