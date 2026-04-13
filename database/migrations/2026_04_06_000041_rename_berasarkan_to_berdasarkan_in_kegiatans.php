<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kegiatans') || !Schema::hasColumn('kegiatans', 'berasarkan') || Schema::hasColumn('kegiatans', 'berdasarkan')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE kegiatans CHANGE berasarkan berdasarkan VARCHAR(255) NULL');
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE kegiatans RENAME COLUMN berasarkan TO berdasarkan');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('kegiatans') || !Schema::hasColumn('kegiatans', 'berdasarkan') || Schema::hasColumn('kegiatans', 'berasarkan')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE kegiatans CHANGE berdasarkan berasarkan VARCHAR(255) NULL');
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE kegiatans RENAME COLUMN berdasarkan TO berasarkan');
        }
    }
};
