<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanCsr extends Model
{
    protected $guarded = [];

    public function bidang()
    {
        return $this->belongsTo(BidangCsr::class, 'bidang_id');
    }

    public function program()
    {
        return $this->belongsTo(ProgramCsr::class, 'program_id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
