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
        Schema::create('dealer_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('bank_id')->required();
            $table->integer('bank_branch_id')->required();
            $table->string('bank_account')->required();
            $table->string('account_type', 1)->required();
            $table->string('branch_micr_code')->nullable()->default(null);
            $table->string('branch_ifcs_code')->nullable()->default(null);
            $table->string('gl_code', 7)->required();
            $table->string('client_code')->nullable()->default(null);
            $table->string('rec_status', 1)->required()->default('A');
            $table->string('maker_id', 7)->required();
            $table->dateTime('maker_date')->required();
            $table->string('author_id', 7)->required();
            $table->dateTime('author_date')->required();
            $table->string('bank_account_name')->required();
            $table->string('drawing_power')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
