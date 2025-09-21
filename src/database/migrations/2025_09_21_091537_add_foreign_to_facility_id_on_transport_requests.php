<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 現在のDBに facility_id の外部キーが付いているかを調べるヘルパ
    private function currentFkName(): ?string
    {
        $sql = "
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'transport_requests'
              AND COLUMN_NAME = 'facility_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ";
        $row = DB::selectOne($sql);
        return $row?->CONSTRAINT_NAME ?? null;
    }

    public function up(): void
    {
        // 1) 既存の外部キーがあれば外す（無ければ何もしない）
        if ($fk = $this->currentFkName()) {
            Schema::table('transport_requests', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk);
            });
        }

        // 2) 新しい外部キーを付与（施設削除時は連鎖削除する例）
        Schema::table('transport_requests', function (Blueprint $table) {
            $table->foreign('facility_id', 'tr_facility_id_fk')
                  ->references('id')
                  ->on('facilities')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // 付けた名前で外す（無ければスキップ）
        Schema::table('transport_requests', function (Blueprint $table) {
            try {
                $table->dropForeign('tr_facility_id_fk');
            } catch (\Throwable $e) {
                // 既に無ければ無視
            }
        });
    }
};
