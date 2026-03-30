<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidangCsr extends Model
{
    protected $guarded = [];

    public function perusahaans()
    {
        return $this->hasMany(Perusahaan::class, 'bidang_csr_id');
    }

    public function programCsrs()
    {
        return $this->hasMany(ProgramCsr::class, 'bidang_id');
    }

    public function laporanCsrs()
    {
        return $this->hasMany(LaporanCsr::class, 'bidang_id');
    }
}
