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

        if (!Schema::hasTable('programs') || !Schema::hasTable('activity_logs') || !Schema::hasTable('files')) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            // Fix programs.user_id FK -> users(id)
            DB::statement('ALTER TABLE programs RENAME TO programs_old_fk_fix');
            DB::statement("CREATE TABLE programs (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                nama VARCHAR NOT NULL,
                deskripsi TEXT,
                target_output NUMERIC NOT NULL DEFAULT '0',
                satuan VARCHAR NOT NULL,
                rencana_biaya NUMERIC NOT NULL DEFAULT '0',
                user_id INTEGER NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                pilar_id INTEGER,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
            DB::statement("INSERT INTO programs (id, nama, deskripsi, target_output, satuan, rencana_biaya, user_id, created_at, updated_at, deleted_at, pilar_id)
                SELECT id, nama, deskripsi, target_output, satuan, rencana_biaya, user_id, created_at, updated_at, deleted_at, pilar_id
                FROM programs_old_fk_fix");
            DB::statement('DROP TABLE programs_old_fk_fix');
            DB::statement('CREATE INDEX IF NOT EXISTS programs_pilar_id_index ON programs (pilar_id)');

            // Fix activity_logs.user_id FK -> users(id)
            DB::statement('ALTER TABLE activity_logs RENAME TO activity_logs_old_fk_fix');
            DB::statement("CREATE TABLE activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                user_id INTEGER,
                module VARCHAR NOT NULL,
                action VARCHAR NOT NULL,
                subject_type VARCHAR,
                subject_id INTEGER,
                field VARCHAR,
                old_value TEXT,
                new_value TEXT,
                description TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
            )");
            DB::statement("INSERT INTO activity_logs (id, user_id, module, action, subject_type, subject_id, field, old_value, new_value, description, created_at, updated_at)
                SELECT id, user_id, module, action, subject_type, subject_id, field, old_value, new_value, description, created_at, updated_at
                FROM activity_logs_old_fk_fix");
            DB::statement('DROP TABLE activity_logs_old_fk_fix');

            // Fix files.realisasi_id FK -> realisasis(id) and files.uploaded_by FK -> users(id)
            DB::statement('ALTER TABLE files RENAME TO files_old_fk_fix');
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
                SELECT id, kegiatan_id, realisasi_id, file_path, file_name, file_type, file_size, kategori, uploaded_by, created_at, updated_at, deleted_at
                FROM files_old_fk_fix");
            DB::statement('DROP TABLE files_old_fk_fix');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // no-op hotfix
    }
};
