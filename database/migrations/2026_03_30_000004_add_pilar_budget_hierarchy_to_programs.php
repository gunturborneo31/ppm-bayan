<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pilars') && !Schema::hasColumn('pilars', 'rencana_biaya')) {
            Schema::table('pilars', function (Blueprint $table) {
                $table->decimal('rencana_biaya', 15, 2)->default(0)->after('deskripsi');
            });
        }

        if (Schema::hasTable('programs') && !Schema::hasColumn('programs', 'pilar_id')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->unsignedBigInteger('pilar_id')->nullable()->after('user_id');
                $table->index('pilar_id', 'programs_pilar_id_index');
            });

            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('programs', function (Blueprint $table) {
                    $table->foreign('pilar_id')->references('id')->on('pilars')->nullOnDelete();
                });
            }
        }

        $this->backfillProgramPilar();
        $this->syncKegiatanPilarFromProgram();
    }

    public function down(): void
    {
        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'pilar_id')) {
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('programs', function (Blueprint $table) {
                    $table->dropForeign(['pilar_id']);
                });
            }

            Schema::table('programs', function (Blueprint $table) {
                $table->dropIndex('programs_pilar_id_index');
                $table->dropColumn('pilar_id');
            });
        }

        if (Schema::hasTable('pilars') && Schema::hasColumn('pilars', 'rencana_biaya')) {
            Schema::table('pilars', function (Blueprint $table) {
                $table->dropColumn('rencana_biaya');
            });
        }
    }

    private function backfillProgramPilar(): void
    {
        if (!Schema::hasTable('programs') || !Schema::hasColumn('programs', 'pilar_id') || !Schema::hasTable('pilars')) {
            return;
        }

        $defaultPilarId = DB::table('pilars')->orderBy('id')->value('id');
        if (!$defaultPilarId) {
            return;
        }

        $programs = DB::table('programs')->select('id', 'pilar_id')->get();

        foreach ($programs as $program) {
            if (!empty($program->pilar_id)) {
                continue;
            }

            $candidatePilarId = DB::table('kegiatans')
                ->join('kegiatan_pilar', 'kegiatans.id', '=', 'kegiatan_pilar.kegiatan_id')
                ->where('kegiatans.program_id', $program->id)
                ->selectRaw('kegiatan_pilar.pilar_id, COUNT(*) as total')
                ->groupBy('kegiatan_pilar.pilar_id')
                ->orderByDesc('total')
                ->orderBy('kegiatan_pilar.pilar_id')
                ->value('kegiatan_pilar.pilar_id');

            DB::table('programs')
                ->where('id', $program->id)
                ->update(['pilar_id' => $candidatePilarId ?: $defaultPilarId]);
        }
    }

    private function syncKegiatanPilarFromProgram(): void
    {
        if (!Schema::hasTable('kegiatans') || !Schema::hasTable('kegiatan_pilar') || !Schema::hasTable('programs')) {
            return;
        }

        $rows = DB::table('kegiatans')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->whereNotNull('programs.pilar_id')
            ->select('kegiatans.id as kegiatan_id', 'programs.pilar_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('kegiatan_pilar')->where('kegiatan_id', $row->kegiatan_id)->delete();
            DB::table('kegiatan_pilar')->insert([
                'kegiatan_id' => $row->kegiatan_id,
                'pilar_id' => $row->pilar_id,
            ]);
        }
    }
};
