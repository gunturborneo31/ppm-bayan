<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramCsr extends Model
{
    protected $guarded = [];

    public function bidang()
    {
        return $this->belongsTo(BidangCsr::class, 'bidang_id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function laporans()
    {
        return $this->hasMany(LaporanCsr::class, 'program_id');
    }
}
