<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kegiatan_lokasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
            $table->string('lokasi');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('target_output', 15, 2)->default(0);
            $table->string('satuan')->nullable();
            $table->decimal('rencana_biaya', 15, 2)->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = DB::table('kegiatans')
            ->select('id', 'target_output', 'satuan', 'rencana_biaya', 'created_at', 'updated_at')
            ->get();

        foreach ($rows as $row) {
            DB::table('kegiatan_lokasis')->insert([
                'kegiatan_id' => $row->id,
                'lokasi' => 'Lokasi 1',
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
                'target_output' => $row->target_output ?? 0,
                'satuan' => $row->satuan,
                'rencana_biaya' => $row->rencana_biaya ?? 0,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_lokasis');
    }
};