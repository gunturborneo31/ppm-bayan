<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pic extends Model {
    protected $fillable = ['nama', 'jabatan', 'no_hp', 'email', 'divisi_id'];

    public function divisi() { return $this->belongsTo(Divisi::class); }
}
