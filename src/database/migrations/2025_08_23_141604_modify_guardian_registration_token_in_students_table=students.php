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
        Schema::table('students', function (Blueprint $table) {
            // 64文字のトークンを格納、ユニーク制約を追加
            $table->string('guardian_registration_token', 64)
                  ->nullable()
                  ->change();

            $table->unique('guardian_registration_token', 'students_guardian_registration_token_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_guardian_registration_token_unique');
            $table->string('guardian_registration_token')
                  ->nullable()
                  ->change();
        });
    }
};
