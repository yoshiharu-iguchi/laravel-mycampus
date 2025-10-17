<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('enrollments')) return;

        if (Schema::hasColumn('enrollments','term')){
            DB::statement('ALTER TABLE enrollments MODIFY term TINYINT UNSIGNED NOT NULL');
        } else {
            Schema::table('enrollments',function(Blueprint $table){
                $table->unsignedTinyInteger('term')->after('subject_id');
            });
        }
        if (Schema::hasColumn('enrollments','status')) {
            DB::statement('ALTER TABLE enrollments MODIFY status TINYINT UNSIGNED NOT NULL DEFAULT 0');
        } else {
            Schema::table('enrollments',function (Blueprint $table){
                $table->unsignedTinyInteger('status')->default(0)->after('term');
            });
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('enrollments')) return;

        if (Schema::hasColumn('enrollments','term')) {
            DB::statement('ALTER TABLE enrollments MODIFY term VARCHAR(50) NOT NULL');
        }
        if (Schema::hasColumn('enrollments','status')){
            DB::statement('ALTER TABLE enrollments MODIFY status VARCHAR(50) NOT NULL');
        }
    }
};
