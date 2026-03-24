<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model {
    protected $fillable = ['nama', 'deskripsi', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function users() { return $this->hasMany(User::class); }
    public function kegiatans() { return $this->hasMany(Kegiatan::class); }
    public function pics() { return $this->hasMany(Pic::class); }
}
