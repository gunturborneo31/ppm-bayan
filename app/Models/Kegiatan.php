<?php
namespace App\Models;

use App\Enums\KegiatanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kegiatan extends Model {
    use SoftDeletes;

    protected $fillable = ['program_id','divisi_id','nama','deskripsi','target_output','rencana_biaya','status','version','catatan_revisi'];
    protected $casts = ['status' => KegiatanStatus::class];

    public function program() { return $this->belongsTo(Program::class); }
    public function divisi() { return $this->belongsTo(Divisi::class); }
    public function realisasis() { return $this->hasMany(Realisasi::class); }
    public function files() { return $this->hasMany(FileLampiran::class); }
    public function pilars() { return $this->belongsToMany(Pilar::class, 'kegiatan_pilar'); }

    public function getProgressAttribute(): float {
        if (!$this->target_output || $this->target_output == 0) return 0;
        $total = $this->realisasis()->sum('realisasi_output');
        return min(100, round(($total / $this->target_output) * 100, 2));
    }
}
