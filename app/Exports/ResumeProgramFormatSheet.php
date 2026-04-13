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

class ResumeProgramFormatSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    private const TOP_OFFSET_ROWS = 4;
    private const LEFT_OFFSET_COLUMNS = 1;
    private const START_COLUMN = 'B';
    private const TABLE_HEADER = [
        'Program',
        'Nama Data',
        'Tipe',
        'Perencanaan',
        'Realisasi',
        'Sisa',
        'Serapan (%)',
    ];

    private array $rows = [];
    private array $headerRows = [];
    private array $programRows = [];
    private array $totalRows = [];
    private ?int $grandTotalRow = null;

    public function __construct(private readonly array $payload)
    {
        $this->buildRows();
    }

    public function title(): string
    {
        return 'Rekap Program';
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
                $sheet->setCellValue(self::START_COLUMN . '1', 'REKAP RESUME PROGRAM PPM');
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

                foreach ($this->programRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->getStyle("B{$r}:H{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                    $sheet->getStyle("B{$r}:H{$r}")->getFont()->setBold(true);
                }

                foreach ($this->totalRows as $row) {
                    $r = $this->sheetRow($row);
                    $sheet->mergeCells("B{$r}:D{$r}");
                    $sheet->getStyle("B{$r}:H{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CFE8A9');
                    $sheet->getStyle("B{$r}:H{$r}")->getFont()->setBold(true);
                }

                if ($this->grandTotalRow !== null) {
                    $r = $this->sheetRow($this->grandTotalRow);
                    $sheet->mergeCells("B{$r}:D{$r}");
                    $sheet->getStyle("B{$r}:H{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('00B0F0');
                    $sheet->getStyle("B{$r}:H{$r}")->getFont()->setBold(true);
                }

                $numStart = $this->sheetRow(2);
                $sheet->getStyle("E{$numStart}:G{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("H{$numStart}:H{$highestRow}")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("E{$numStart}:H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(36);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(14);
            },
        ];
    }

    private function buildRows(): void
    {
        $this->rows[] = self::TABLE_HEADER;
        $this->headerRows[] = count($this->rows);

        $drilldown = $this->payload['summary_drilldown'] ?? [];
        $programSummary = collect($this->payload['program_summary'] ?? [])->values();

        $grandAnggaran = 0.0;
        $grandRealisasi = 0.0;

        foreach ($programSummary as $program) {
            $programId = $program['id'] ?? null;
            $name = (string) ($program['nama'] ?? '-');
            $anggaran = (float) ($program['total_anggaran'] ?? 0);
            $realisasi = (float) ($program['total_realisasi'] ?? 0);
            $sisa = (float) ($program['sisa_anggaran'] ?? 0);
            $serapan = (float) ($program['persentase_serapan'] ?? 0);

            $grandAnggaran += $anggaran;
            $grandRealisasi += $realisasi;

            $this->rows[] = [$name, $name, 'Program', $anggaran, $realisasi, $sisa, $serapan];
            $this->programRows[] = count($this->rows);

            $programItems = collect($drilldown[(string) $programId] ?? $drilldown[$programId] ?? [])->values();

            foreach ($programItems as $item) {
                foreach (collect($item['kegiatans'] ?? [])->values() as $kegiatan) {
                    $this->rows[] = [
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
                'Total Program ' . $name,
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

        $this->rows[] = ['TOTAL KESELURUHAN', '', '', $grandAnggaran, $grandRealisasi, $grandSisa, $grandSerapan];
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
