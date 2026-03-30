<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ResumeExport implements WithMultipleSheets
{
    public function __construct(private readonly array $payload)
    {
    }

    public function sheets(): array
    {
        return [
            new ResumeArraySheet(
                title: 'Ringkasan',
                headings: ['Parameter', 'Nilai'],
                rows: [
                    ['Total Pagu Program', $this->payload['summary']['total_pagu'] ?? 0],
                    ['Total Anggaran Kegiatan', $this->payload['summary']['total_anggaran'] ?? 0],
                    ['Total Realisasi', $this->payload['summary']['total_realisasi'] ?? 0],
                    ['Sisa Anggaran', $this->payload['summary']['sisa_anggaran'] ?? 0],
                    ['Persentase Serapan (%)', $this->payload['summary']['persentase_serapan'] ?? 0],
                    ['Total Program', $this->payload['summary']['total_program'] ?? 0],
                    ['Total Kegiatan', $this->payload['summary']['total_kegiatan'] ?? 0],
                    ['Filter User', $this->payload['filter_summary']['user'] ?? 'Semua User'],
                    ['Filter Divisi', $this->payload['filter_summary']['divisi'] ?? 'Semua Divisi'],
                    ['Filter Pilar', $this->payload['filter_summary']['pilar'] ?? 'Semua Pilar'],
                    ['Filter Status', $this->payload['filter_summary']['status'] ?? 'Semua Status'],
                    ['Filter Tahun', $this->payload['filter_summary']['tahun'] ?? 'Semua Tahun'],
                    ['Filter Triwulan', $this->payload['filter_summary']['triwulan'] ?? 'Semua Triwulan'],
                    ['Pencarian', $this->payload['filter_summary']['q'] ?? '-'],
                ],
                currencyColumns: ['B'],
                percentColumns: ['B']
            ),
            new ResumeArraySheet(
                title: 'Rekap Pilar',
                headings: ['Pilar', 'Program', 'Kegiatan', 'Anggaran', 'Realisasi', 'Sisa Anggaran', 'Serapan (%)'],
                rows: collect($this->payload['pilar_summary'] ?? [])->map(fn (array $row) => [
                    $row['nama'],
                    $row['jumlah_program'],
                    $row['jumlah_kegiatan'],
                    $row['total_anggaran'],
                    $row['total_realisasi'],
                    $row['sisa_anggaran'],
                    $row['persentase_serapan'],
                ])->all(),
                currencyColumns: ['D', 'E', 'F'],
                percentColumns: ['G']
            ),
            new ResumeArraySheet(
                title: 'Rekap Program',
                headings: ['Program', 'Kegiatan', 'Anggaran', 'Realisasi', 'Sisa Anggaran', 'Serapan (%)'],
                rows: collect($this->payload['program_summary'] ?? [])->map(fn (array $row) => [
                    $row['nama'],
                    $row['jumlah_kegiatan'],
                    $row['total_anggaran'],
                    $row['total_realisasi'],
                    $row['sisa_anggaran'],
                    $row['persentase_serapan'],
                ])->all(),
                currencyColumns: ['C', 'D', 'E'],
                percentColumns: ['F']
            ),
            new ResumeArraySheet(
                title: 'Rekap Divisi',
                headings: ['Divisi', 'Program', 'Kegiatan', 'Anggaran', 'Realisasi', 'Sisa Anggaran', 'Serapan (%)'],
                rows: collect($this->payload['divisi_summary'] ?? [])->map(fn (array $row) => [
                    $row['nama'],
                    $row['jumlah_program'],
                    $row['jumlah_kegiatan'],
                    $row['total_anggaran'],
                    $row['total_realisasi'],
                    $row['sisa_anggaran'],
                    $row['persentase_serapan'],
                ])->all(),
                currencyColumns: ['D', 'E', 'F'],
                percentColumns: ['G']
            ),
            new ResumeArraySheet(
                title: 'Rekap User',
                headings: ['User', 'Divisi', 'Program', 'Kegiatan', 'Anggaran', 'Realisasi', 'Sisa Anggaran', 'Serapan (%)'],
                rows: collect($this->payload['user_summary'] ?? [])->map(fn (array $row) => [
                    $row['nama'],
                    $row['divisi'] ?? '-',
                    $row['jumlah_program'],
                    $row['jumlah_kegiatan'],
                    $row['total_anggaran'],
                    $row['total_realisasi'],
                    $row['sisa_anggaran'],
                    $row['persentase_serapan'],
                ])->all(),
                currencyColumns: ['E', 'F', 'G'],
                percentColumns: ['H']
            ),
            new ResumeArraySheet(
                title: 'Detail Kegiatan',
                headings: [
                    'Kode Ref',
                    'Program',
                    'Kegiatan',
                    'Divisi',
                    'Pilar',
                    'User Penginput',
                    'Status',
                    'Target Output',
                    'Satuan',
                    'Anggaran',
                    'Realisasi',
                    'Sisa Anggaran',
                    'Serapan (%)',
                    'Realisasi Output',
                    'Progress (%)',
                ],
                rows: collect($this->payload['detail_rows'] ?? [])->map(fn (array $row) => [
                    $row['kode_ref'],
                    $row['program'],
                    $row['kegiatan'],
                    $row['divisi'],
                    $row['pilar'],
                    $row['user_penginput'],
                    $row['status_label'],
                    $row['target_output'],
                    $row['satuan'],
                    $row['anggaran'],
                    $row['realisasi'],
                    $row['sisa_anggaran'],
                    $row['persentase_serapan'],
                    $row['realisasi_output'],
                    $row['progress'],
                ])->all(),
                currencyColumns: ['J', 'K', 'L'],
                percentColumns: ['M', 'O']
            ),
        ];
    }
}
