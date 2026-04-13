<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_csrs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('bidang_id')->constrained('bidang_csrs')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->constrained('perusahaans')->cascadeOnDelete();
            $table->decimal('anggaran', 20, 2)->default(0);
            $table->string('lokasi')->nullable();
            $table->integer('tahun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_csrs');
    }
};
