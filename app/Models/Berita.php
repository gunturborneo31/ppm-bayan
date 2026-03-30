<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_id');
    }
}
