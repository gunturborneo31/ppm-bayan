<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumeChartSheet implements FromArray, ShouldAutoSize, WithCharts, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly string $titleLabel,
        private readonly array $rows,
    ) {
    }

    public function title(): string
    {
        return 'Grafik Resume';
    }

    public function headings(): array
    {
        return ['Kategori', 'Rencana', 'Realisasi', 'Serapan (%)'];
    }

    public function array(): array
    {
        return $this->rows === []
            ? [['Belum ada data', 0, 0, 0]]
            : $this->rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '0F3C73'],
                ],
            ],
        ];
    }

    public function charts(): array
    {
        $endRow = max(count($this->rows) + 1, 2);
        $sheetName = "'" . $this->title() . "'";

        $labels = [
            new DataSeriesValues('String', $sheetName . '!$B$1', null, 1),
            new DataSeriesValues('String', $sheetName . '!$C$1', null, 1),
            new DataSeriesValues('String', $sheetName . '!$D$1', null, 1),
        ];

        $categories = [
            new DataSeriesValues('String', $sheetName . '!$A$2:$A$' . $endRow, null, max($endRow - 1, 1)),
        ];

        $values = [
            new DataSeriesValues('Number', $sheetName . '!$B$2:$B$' . $endRow, null, max($endRow - 1, 1)),
            new DataSeriesValues('Number', $sheetName . '!$C$2:$C$' . $endRow, null, max($endRow - 1, 1)),
            new DataSeriesValues('Number', $sheetName . '!$D$2:$D$' . $endRow, null, max($endRow - 1, 1)),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1),
            $labels,
            $categories,
            $values,
        );
        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        $plotArea = new PlotArea(new Layout(), [$series]);

        $chart = new Chart(
            'resume_chart',
            new Title('Grafik Batang ' . $this->titleLabel),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            $plotArea,
            true,
            0,
            new Title('Kategori'),
            new Title('Nilai')
        );

        $chart->setTopLeftPosition('F2');
        $chart->setBottomRightPosition('N22');

        return [$chart];
    }
}