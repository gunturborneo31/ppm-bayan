<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('realisasis') || Schema::hasColumn('realisasis', 'tanggal_realisasi')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE realisasis ADD COLUMN tanggal_realisasi DATE NULL');
            DB::statement('CREATE INDEX IF NOT EXISTS realisasis_tanggal_realisasi_index ON realisasis (tanggal_realisasi)');
        } else {
            Schema::table('realisasis', function (Blueprint $table) {
                $table->date('tanggal_realisasi')->nullable()->after('periode_id');
                $table->index('tanggal_realisasi');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE realisasis
                SET tanggal_realisasi = (
                    SELECT printf('%04d-%02d-01', p.tahun, p.bulan)
                    FROM periodes p
                    WHERE p.id = realisasis.periode_id
                )
                WHERE tanggal_realisasi IS NULL");
        } else {
            DB::statement("UPDATE realisasis r
                JOIN periodes p ON p.id = r.periode_id
                SET r.tanggal_realisasi = DATE(CONCAT(p.tahun, '-', LPAD(p.bulan, 2, '0'), '-01'))
                WHERE r.tanggal_realisasi IS NULL");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('realisasis') || !Schema::hasColumn('realisasis', 'tanggal_realisasi')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE realisasis RENAME TO realisasis_old_remove_tanggal');
            DB::statement("CREATE TABLE realisasis (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                kegiatan_id INTEGER NOT NULL,
                kegiatan_lokasi_id INTEGER NULL,
                periode_id INTEGER NOT NULL,
                realisasi_output NUMERIC,
                realisasi_biaya NUMERIC,
                keterangan TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(kegiatan_id) REFERENCES kegiatans(id) ON DELETE CASCADE,
                FOREIGN KEY(kegiatan_lokasi_id) REFERENCES kegiatan_lokasis(id) ON DELETE SET NULL,
                FOREIGN KEY(periode_id) REFERENCES periodes(id) ON DELETE CASCADE
            )");
            DB::statement('INSERT INTO realisasis (id, kegiatan_id, kegiatan_lokasi_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at) SELECT id, kegiatan_id, kegiatan_lokasi_id, periode_id, realisasi_output, realisasi_biaya, keterangan, created_at, updated_at FROM realisasis_old_remove_tanggal');
            DB::statement('DROP TABLE realisasis_old_remove_tanggal');
        } else {
            Schema::table('realisasis', function (Blueprint $table) {
                $table->dropIndex(['tanggal_realisasi']);
                $table->dropColumn('tanggal_realisasi');
            });
        }
    }
};