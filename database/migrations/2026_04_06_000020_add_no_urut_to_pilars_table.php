<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('pilars', 'no_urut')) {
            Schema::table('pilars', function (Blueprint $table) {
                $table->unsignedInteger('no_urut')->default(0)->after('nama');
                $table->index('no_urut', 'pilars_no_urut_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pilars', 'no_urut')) {
            Schema::table('pilars', function (Blueprint $table) {
                $table->dropIndex('pilars_no_urut_index');
                $table->dropColumn('no_urut');
            });
        }
    }
};
