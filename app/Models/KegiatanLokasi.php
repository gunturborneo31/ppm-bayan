<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanLokasi extends Model
{
    protected $fillable = [
        'kegiatan_id',
        'lokasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'target_output',
        'satuan',
        'rencana_biaya',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'target_output' => 'decimal:2',
        'rencana_biaya' => 'decimal:2',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class, 'kegiatan_lokasi_id');
    }
}