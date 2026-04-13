<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Realisasi extends Model {
    protected $fillable = ['kegiatan_id','kegiatan_lokasi_id','periode_id','tanggal_realisasi','realisasi_output','realisasi_biaya','keterangan'];

    protected $casts = [
        'tanggal_realisasi' => 'date',
    ];

    public function kegiatan() { return $this->belongsTo(Kegiatan::class); }
    public function kegiatanLokasi() { return $this->belongsTo(KegiatanLokasi::class, 'kegiatan_lokasi_id'); }
    public function periode() { return $this->belongsTo(Periode::class); }
    public function files() { return $this->hasMany(FileLampiran::class, 'realisasi_id'); }
}
