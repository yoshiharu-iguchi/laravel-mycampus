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
        Schema::create('roster_students', function (Blueprint $table) {
            $table->id();
      $table->string('student_number', 20)->unique(); // 学籍番号（文字列として扱う）
      $table->string('name', 100);
      $table->string('name_kana', 100)->nullable();
      $table->date('birth_date')->nullable();
      $table->unsignedTinyInteger('grade')->nullable();
      $table->foreignId('registered_student_id')->nullable()
            ->constrained('students')->nullOnDelete(); // 既に本登録されたら紐づけ
      $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_students');
    }
};
