<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('divisis', function (Blueprint $table) {
            $table->unique('nama', 'divisis_nama_unique');
        });

        Schema::table('pilars', function (Blueprint $table) {
            $table->unique('nama', 'pilars_nama_unique');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->unique('nama', 'programs_nama_unique');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->unique(['program_id', 'divisi_id', 'nama'], 'kegiatans_program_divisi_nama_unique');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropUnique('kegiatans_program_divisi_nama_unique');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique('programs_nama_unique');
        });

        Schema::table('pilars', function (Blueprint $table) {
            $table->dropUnique('pilars_nama_unique');
        });

        Schema::table('divisis', function (Blueprint $table) {
            $table->dropUnique('divisis_nama_unique');
        });
    }
};
