<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' || !Schema::hasTable('kegiatans')) {
            return;
        }

        $schemaSql = DB::table('sqlite_master')
            ->where('type', 'table')
            ->where('name', 'kegiatans')
            ->value('sql');

        if (!is_string($schemaSql) || strpos($schemaSql, 'programs_old_fk_fix') === false) {
            return;
        }

        $hasBerdasarkan = Schema::hasColumn('kegiatans', 'berdasarkan');
        $hasBerasarkan = Schema::hasColumn('kegiatans', 'berasarkan');

        $basedSourceExpression = $hasBerdasarkan
            ? 'berdasarkan'
            : ($hasBerasarkan ? 'berasarkan' : 'NULL');

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () use ($basedSourceExpression) {
            DB::statement('ALTER TABLE kegiatans RENAME TO kegiatans_old_fk_fix_2');
            DB::statement("CREATE TABLE kegiatans (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                program_id INTEGER NOT NULL,
                divisi_id INTEGER NOT NULL,
                nama VARCHAR NOT NULL,
                deskripsi TEXT,
                berdasarkan VARCHAR,
                target_output NUMERIC NOT NULL DEFAULT '0',
                target_bulanan NUMERIC NOT NULL DEFAULT '0',
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

            DB::statement("INSERT INTO kegiatans (id, program_id, divisi_id, nama, deskripsi, berdasarkan, target_output, target_bulanan, satuan, rencana_biaya, status, version, catatan_revisi, created_at, updated_at, deleted_at)
                SELECT id, program_id, divisi_id, nama, deskripsi,
                    {$basedSourceExpression},
                    target_output,
                    COALESCE(target_bulanan, 0),
                    satuan,
                    rencana_biaya,
                    status,
                    version,
                    catatan_revisi,
                    created_at,
                    updated_at,
                    deleted_at
                FROM kegiatans_old_fk_fix_2");
            DB::statement('DROP TABLE kegiatans_old_fk_fix_2');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS kegiatans_program_divisi_nama_unique ON kegiatans (program_id, divisi_id, nama)');

            DB::statement('ALTER TABLE kegiatan_pilar RENAME TO kegiatan_pilar_old_fk_fix_2');
            DB::statement("CREATE TABLE kegiatan_pilar (
                kegiatan_id INTEGER NOT NULL,
                pilar_id INTEGER NOT NULL,
                PRIMARY KEY (kegiatan_id, pilar_id),
                FOREIGN KEY(kegiatan_id) REFERENCES kegiatans(id) ON DELETE CASCADE,
                FOREIGN KEY(pilar_id) REFERENCES pilars(id) ON DELETE CASCADE
            )");
            DB::statement('INSERT INTO kegiatan_pilar (kegiatan_id, pilar_id) SELECT kegiatan_id, pilar_id FROM kegiatan_pilar_old_fk_fix_2');
            DB::statement('DROP TABLE kegiatan_pilar_old_fk_fix_2');

            DB::statement('ALTER TABLE realisasis RENAME TO realisasis_old_fk_fix_2');
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
            DB::statement('INSERT INTO realisasis (id, kegiatan_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at) SELECT id, kegiatan_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at FROM realisasis_old_fk_fix_2');
            DB::statement('DROP TABLE realisasis_old_fk_fix_2');

            DB::statement('ALTER TABLE kegiatan_lokasis RENAME TO kegiatan_lokasis_old_fk_fix_2');
            DB::statement("CREATE TABLE kegiatan_lokasis (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                kegiatan_id INTEGER NOT NULL,
                lokasi VARCHAR NOT NULL,
                tanggal_mulai DATE,
                tanggal_selesai DATE,
                target_output NUMERIC NOT NULL DEFAULT '0',
                satuan VARCHAR,
                rencana_biaya NUMERIC NOT NULL DEFAULT '0',
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(kegiatan_id) REFERENCES kegiatans(id) ON DELETE CASCADE
            )");
            DB::statement('INSERT INTO kegiatan_lokasis (id, kegiatan_id, lokasi, tanggal_mulai, tanggal_selesai, target_output, satuan, rencana_biaya, created_at, updated_at) SELECT id, kegiatan_id, lokasi, tanggal_mulai, tanggal_selesai, target_output, satuan, rencana_biaya, created_at, updated_at FROM kegiatan_lokasis_old_fk_fix_2');
            DB::statement('DROP TABLE kegiatan_lokasis_old_fk_fix_2');

            DB::statement('ALTER TABLE files RENAME TO files_old_fk_fix_2');
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
            DB::statement('INSERT INTO files (id, kegiatan_id, realisasi_id, file_path, file_name, file_type, file_size, kategori, uploaded_by, created_at, updated_at, deleted_at) SELECT id, kegiatan_id, realisasi_id, file_path, file_name, file_type, file_size, kategori, uploaded_by, created_at, updated_at, deleted_at FROM files_old_fk_fix_2');
            DB::statement('DROP TABLE files_old_fk_fix_2');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // no-op hotfix
    }
};
