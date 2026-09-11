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
        Schema::create('offline_search_client_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number');
            $table->enum('source', ['Newgen BMI', 'Newgen BFC', 'Finnone']);
            $table->unsignedBigInteger('client_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_search_client_accounts');
    }
};
