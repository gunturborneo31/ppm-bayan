<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriBerita extends Model
{
    protected $guarded = [];

    public function beritas()
    {
        return $this->hasMany(Berita::class, 'kategori_id');
    }
}
