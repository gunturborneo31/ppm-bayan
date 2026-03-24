<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Realisasi extends Model {
    protected $fillable = ['kegiatan_id','periode_id','realisasi_output','realisasi_biaya','keterangan'];

    public function kegiatan() { return $this->belongsTo(Kegiatan::class); }
    public function periode() { return $this->belongsTo(Periode::class); }
    public function files() { return $this->hasMany(FileLampiran::class, 'realisasi_id'); }
}
