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

class ResumeUserFormatSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    private const TOP_OFFSET_ROWS = 4;
    private const LEFT_OFFSET_COLUMNS = 1;
    private const START_COLUMN = 'B';
    private const TABLE_HEADER = [
        'User',
        'Divisi',
        'Nama Data',
        'Tipe',
        'Perencanaan',
        'Realisasi',
        'Sisa',
        'Serapan (%)',
    ];

    private array $rows = [];
    private array $headerRows = [];
    private array $userRows = [];
    private array $totalRows = [];
    private ?int $grandTotalRow = null;

    public function __construct(private readonly array $payload)
    {
        $this->buildRows();
    }

    public function title(): string
    {
        return 'Rekap User';
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

                $sheet->getStyle(self::START_COLUMN . "{$tableStartRow}:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle(self::START_COLUMN . "1:{$highestColumn}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle(self::START_COLUMN . "1:{$highestColumn}{$highestRow}")->getAlignment()->setWrapText(true);

                $sheet->mergeCells(self::START_COLUMN . "1:{$endColumn}2");
                $sheet->mergeCells(self::START_COLUMN . "3:{$endColumn}4");
                $sheet->setCellValue(self::START_COLUMN . '1', 'REKAP RESUME USER PPM');
                $sheet->setCellValue(self::START_COLUMN . '3', 'Data menyesuaikan filter aktif (bulan/triwulan/semester).');
                $sheet->getStyle(self::START_COLUMN . "1:{$endColumn}2")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle(self::START_COLUMN . "1:{$endColumn}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle(self::START_COLUMN . "3:{$endColumn}4")->getFont()->setSize(10);

                foreach ($this->headerRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getFont()->setBold(true);
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8EEF7');
                    $sheet->getStyle(self::START_COLUMN . "{$r}:{$endColumn}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                foreach ($this->userRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->getStyle("B{$r}:I{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                    $sheet->getStyle("B{$r}:I{$r}")->getFont()->setBold(true);
                }

                foreach ($this->totalRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->getStyle("B{$r}:I{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CFE8A9');
                    $sheet->getStyle("B{$r}:I{$r}")->getFont()->setBold(true);
                }

                if ($this->grandTotalRow !== null) {
                    $r = $this->sheetRow($this->grandTotalRow);
                    $sheet->mergeCells("B{$r}:E{$r}");
                    $sheet->getStyle("B{$r}:I{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('00B0F0');
                    $sheet->getStyle("B{$r}:I{$r}")->getFont()->setBold(true);
                }

                $numStart = $this->sheetRow(2);
                $sheet->getStyle("F{$numStart}:H{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("I{$numStart}:I{$highestRow}")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("F{$numStart}:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getColumnDimension('B')->setWidth(24);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(36);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(18);
                $sheet->getColumnDimension('I')->setWidth(14);
            },
        ];
    }

    private function buildRows(): void
    {
        $this->rows[] = self::TABLE_HEADER;
        $this->headerRows[] = count($this->rows);

        $drilldown = $this->payload['summary_drilldown'] ?? [];
        $userSummary = collect($this->payload['user_summary'] ?? [])->values();

        $grandAnggaran = 0.0;
        $grandRealisasi = 0.0;

        foreach ($userSummary as $user) {
            $userId = $user['id'] ?? null;
            $name = (string) ($user['nama'] ?? '-');
            $divisi = (string) ($user['divisi'] ?? '-');
            $anggaran = (float) ($user['total_anggaran'] ?? 0);
            $realisasi = (float) ($user['total_realisasi'] ?? 0);
            $sisa = (float) ($user['sisa_anggaran'] ?? 0);
            $serapan = (float) ($user['persentase_serapan'] ?? 0);

            $grandAnggaran += $anggaran;
            $grandRealisasi += $realisasi;

            $this->rows[] = [$name, $divisi, $name, 'User', $anggaran, $realisasi, $sisa, $serapan];
            $this->userRows[] = count($this->rows);

            $programs = collect($drilldown[(string) $userId] ?? $drilldown[$userId] ?? [])->values();

            foreach ($programs as $program) {
                $this->rows[] = [
                    '',
                    '',
                    (string) ($program['nama'] ?? '-'),
                    'Program',
                    (float) ($program['total_anggaran'] ?? 0),
                    (float) ($program['total_realisasi'] ?? 0),
                    (float) ($program['sisa_anggaran'] ?? 0),
                    (float) ($program['persentase_serapan'] ?? 0),
                ];

                foreach (collect($program['kegiatans'] ?? [])->values() as $kegiatan) {
                    $this->rows[] = [
                        '',
                        '',
                        (string) ($kegiatan['nama'] ?? '-'),
                        'Kegiatan',
                        (float) ($kegiatan['anggaran'] ?? 0),
                        (float) ($kegiatan['realisasi'] ?? 0),
                        (float) ($kegiatan['sisa_anggaran'] ?? 0),
                        (float) ($kegiatan['persentase_serapan'] ?? 0),
                    ];

                    foreach (collect($kegiatan['lokasis'] ?? [])->values() as $lokasi) {
                        $this->rows[] = [
                            '',
                            '',
                            ' - Lokasi: ' . (string) ($lokasi['nama'] ?? '-'),
                            'Lokasi',
                            (float) ($lokasi['anggaran'] ?? 0),
                            (float) ($lokasi['realisasi'] ?? 0),
                            (float) ($lokasi['sisa_anggaran'] ?? 0),
                            (float) ($lokasi['persentase_serapan'] ?? 0),
                        ];
                    }
                }
            }

            $this->rows[] = [
                'Total User ' . $name,
                '',
                '',
                '',
                $anggaran,
                $realisasi,
                $sisa,
                $serapan,
            ];
            $this->totalRows[] = count($this->rows);
        }

        $grandSisa = $grandAnggaran - $grandRealisasi;
        $grandSerapan = $grandAnggaran > 0 ? round(($grandRealisasi / $grandAnggaran) * 100, 2) : 0;

        $this->rows[] = ['TOTAL KESELURUHAN', '', '', '', $grandAnggaran, $grandRealisasi, $grandSisa, $grandSerapan];
        $this->grandTotalRow = count($this->rows);
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
