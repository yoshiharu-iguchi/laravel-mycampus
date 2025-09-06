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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['term','status']);
        });
        // 整数カラムとして作り直し
        Schema::table('enrollments',function(Blueprint $table){
            $table->unsignedTinyInteger('term')->after('year');

            $table->unsignedTinyInteger('status')->default(1)->after('term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // 文字列カラムを一旦削除
            $table->dropColumn(['term','status']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('term',10)->after('year');
            $table->string('status',20)->default('registered')->after('term');
        });    
        
    }
};
