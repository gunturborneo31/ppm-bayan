<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            if (Schema::hasTable('kegiatan_pilar')) {
                DB::statement('ALTER TABLE kegiatan_pilar RENAME TO kegiatan_pilar_old');
                DB::statement("CREATE TABLE kegiatan_pilar (
                    kegiatan_id INTEGER NOT NULL,
                    pilar_id INTEGER NOT NULL,
                    PRIMARY KEY (kegiatan_id, pilar_id),
                    FOREIGN KEY(kegiatan_id) REFERENCES kegiatans(id) ON DELETE CASCADE,
                    FOREIGN KEY(pilar_id) REFERENCES pilars(id) ON DELETE CASCADE
                )");
                DB::statement('INSERT INTO kegiatan_pilar (kegiatan_id, pilar_id) SELECT kegiatan_id, pilar_id FROM kegiatan_pilar_old');
                DB::statement('DROP TABLE kegiatan_pilar_old');
            }

            if (Schema::hasTable('realisasis')) {
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
                DB::statement('INSERT INTO realisasis (id, kegiatan_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at) SELECT id, kegiatan_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at FROM realisasis_old');
                DB::statement('DROP TABLE realisasis_old');
            }

            if (Schema::hasTable('files')) {
                DB::statement('ALTER TABLE files RENAME TO files_old');
                DB::statement("CREATE TABLE files (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    kegiatan_id INTEGER,
                    realisasi_id INTEGER,
                    file_path VARCHAR NOT NULL,
                    file_name VARCHAR NOT NULL,
                    file_type VARCHAR NOT NULL,
                    file_size INTEGER,
                    kategori VARCHAR NOT NULL CHECK (kategori IN ('evidence','laporan')),
                    uploaded_by INTEGER NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    deleted_at DATETIME,
                    FOREIGN KEY(kegiatan_id) REFERENCES kegiatans(id) ON DELETE SET NULL,
                    FOREIGN KEY(realisasi_id) REFERENCES realisasis(id) ON DELETE SET NULL,
                    FOREIGN KEY(uploaded_by) REFERENCES users(id) ON DELETE CASCADE
                )");
                DB::statement("INSERT INTO files (id, kegiatan_id, realisasi_id, file_path, file_name, file_type, file_size, kategori, uploaded_by, created_at, updated_at, deleted_at)
                    SELECT id, kegiatan_id, realisasi_id, file_path, file_name, file_type, file_size, kategori, uploaded_by, created_at, updated_at, deleted_at FROM files_old");
                DB::statement('DROP TABLE files_old');
            }
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // no-op for sqlite hotfix migration
    }
};
