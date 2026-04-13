<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pilar extends Model {
    protected $fillable = ['nama', 'no_urut', 'deskripsi', 'rencana_biaya', 'warna', 'icon'];
    protected $casts = ['no_urut' => 'integer'];

    public static function colorMap(): array
    {
        $defaults = [
            'Pendidikan' => '#2563eb',
            'Kesehatan' => '#dc2626',
            'Pendapatan Riil atau Pekerjaan' => '#0f766e',
            'Kemandirian Ekonomi' => '#d97706',
            'Sosial dan Budaya' => '#7c3aed',
            'Lingkungan' => '#16a34a',
            'Kelembagaan Komunitas Masyarakat' => '#db2777',
            'Infrastruktur' => '#0891b2',
        ];

        $fromDatabase = self::query()
            ->select('nama', 'warna')
            ->whereNotNull('warna')
            ->get()
            ->filter(fn (self $pilar) => is_string($pilar->warna) && preg_match('/^#[0-9A-Fa-f]{6}$/', $pilar->warna))
            ->mapWithKeys(fn (self $pilar) => [$pilar->nama => $pilar->warna])
            ->toArray();

        return array_merge($defaults, $fromDatabase);
    }

    public static function colorForName(?string $name, string $default = '#64748b'): string
    {
        return self::colorMap()[$name ?? ''] ?? $default;
    }

    public static function iconMap(): array
    {
        $defaults = [
            'Pendidikan' => 'school',
            'Kesehatan' => 'medical_services',
            'Pendapatan Riil atau Pekerjaan' => 'trending_up',
            'Kemandirian Ekonomi' => 'payments',
            'Sosial dan Budaya' => 'groups',
            'Lingkungan' => 'eco',
            'Kelembagaan Komunitas Masyarakat' => 'hub',
            'Infrastruktur' => 'construction',
        ];

        $fromDatabase = self::query()
            ->select('nama', 'icon')
            ->whereNotNull('icon')
            ->where('icon', '!=', '')
            ->get()
            ->mapWithKeys(fn (self $pilar) => [$pilar->nama => trim((string) $pilar->icon)])
            ->toArray();

        return array_merge($defaults, $fromDatabase);
    }

    public static function iconForName(?string $name, string $default = 'category'): string
    {
        return self::iconMap()[$name ?? ''] ?? $default;
    }

    public function programs() { return $this->hasMany(Program::class); }
    public function kegiatans() { return $this->belongsToMany(Kegiatan::class, 'kegiatan_pilar'); }
}
