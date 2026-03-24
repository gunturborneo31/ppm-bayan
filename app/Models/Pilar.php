<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pilar extends Model {
    protected $fillable = ['nama', 'deskripsi'];

    public function kegiatans() { return $this->belongsToMany(Kegiatan::class, 'kegiatan_pilar'); }
}
