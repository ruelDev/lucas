<?php

use App\Models\AppVersion;
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
        Schema::create('release_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AppVersion::class, 'app_version_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title')->nullable();
            $table->longText('description');
            $table->integer('sort_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_notes');
    }
};
