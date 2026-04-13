<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('divisi_id')->constrained('divisis')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->decimal('target_output', 15, 2)->default(0);
            $table->decimal('rencana_biaya', 15, 2)->default(0);
            $table->enum('status', ['draft','diajukan','revisi','disetujui','ditolak','selesai'])->default('draft');
            $table->integer('version')->default(1);
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('kegiatans'); }
};
