<?php

use App\Models\Deposit;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Deposit::class)->constrained();
            $table->string('makerId');
            $table->string('makerName');
            $table->string('agreementNumber');
            $table->string('referenceNumber');
            $table->string('misNumber');
            $table->string('customerName');
            $table->string('aoc');
            $table->date('arDate');
            $table->string('arNumber');
            $table->decimal('arAmount', 15, 2);
            $table->string('paymentType');
            $table->string('npaStage')->nullable();
            $table->longText('reason');
            $table->string('source');
            $table->string('status');
            $table->string('receiptType');
            $table->string('company')->nullable();
            $table->string('authorizerId')->nullable();
            $table->date('dateAuthor')->nullable();
            $table->longText('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
