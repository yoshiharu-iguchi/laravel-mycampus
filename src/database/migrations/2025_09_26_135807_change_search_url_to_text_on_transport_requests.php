<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MySQL用：search_url を TEXT に拡張
        DB::statement('ALTER TABLE transport_requests MODIFY search_url TEXT NOT NULL');
    }

    public function down(): void
    {
        // 元に戻す（必要なら）
        DB::statement('ALTER TABLE transport_requests MODIFY search_url VARCHAR(255) NOT NULL');
    }
};

