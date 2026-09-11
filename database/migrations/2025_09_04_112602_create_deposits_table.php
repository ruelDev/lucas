<?php

use App\Models\Bank;
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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('makerId');
            $table->string('makerName');
            $table->foreignIdFor(Bank::class)->constrained();
            $table->string('referenceNumber')->unique();
            $table->string('depositoryRemarks');
            $table->date('depositDate');
            $table->decimal('depositAmount', 15, 2);
            $table->decimal('depositCharge', 15, 2);
            $table->string('depositSlip')->nullable();
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
