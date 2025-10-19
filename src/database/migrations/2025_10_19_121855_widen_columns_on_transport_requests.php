<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL想定：長いURLや長文メモに対応
        DB::statement('ALTER TABLE transport_requests MODIFY search_url TEXT');
        DB::statement('ALTER TABLE transport_requests MODIFY route_memo TEXT');
    }

    public function down(): void
    {
        // 可能なら元に戻す（255字）。戻せない場合はその旨コメント。
        DB::statement('ALTER TABLE transport_requests MODIFY search_url VARCHAR(255)');
        DB::statement('ALTER TABLE transport_requests MODIFY route_memo VARCHAR(255)');
    }
};
