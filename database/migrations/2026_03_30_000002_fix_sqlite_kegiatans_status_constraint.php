<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kegiatans')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            DB::statement('ALTER TABLE kegiatans RENAME TO kegiatans_old');

            DB::statement("CREATE TABLE kegiatans (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                program_id INTEGER NOT NULL,
                divisi_id INTEGER NOT NULL,
                nama VARCHAR NOT NULL,
                deskripsi TEXT,
                target_output NUMERIC NOT NULL DEFAULT '0',
                satuan VARCHAR,
                rencana_biaya NUMERIC NOT NULL DEFAULT '0',
                status VARCHAR NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','diajukan','diajukan_ulang','revisi','disetujui','ditolak','selesai')),
                version INTEGER NOT NULL DEFAULT '1',
                catatan_revisi TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                FOREIGN KEY(program_id) REFERENCES programs(id) ON DELETE CASCADE,
                FOREIGN KEY(divisi_id) REFERENCES divisis(id) ON DELETE CASCADE
            )");

            DB::statement("INSERT INTO kegiatans (id, program_id, divisi_id, nama, deskripsi, target_output, satuan, rencana_biaya, status, version, catatan_revisi, created_at, updated_at, deleted_at)
                SELECT id, program_id, divisi_id, nama, deskripsi, target_output, satuan, rencana_biaya, status, version, catatan_revisi, created_at, updated_at, deleted_at
                FROM kegiatans_old");

            DB::statement('DROP INDEX IF EXISTS kegiatans_program_divisi_nama_unique');
            DB::statement('CREATE UNIQUE INDEX kegiatans_program_divisi_nama_unique ON kegiatans (program_id, divisi_id, nama)');
            DB::statement('DROP TABLE kegiatans_old');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        if (!Schema::hasTable('kegiatans')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            DB::statement("UPDATE kegiatans SET status = 'diajukan' WHERE status = 'diajukan_ulang'");
            DB::statement('ALTER TABLE kegiatans RENAME TO kegiatans_old');

            DB::statement("CREATE TABLE kegiatans (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                program_id INTEGER NOT NULL,
                divisi_id INTEGER NOT NULL,
                nama VARCHAR NOT NULL,
                deskripsi TEXT,
                target_output NUMERIC NOT NULL DEFAULT '0',
                satuan VARCHAR,
                rencana_biaya NUMERIC NOT NULL DEFAULT '0',
                status VARCHAR NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','diajukan','revisi','disetujui','ditolak','selesai')),
                version INTEGER NOT NULL DEFAULT '1',
                catatan_revisi TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                FOREIGN KEY(program_id) REFERENCES programs(id) ON DELETE CASCADE,
                FOREIGN KEY(divisi_id) REFERENCES divisis(id) ON DELETE CASCADE
            )");

            DB::statement("INSERT INTO kegiatans (id, program_id, divisi_id, nama, deskripsi, target_output, satuan, rencana_biaya, status, version, catatan_revisi, created_at, updated_at, deleted_at)
                SELECT id, program_id, divisi_id, nama, deskripsi, target_output, satuan, rencana_biaya, status, version, catatan_revisi, created_at, updated_at, deleted_at
                FROM kegiatans_old");

            DB::statement('DROP INDEX IF EXISTS kegiatans_program_divisi_nama_unique');
            DB::statement('CREATE UNIQUE INDEX kegiatans_program_divisi_nama_unique ON kegiatans (program_id, divisi_id, nama)');
            DB::statement('DROP TABLE kegiatans_old');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
