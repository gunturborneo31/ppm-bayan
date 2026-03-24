<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kegiatan_pilar', function (Blueprint $table) {
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
            $table->foreignId('pilar_id')->constrained('pilars')->cascadeOnDelete();
            $table->primary(['kegiatan_id','pilar_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('kegiatan_pilar'); }
};
