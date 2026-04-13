<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' || !Schema::hasTable('periodes')) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            DB::statement('ALTER TABLE periodes RENAME TO periodes_old');

            DB::statement("CREATE TABLE periodes (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                tahun INTEGER NOT NULL,
                bulan INTEGER NOT NULL CHECK (bulan BETWEEN 1 AND 12),
                triwulan VARCHAR NOT NULL CHECK (triwulan IN ('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des','Tw 1','Tw 2','Tw 3','Tw 4')),
                status TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                UNIQUE (tahun, bulan)
            )");

            DB::statement("INSERT INTO periodes (id, tahun, bulan, triwulan, status, created_at, updated_at)
                SELECT
                    id,
                    tahun,
                    COALESCE(
                        bulan,
                        CASE triwulan
                            WHEN 'Tw 1' THEN 1
                            WHEN 'Tw 2' THEN 4
                            WHEN 'Tw 3' THEN 7
                            WHEN 'Tw 4' THEN 10
                            ELSE 1
                        END
                    ) AS bulan,
                    CASE
                        WHEN bulan IS NOT NULL THEN
                            CASE bulan
                                WHEN 1 THEN 'Jan'
                                WHEN 2 THEN 'Feb'
                                WHEN 3 THEN 'Mar'
                                WHEN 4 THEN 'Apr'
                                WHEN 5 THEN 'Mei'
                                WHEN 6 THEN 'Jun'
                                WHEN 7 THEN 'Jul'
                                WHEN 8 THEN 'Agu'
                                WHEN 9 THEN 'Sep'
                                WHEN 10 THEN 'Okt'
                                WHEN 11 THEN 'Nov'
                                ELSE 'Des'
                            END
                        ELSE
                            CASE triwulan
                                WHEN 'Tw 1' THEN 'Jan'
                                WHEN 'Tw 2' THEN 'Apr'
                                WHEN 'Tw 3' THEN 'Jul'
                                WHEN 'Tw 4' THEN 'Okt'
                                ELSE 'Jan'
                            END
                    END AS triwulan,
                    COALESCE(status, 1) AS status,
                    created_at,
                    updated_at
                FROM periodes_old");

            DB::statement('DROP TABLE periodes_old');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // no-op sqlite hotfix
    }
};
