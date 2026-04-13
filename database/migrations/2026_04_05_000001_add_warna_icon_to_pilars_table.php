<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pilars', function (Blueprint $table) {
            $table->string('warna', 7)->default('#64748b')->after('deskripsi');
            $table->string('icon', 32)->default('category')->after('warna');
        });
    }

    public function down(): void
    {
        Schema::table('pilars', function (Blueprint $table) {
            $table->dropColumn(['warna', 'icon']);
        });
    }
};