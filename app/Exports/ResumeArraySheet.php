<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumeArraySheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle, WithEvents
{
    private const HEADER_ROW = 5;
    private const FIRST_DATA_ROW = 6;
    private const START_COLUMN = 'B';

    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows,
        private readonly array $currencyColumns = [],
        private readonly array $percentColumns = [],
    ) {
    }

    public function array(): array
    {
        return $this->rows === [] ? [['Belum ada data']] : $this->rows;
    }

    public function headings(): array
    {
        return $this->headings === [] ? ['Informasi'] : $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestColumn = $this->shiftColumn($sheet->getHighestColumn(), 1);
        $highestRow = $sheet->getHighestRow();

        return [
            self::HEADER_ROW => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F3C73'],
                ],
            ],
            self::START_COLUMN . self::HEADER_ROW . ":{$highestColumn}{$highestRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, self::HEADER_ROW - 1);
                $sheet->insertNewColumnBefore('A', 1);

                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $logoIconPath = public_path('logo-bayan.png');
                $logoTextPath = public_path('logo-bayan-text.png');
                $headerStartColumn = $highestColumnIndex >= 3 ? 'C' : 'A';
                $titleRow = $highestColumnIndex >= 3 ? 1 : 2;
                $sheetTitleRow = $highestColumnIndex >= 3 ? 2 : 3;
                $subtitleRow = $highestColumnIndex >= 3 ? 3 : 4;

                $headerStartColumn = $this->shiftColumn($headerStartColumn, 1);

                $sheet->setCellValue("{$headerStartColumn}{$titleRow}", 'Resume PPM Bayan');
                $sheet->setCellValue("{$headerStartColumn}{$sheetTitleRow}", $this->title);
                $sheet->setCellValue("{$headerStartColumn}{$subtitleRow}", 'Export dibuat dari modul Resume dengan filter aktif.');

                if ($headerStartColumn !== $highestColumn) {
                    $sheet->mergeCells("{$headerStartColumn}{$titleRow}:{$highestColumn}{$titleRow}");
                    $sheet->mergeCells("{$headerStartColumn}{$sheetTitleRow}:{$highestColumn}{$sheetTitleRow}");
                    $sheet->mergeCells("{$headerStartColumn}{$subtitleRow}:{$highestColumn}{$subtitleRow}");
                }

                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(8);

                if (is_file($logoIconPath)) {
                    $icon = new Drawing();
                    $icon->setPath($logoIconPath);
                    $icon->setCoordinates('B1');
                    $icon->setHeight(40);
                    $icon->setWorksheet($sheet);
                }

                if (is_file($logoTextPath)) {
                    $wordmark = new Drawing();
                    $wordmark->setPath($logoTextPath);
                    $wordmark->setCoordinates('C1');
                    $wordmark->setHeight(28);
                    $wordmark->setOffsetY(6);
                    $wordmark->setWorksheet($sheet);
                }

                $sheet->freezePane(self::START_COLUMN . self::FIRST_DATA_ROW);
                $sheet->getStyle(self::START_COLUMN . self::HEADER_ROW . ":{$highestColumn}" . self::HEADER_ROW)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle(self::START_COLUMN . "1:{$highestColumn}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("{$headerStartColumn}{$titleRow}:{$headerStartColumn}{$sheetTitleRow}")->getFont()->setBold(true)->setColor(new Color('0F3C73'));
                $sheet->getStyle("{$headerStartColumn}{$titleRow}")->getFont()->setSize(16);
                $sheet->getStyle("{$headerStartColumn}{$sheetTitleRow}")->getFont()->setSize(11);
                $sheet->getStyle("{$headerStartColumn}{$subtitleRow}")->getFont()->setSize(9)->setColor(new Color('6B7280'));
                $sheet->getStyle(self::START_COLUMN . self::HEADER_ROW . ":{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color('D1D5DB'));

                foreach ($this->currencyColumns as $column) {
                    $shiftedColumn = $this->shiftColumn($column, 1);
                    $sheet->getStyle("{$shiftedColumn}" . self::FIRST_DATA_ROW . ":{$shiftedColumn}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                    $sheet->getStyle("{$shiftedColumn}" . self::FIRST_DATA_ROW . ":{$shiftedColumn}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                foreach ($this->percentColumns as $column) {
                    $shiftedColumn = $this->shiftColumn($column, 1);
                    $sheet->getStyle("{$shiftedColumn}" . self::FIRST_DATA_ROW . ":{$shiftedColumn}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                    $sheet->getStyle("{$shiftedColumn}" . self::FIRST_DATA_ROW . ":{$shiftedColumn}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }

    private function shiftColumn(string $column, int $offset): string
    {
        $index = Coordinate::columnIndexFromString($column);

        return Coordinate::stringFromColumnIndex($index + $offset);
    }
}