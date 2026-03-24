<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model {
    protected $fillable = ['tahun', 'triwulan', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function realisasis() { return $this->hasMany(Realisasi::class); }
}
