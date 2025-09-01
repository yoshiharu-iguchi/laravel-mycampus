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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code', 30)->unique();
            $table->string('name_ja', 100);
            $table->string('name_en', 100)->nullable();
            $table->decimal('credits', 3, 1)->default(1.0);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('term', 20)->nullable();
            $table->string('category', 20)->default('elective'); // 必修/選択
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
