<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->enum('triwulan', ['Q1','Q2','Q3','Q4']);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['tahun','triwulan']);
        });
    }
    public function down(): void { Schema::dropIfExists('periodes'); }
};
