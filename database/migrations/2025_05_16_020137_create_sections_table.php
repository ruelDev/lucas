<?php

use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description');
            $table->foreignIdFor(Group::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Division::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Department::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
