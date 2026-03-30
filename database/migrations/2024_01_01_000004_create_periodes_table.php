<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->enum('triwulan', ['Tw 1','Tw 2','Tw 3','Tw 4']);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['tahun','triwulan']);
        });
    }
    public function down(): void { Schema::dropIfExists('periodes'); }
};
