<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lokasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        $now = now();
        $names = DB::table('kegiatan_lokasis')
            ->select('lokasi')
            ->whereNotNull('lokasi')
            ->distinct()
            ->pluck('lokasi');

        foreach ($names as $name) {
            $trimmed = trim((string) $name);
            if ($trimmed === '') {
                continue;
            }

            DB::table('lokasis')->insertOrIgnore([
                'nama' => $trimmed,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasis');
    }
};