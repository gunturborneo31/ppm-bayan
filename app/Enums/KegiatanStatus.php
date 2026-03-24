<?php
namespace App\Enums;

enum KegiatanStatus: string {
    case DRAFT = 'draft';
    case DIAJUKAN = 'diajukan';
    case REVISI = 'revisi';
    case DISETUJUI = 'disetujui';
    case DITOLAK = 'ditolak';
    case SELESAI = 'selesai';

    public function label(): string {
        return match($this) {
            KegiatanStatus::DRAFT => 'Draft',
            KegiatanStatus::DIAJUKAN => 'Diajukan',
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
            KegiatanStatus::REVISI => 'yellow',
            KegiatanStatus::DISETUJUI => 'green',
            KegiatanStatus::DITOLAK => 'red',
            KegiatanStatus::SELESAI => 'purple',
        };
    }
}
