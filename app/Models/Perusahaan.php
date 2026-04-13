<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    protected $guarded = [];

    public function bidangUtama()
    {
        return $this->belongsTo(BidangCsr::class, 'bidang_csr_id');
    }

    public function programCsrs()
    {
        return $this->hasMany(ProgramCsr::class);
    }

    public function laporanCsrs()
    {
        return $this->hasMany(LaporanCsr::class);
    }
}
