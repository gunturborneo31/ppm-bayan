<?php
namespace App\Enums;

enum KegiatanStatus: string {
    case DRAFT = 'draft';
    case DIAJUKAN = 'diajukan';
    case DIAJUKAN_ULANG = 'diajukan_ulang';
    case REVISI = 'revisi';
    case DISETUJUI = 'disetujui';
    case DITOLAK = 'ditolak';
    case SELESAI = 'selesai';

    public function label(): string {
        return match($this) {
            KegiatanStatus::DRAFT => 'Draft',
            KegiatanStatus::DIAJUKAN => 'Diajukan',
            KegiatanStatus::DIAJUKAN_ULANG => 'Diajukan Ulang',
            KegiatanStatus::REVISI => 'Revisi',
            KegiatanStatus::DISETUJUI => 'Disetujui',
            KegiatanStatus::DITOLAK => 'Ditolak',
            KegiatanStatus::SELESAI => 'Selesai',
        };
    }

    public function color(): string {
        return match($this) {
            KegiatanStatus::DRAFT => 'gray',
            KegiatanStatus::DIAJUKAN => 'blue',
            KegiatanStatus::DIAJUKAN_ULANG => 'blue',
            KegiatanStatus::REVISI => 'yellow',
            KegiatanStatus::DISETUJUI => 'green',
            KegiatanStatus::DITOLAK => 'red',
            KegiatanStatus::SELESAI => 'purple',
        };
    }
}
