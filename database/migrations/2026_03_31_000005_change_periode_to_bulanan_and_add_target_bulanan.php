<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('periodes')) {
            if (!Schema::hasColumn('periodes', 'bulan')) {
                Schema::table('periodes', function (Blueprint $table) {
                    $table->unsignedTinyInteger('bulan')->nullable()->after('tahun');
                });
            }

            $rows = DB::table('periodes')->select('id', 'triwulan', 'bulan')->get();
            foreach ($rows as $row) {
                if (!empty($row->bulan)) {
                    continue;
                }

                $bulan = match ((string) $row->triwulan) {
                    'Tw 1' => 1,
                    'Tw 2' => 4,
                    'Tw 3' => 7,
                    'Tw 4' => 10,
                    default => 1,
                };

                DB::table('periodes')->where('id', $row->id)->update([
                    'bulan' => $bulan,
                    'triwulan' => $this->monthLabel($bulan),
                ]);
            }

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE periodes MODIFY COLUMN triwulan ENUM('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des','Tw 1','Tw 2','Tw 3','Tw 4') NOT NULL");
                DB::statement("ALTER TABLE periodes MODIFY COLUMN status TINYINT(1) NOT NULL");
            }

            DB::table('periodes')
                ->whereNull('bulan')
                ->update(['bulan' => 1]);

            if (DB::getDriverName() === 'mysql') {
                DB::statement('DELETE p1 FROM periodes p1 INNER JOIN periodes p2 ON p1.id > p2.id AND p1.tahun = p2.tahun AND p1.bulan = p2.bulan');
            } else {
                $idsToKeep = DB::table('periodes')
                    ->selectRaw('MIN(id) as id')
                    ->groupBy('tahun', 'bulan')
                    ->pluck('id')
                    ->all();

                if (!empty($idsToKeep)) {
                    DB::table('periodes')->whereNotIn('id', $idsToKeep)->delete();
                }
            }

            try {
                Schema::table('periodes', function (Blueprint $table) {
                    $table->unique(['tahun', 'bulan'], 'periodes_tahun_bulan_unique');
                });
            } catch (\Throwable $e) {
                // Index may already exist in some environments.
            }
        }

        if (Schema::hasTable('kegiatans') && !Schema::hasColumn('kegiatans', 'target_bulanan')) {
            Schema::table('kegiatans', function (Blueprint $table) {
                $table->decimal('target_bulanan', 15, 2)->default(0)->after('target_output');
            });

            DB::table('kegiatans')
                ->whereNull('target_bulanan')
                ->orWhere('target_bulanan', 0)
                ->update(['target_bulanan' => DB::raw('target_output')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kegiatans') && Schema::hasColumn('kegiatans', 'target_bulanan')) {
            Schema::table('kegiatans', function (Blueprint $table) {
                $table->dropColumn('target_bulanan');
            });
        }

        if (Schema::hasTable('periodes')) {
            try {
                Schema::table('periodes', function (Blueprint $table) {
                    $table->dropUnique('periodes_tahun_bulan_unique');
                });
            } catch (\Throwable $e) {
                // ignore if index does not exist
            }

            if (Schema::hasColumn('periodes', 'bulan')) {
                Schema::table('periodes', function (Blueprint $table) {
                    $table->dropColumn('bulan');
                });
            }

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE periodes MODIFY COLUMN triwulan ENUM('Tw 1','Tw 2','Tw 3','Tw 4') NOT NULL");
                DB::statement("ALTER TABLE periodes MODIFY COLUMN status TINYINT(1) NOT NULL DEFAULT 1");
            }
        }
    }

    private function monthLabel(int $bulan): string
    {
        return match ($bulan) {
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            default => 'Des',
        };
    }
};
