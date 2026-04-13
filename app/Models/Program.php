<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model {
    use SoftDeletes;

    protected $fillable = ['nama', 'deskripsi', 'target_output', 'satuan', 'rencana_biaya', 'user_id', 'pilar_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function pilar() { return $this->belongsTo(Pilar::class); }
    public function kegiatans() { return $this->hasMany(Kegiatan::class); }

    public function getTotalKegiatanBiayaAttribute() {
        return $this->kegiatans()->sum('rencana_biaya');
    }
}
