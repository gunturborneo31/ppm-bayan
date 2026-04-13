<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model {
    protected $fillable = ['tahun', 'bulan', 'triwulan', 'status'];
    protected $casts = ['status' => 'boolean', 'bulan' => 'integer'];

    public function realisasis() { return $this->hasMany(Realisasi::class); }

    public function getTriwulanAttribute($value): string
    {
        if (!empty($this->attributes['bulan'])) {
            return $this->monthLabel((int) $this->attributes['bulan']);
        }

        return (string) $value;
    }

    public function getBulanLabelAttribute(): string
    {
        if (!empty($this->attributes['bulan'])) {
            return $this->monthLabel((int) $this->attributes['bulan']);
        }

        return (string) ($this->attributes['triwulan'] ?? '-');
    }

    private function monthLabel(int $bulan): string
    {
        return match ($bulan) {
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            default => 'Des',
        };
    }
}
