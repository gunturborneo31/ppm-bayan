<?php
namespace App\Models;

use App\Enums\KegiatanStatus;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kegiatan extends Model {
    use SoftDeletes;

    protected $fillable = ['program_id','divisi_id','nama','deskripsi','berdasarkan','target_output','target_bulanan','satuan','rencana_biaya','status','version','catatan_revisi'];
    protected $casts = ['status' => KegiatanStatus::class];

    public function program() { return $this->belongsTo(Program::class); }
    public function divisi() { return $this->belongsTo(Divisi::class); }
    public function lokasis() { return $this->hasMany(KegiatanLokasi::class)->orderBy('tanggal_mulai')->orderBy('id'); }
    public function realisasis() { return $this->hasMany(Realisasi::class)->orderByDesc('tanggal_realisasi')->orderByDesc('id'); }
    public function comments() { return $this->hasMany(KegiatanComment::class)->latest(); }
    public function files() { return $this->hasMany(FileLampiran::class); }
    // Kept for backward compatibility with existing pages/queries during migration.
    public function pilars() { return $this->belongsToMany(Pilar::class, 'kegiatan_pilar'); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class, 'subject_id')->where('subject_type', self::class); }

    public function getProgressAttribute(): float {
        if (!$this->target_output || $this->target_output == 0) return 0;
        $total = $this->realisasis()->sum('realisasi_output');
        return min(100, round(($total / $this->target_output) * 100, 2));
    }
}
