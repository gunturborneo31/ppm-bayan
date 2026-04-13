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
        $resumeType = (string) ($this->payload['resume_type'] ?? 'pilar');
        $chartRows = collect($this->payload['chart_rows'] ?? [])->map(fn (array $row) => [
            $row['nama'] ?? '-',
            (float) ($row['total_anggaran'] ?? 0),
            (float) ($row['total_realisasi'] ?? 0),
            (float) ($row['persentase_serapan'] ?? 0),
        ])->all();

        if ($resumeType === 'pilar') {
            return [
                new ResumePilarFormatSheet($this->payload),
                new ResumeChartSheet('Resume Pilar', $chartRows),
            ];
        }

        if ($resumeType === 'program') {
            return [
                new ResumeProgramFormatSheet($this->payload),
                new ResumeChartSheet('Resume Program', $chartRows),
            ];
        }

        if ($resumeType === 'divisi') {
            return [
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
                new ResumeChartSheet('Resume Divisi', $chartRows),
            ];
        }

        return [
            new ResumeUserFormatSheet($this->payload),
            new ResumeChartSheet('Resume User', $chartRows),
        ];
    }
}
