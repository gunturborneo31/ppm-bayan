<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumePilarFormatSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    private const TABLE_HEADER = [
        'Program Utama PPM Tahunan',
        'Rincian Kegiatan PPM',
        'Lokasi Kegiatan',
        'Waktu Pelaksanaan PPM',
        'Rencana Pembiayaan PPM',
        'Realisasi PPM',
        'Sisa Anggaran',
        'Serapan (%)',
        'Nilai Manfaat Program',
        'KETERANGAN',
    ];
    private const TOP_OFFSET_ROWS = 4;
    private const LEFT_OFFSET_COLUMNS = 1;
    private const START_COLUMN = 'B';

    private array $rows = [];
    private array $tableHeaderRows = [];
    private array $pilarHeaderRows = [];
    private array $programMergeRanges = [];
    private array $pilarTotalRows = [];
    private ?int $grandTotalRow = null;

    public function __construct(private readonly array $payload)
    {
        $this->buildRows();
    }

    public function title(): string
    {
        return 'Rekap Pilar';
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, self::TOP_OFFSET_ROWS);
                $sheet->insertNewColumnBefore('A', self::LEFT_OFFSET_COLUMNS);

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $endColumn = $this->sheetEndColumn();
                $tableStartRow = $this->sheetRow(1);

                // Keep top offset rows clean (no borders), start table border from first header row.
                $sheet->getStyle(self::START_COLUMN . "{$tableStartRow}:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle(self::START_COLUMN . "1:{$highestColumn}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle(self::START_COLUMN . "1:{$highestColumn}{$highestRow}")->getAlignment()->setWrapText(true);

                $sheet->mergeCells(self::START_COLUMN . "1:{$endColumn}2");
                $sheet->mergeCells(self::START_COLUMN . "3:{$endColumn}4");
                $sheet->setCellValue(self::START_COLUMN . '1', 'REKAP RESUME PILAR PPM');
                $sheet->setCellValue(self::START_COLUMN . '3', 'Data disusun per pilar sesuai nomor urut manajemen pilar.');
                $sheet->getStyle(self::START_COLUMN . "1:{$endColumn}2")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle(self::START_COLUMN . "1:{$endColumn}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle(self::START_COLUMN . "3:{$endColumn}4")->getFont()->setSize(10);
                $sheet->getStyle(self::START_COLUMN . "3:{$endColumn}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                foreach ($this->tableHeaderRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getFont()->setBold(true);
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2DCDB');
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                foreach ($this->pilarHeaderRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->mergeCells(self::START_COLUMN . "{$r}:{$endColumn}{$r}");
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getFont()->setBold(true);
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                foreach ($this->programMergeRanges as $range) {
                    if ($range['start'] < $range['end']) {
                        $start = $this->sheetRow($range['start']);
                        $end = $this->sheetRow($range['end']);
                        $sheet->mergeCells("B{$start}:B{$end}");
                    }
                }

                foreach ($this->pilarTotalRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->getStyle("B{$r}:{$endColumn}{$r}")->getFont()->setBold(true);
                    $sheet->getStyle("B{$r}:{$endColumn}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('92D050');
                    $sheet->getStyle("F{$r}:H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                if ($this->grandTotalRow !== null) {
                    $r = $this->sheetRow($this->grandTotalRow);
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->getStyle("B{$r}:{$endColumn}{$r}")->getFont()->setBold(true);
                    $sheet->getStyle("B{$r}:{$endColumn}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('00B0F0');
                    $sheet->getStyle("B{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("F{$r}:H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $sheet->getStyle("F" . $this->sheetRow(2) . ":H{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("I" . $this->sheetRow(2) . ":I{$highestRow}")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("D" . $this->sheetRow(2) . ":E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F" . $this->sheetRow(2) . ":H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("I" . $this->sheetRow(2) . ":I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getColumnDimension('B')->setWidth(36);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(22);
                $sheet->getColumnDimension('H')->setWidth(22);
                $sheet->getColumnDimension('I')->setWidth(16);
                $sheet->getColumnDimension('J')->setWidth(24);
                $sheet->getColumnDimension('K')->setWidth(24);
            },
        ];
    }

    private function buildRows(): void
    {
        $drilldown = $this->payload['summary_drilldown'] ?? [];
        $detailByKegiatan = collect($this->payload['detail_rows'] ?? [])->groupBy('kegiatan_id');

        $pilars = collect($this->payload['pilar_summary'] ?? [])
            ->sortBy([
                ['no_urut', 'asc'],
                ['nama', 'asc'],
            ])
            ->values();

        $grandTotalAnggaran = 0.0;
        $grandTotalRealisasi = 0.0;
        $grandTotalSisa = 0.0;

        foreach ($pilars as $index => $pilar) {
            $this->rows[] = self::TABLE_HEADER;
            $this->tableHeaderRows[] = count($this->rows);

            $pilarId = $pilar['id'] ?? null;
            $pilarName = (string) ($pilar['nama'] ?? '-');
            $programs = collect($drilldown[(string) $pilarId] ?? $drilldown[$pilarId] ?? [])->values();

            $this->rows[] = [
                strtoupper($pilarName),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
            $this->pilarHeaderRows[] = count($this->rows);

            if ($programs->isEmpty()) {
                $this->rows[] = ['(belum ada program)', '', '', '', 0, 0, 0, 0, '', ''];
            }

            foreach ($programs as $program) {
                $kegiatans = collect($program['kegiatans'] ?? [])->values();
                $startRow = count($this->rows) + 1;

                if ($kegiatans->isEmpty()) {
                    $this->rows[] = [
                        ($program['nama'] ?? '-'),
                        '',
                        '',
                        '',
                        0,
                        0,
                        0,
                        0,
                        '',
                        '',
                    ];
                }

                foreach ($kegiatans as $kegiatanIndex => $kegiatan) {
                    $programLabel = $kegiatanIndex === 0 ? (($program['nama'] ?? '-')) : '';
                    $detailRows = collect($detailByKegiatan->get((int) ($kegiatan['id'] ?? 0), []))->values();

                    if ($detailRows->isEmpty()) {
                        $this->rows[] = [
                            $programLabel,
                            ($kegiatan['nama'] ?? '-'),
                            '-',
                            '-',
                            (float) ($kegiatan['anggaran'] ?? 0),
                            (float) ($kegiatan['realisasi'] ?? 0),
                            (float) ($kegiatan['sisa_anggaran'] ?? 0),
                            (float) ($kegiatan['persentase_serapan'] ?? 0),
                            '-',
                            '-',
                        ];
                        continue;
                    }

                    foreach ($detailRows as $detailIndex => $detail) {
                        $this->rows[] = [
                            $detailIndex === 0 ? $programLabel : '',
                            $detailIndex === 0 ? ($kegiatan['nama'] ?? '-') : '',
                            $detail['lokasi_kegiatan'] ?? '-',
                            $detail['waktu_pelaksanaan'] ?? '-',
                            (float) ($detail['anggaran'] ?? 0),
                            (float) ($detail['realisasi'] ?? 0),
                            (float) ($detail['sisa_anggaran'] ?? 0),
                            (float) ($detail['persentase_serapan'] ?? 0),
                            $detailIndex === 0 ? ($detail['nilai_manfaat_program'] ?? '-') : '',
                            $detailIndex === 0 ? ($detail['keterangan'] ?? '-') : '',
                        ];
                    }
                }

                $endRow = count($this->rows);
                $this->programMergeRanges[] = [
                    'start' => $startRow,
                    'end' => $endRow,
                ];
            }

            $totalPilar = (float) ($pilar['total_anggaran'] ?? 0);
            $totalRealisasiPilar = (float) ($pilar['total_realisasi'] ?? 0);
            $totalSisaPilar = (float) ($pilar['sisa_anggaran'] ?? 0);
            $totalSerapanPilar = (float) ($pilar['persentase_serapan'] ?? 0);

            $grandTotalAnggaran += $totalPilar;
            $grandTotalRealisasi += $totalRealisasiPilar;
            $grandTotalSisa += $totalSisaPilar;

            $this->rows[] = [
                'Total Biaya ' . $pilarName,
                '',
                '',
                '',
                $totalPilar,
                $totalRealisasiPilar,
                $totalSisaPilar,
                $totalSerapanPilar,
                '',
                '',
            ];
            $this->pilarTotalRows[] = count($this->rows);
        }

        if ($pilars->isNotEmpty()) {
            $grandSerapan = $grandTotalAnggaran > 0
                ? round(($grandTotalRealisasi / $grandTotalAnggaran) * 100, 2)
                : 0;

            $this->rows[] = [
                'TOTAL KESELURUHAN',
                '',
                '',
                '',
                $grandTotalAnggaran,
                $grandTotalRealisasi,
                $grandTotalSisa,
                $grandSerapan,
                '',
                '',
            ];
            $this->grandTotalRow = count($this->rows);
        }
    }

    private function sheetRow(int $row): int
    {
        return $row + self::TOP_OFFSET_ROWS;
    }

    private function sheetEndColumn(): string
    {
        $start = Coordinate::columnIndexFromString(self::START_COLUMN);
        $end = $start + count(self::TABLE_HEADER) - 1;

        return Coordinate::stringFromColumnIndex($end);
    }
}
