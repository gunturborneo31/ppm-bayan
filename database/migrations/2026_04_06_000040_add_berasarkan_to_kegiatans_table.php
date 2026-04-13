<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('kegiatans') || Schema::hasColumn('kegiatans', 'berasarkan')) {
            return;
        }

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->string('berasarkan')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('kegiatans') || !Schema::hasColumn('kegiatans', 'berasarkan')) {
            return;
        }

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropColumn('berasarkan');
        });
    }
};
