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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            

            // 評価日
            $table->date('evaluation_date');

            // 点数(0〜100点想定)。未入力はnull=未記録
            $table->unsignedTinyInteger('score')->nullable()->comment('0-100,null=未記録');

            // 任意メモ
            $table->string('note',255)->nullable();

            // 採点した時刻(scoreがnullでない時に入れる)
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            // 同じ学生・同じ科目・同じ評価日は1行に制約(重複防止)
            $table->unique(['student_id','subject_id','evaluation_date']);
            // 科目と評価日で探す時に検索を早くする工夫
            $table->index(['subject_id','evaluation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
