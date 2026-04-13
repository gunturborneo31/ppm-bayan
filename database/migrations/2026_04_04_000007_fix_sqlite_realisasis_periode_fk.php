<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' || !Schema::hasTable('realisasis')) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            DB::statement('ALTER TABLE realisasis RENAME TO realisasis_old');

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

            DB::statement("INSERT INTO realisasis (id, kegiatan_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at)
                SELECT r.id, r.kegiatan_id, r.periode_id, r.realisasi_output, r.realisasi_biaya, r.keterangan, r.created_at, r.updated_at
                FROM realisasis_old r
                INNER JOIN periodes p ON p.id = r.periode_id");

            DB::statement('DROP TABLE realisasis_old');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // no-op sqlite hotfix
    }
};
