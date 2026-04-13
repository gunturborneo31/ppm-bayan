<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('realisasis') || Schema::hasColumn('realisasis', 'kegiatan_lokasi_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE realisasis ADD COLUMN kegiatan_lokasi_id INTEGER NULL');
            DB::statement('CREATE INDEX IF NOT EXISTS realisasis_kegiatan_lokasi_id_index ON realisasis (kegiatan_lokasi_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS realisasis_kegiatan_lokasi_periode_index ON realisasis (kegiatan_lokasi_id, periode_id)');
        } else {
            Schema::table('realisasis', function (Blueprint $table) {
                $table->foreignId('kegiatan_lokasi_id')
                    ->nullable()
                    ->after('kegiatan_id')
                    ->constrained('kegiatan_lokasis')
                    ->nullOnDelete();
                $table->index(['kegiatan_lokasi_id', 'periode_id']);
            });
        }

        DB::statement("UPDATE realisasis
            SET kegiatan_lokasi_id = (
                SELECT kl.id
                FROM kegiatan_lokasis kl
                WHERE kl.kegiatan_id = realisasis.kegiatan_id
                ORDER BY kl.id
                LIMIT 1
            )
            WHERE kegiatan_lokasi_id IS NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('realisasis') || !Schema::hasColumn('realisasis', 'kegiatan_lokasi_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE realisasis RENAME TO realisasis_old_remove_lokasi');
            DB::statement("CREATE TABLE realisasis (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                kegiatan_id INTEGER NOT NULL,
                periode_id INTEGER NOT NULL,
                realisasi_output NUMERIC,
                realisasi_biaya NUMERIC,
                keterangan TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(kegiatan_id) REFERENCES kegiatans(id) ON DELETE CASCADE,
                FOREIGN KEY(periode_id) REFERENCES periodes(id) ON DELETE CASCADE
            )");
            DB::statement('INSERT INTO realisasis (id, kegiatan_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at) SELECT id, kegiatan_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at FROM realisasis_old_remove_lokasi');
            DB::statement('DROP TABLE realisasis_old_remove_lokasi');
        } else {
            Schema::table('realisasis', function (Blueprint $table) {
                $table->dropForeign(['kegiatan_lokasi_id']);
                $table->dropIndex(['kegiatan_lokasi_id', 'periode_id']);
                $table->dropColumn('kegiatan_lokasi_id');
            });
        }
    }
};
