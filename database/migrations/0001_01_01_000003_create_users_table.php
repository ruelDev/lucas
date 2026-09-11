<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->max(7);
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('company');
            $table->string('position');
            $table->date('expiration_date')->nullable();
            $table->string('password');
            $table->tinyInteger('isReset')->default(0);
            $table->tinyInteger('isBranchDealer');
            $table->tinyInteger('isAlternateUser')->default(0);
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();
            $table->string('profile_picture')->nullable();
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamp('password_changed_at')->nullable();
            $table->unsignedBigInteger('session_version')->default(0);
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->timestamp('date_log_in')->default(Carbon::now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
