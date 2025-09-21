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
        Schema::create('transport_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->string('from_station_name');
            $table->string('to_station_name');
            $table->date('travel_date');
            $table->string('dep_time')->nullable();
            $table->string('arr_time')->nullable();
            $table->unsignedInteger('fare_yen')->nullable();
            $table->unsignedInteger('seat_fee_yen')->nullable();
            $table->unsignedInteger('total_yen')->nullable();
            $table->string('search_url');
            $table->enum('status',['pending','approved','rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_requests');
    }
};
