<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\Pilar;
use App\Models\Program;
use App\Models\Realisasi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicDashboardController extends Controller
{
    public function index(Request $request)
    {
        $pilarColorMap = Pilar::colorMap();

        $tahunOptions = Periode::query()
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->values();

        $currentYear = Carbon::now()->year;
        $selectedYear = (int) ($request->input('tahun') ?: ($tahunOptions->first() ?: $currentYear));

        $periodMode = in_array($request->input('period_mode'), ['bulan', 'triwulan', 'semester'], true)
            ? $request->input('period_mode')
            : 'bulan';

        $periodValue = $request->input('period_value');
        $periodValue = ($periodValue !== null && $periodValue !== '') ? (int) $periodValue : null;
        $monthRange = $this->periodToRange($periodMode, $periodValue);

        $realisasiQuery = Realisasi::query()
            ->whereHas('periode', function (Builder $query) use ($selectedYear, $monthRange) {
                $query->where('tahun', $selectedYear);

                if ($monthRange) {
                    $query->whereBetween('bulan', $monthRange);
                }
            });

        $totalAnggaranPilar = (float) Pilar::query()->sum('rencana_biaya');
        $totalPagu = (float) Program::query()->sum('rencana_biaya');
        $totalRencana = (float) Kegiatan::query()->sum('rencana_biaya');
        $totalTargetOutput = (float) Kegiatan::query()->sum('target_output');
        $totalRealisasiBiaya = (float) (clone $realisasiQuery)->sum('realisasi_biaya');
        $totalRealisasiOutput = (float) (clone $realisasiQuery)->sum('realisasi_output');
        $sisaRencanaBiaya = max(0, $totalRencana - $totalRealisasiBiaya);
        $sisaTargetOutput = max(0, $totalTargetOutput - $totalRealisasiOutput);
        $totalSisaAnggaranPilar = max(0, $totalAnggaranPilar - $totalPagu);
        $programDianggarkanCount = (int) Program::query()->where('rencana_biaya', '>', 0)->count();

        $serapanPersen = $totalRencana > 0
            ? round(($totalRealisasiBiaya / $totalRencana) * 100, 2)
            : 0;

        $targetPersen = $totalTargetOutput > 0
            ? round(($totalRealisasiOutput / $totalTargetOutput) * 100, 2)
            : 0;

        $pilarRows = Pilar::query()
            ->with(['programs:id,pilar_id,nama,rencana_biaya'])
            ->orderBy('nama')
            ->get();

        $realisasiByPilar = Realisasi::query()
            ->selectRaw('programs.pilar_id as pilar_id, COALESCE(SUM(realisasis.realisasi_biaya),0) as total')
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->where('periodes.tahun', $selectedYear)
            ->when($monthRange, fn($query) => $query->whereBetween('periodes.bulan', $monthRange))
            ->groupBy('programs.pilar_id')
            ->pluck('total', 'pilar_id');

        $programCountByPilar = Program::query()
            ->selectRaw('pilar_id, COUNT(*) as total')
            ->groupBy('pilar_id')
            ->pluck('total', 'pilar_id');

        $kegiatanCountByPilar = Kegiatan::query()
            ->selectRaw('programs.pilar_id as pilar_id, COUNT(*) as total')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->groupBy('programs.pilar_id')
            ->pluck('total', 'pilar_id');

        $pilarSummary = $pilarRows->map(function (Pilar $pilar) use ($realisasiByPilar, $programCountByPilar, $kegiatanCountByPilar) {
            $totalProgramBiaya = (float) ($pilar->programs->sum('rencana_biaya') ?? 0);

            return [
                'id' => $pilar->id,
                'nama' => $pilar->nama,
                'color' => Pilar::colorForName($pilar->nama),
                'total_program' => (int) ($programCountByPilar[$pilar->id] ?? 0),
                'total_kegiatan' => (int) ($kegiatanCountByPilar[$pilar->id] ?? 0),
                'total_anggaran' => (float) ($pilar->rencana_biaya ?? 0),
                'total_rencana' => $totalProgramBiaya,
                'total_realisasi' => (float) ($realisasiByPilar[$pilar->id] ?? 0),
                'total_sisa' => max(0, (float) ($pilar->rencana_biaya ?? 0) - $totalProgramBiaya),
            ];
        })->values();

        $programTable = Program::query()
            ->with('pilar:id,nama')
            ->when($selectedYear || $monthRange, function ($query) use ($selectedYear, $monthRange) {
                $query->whereHas('kegiatans.realisasis.periode', function (Builder $periodeQuery) use ($selectedYear, $monthRange) {
                    $periodeQuery->where('tahun', $selectedYear);

                    if ($monthRange) {
                        $periodeQuery->whereBetween('bulan', $monthRange);
                    }
                });
            })
            ->orderBy('nama')
            ->get()
            ->map(function (Program $program) use ($selectedYear, $monthRange, $pilarColorMap) {
                $realisasi = Realisasi::query()
                    ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
                    ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
                    ->where('kegiatans.program_id', $program->id)
                    ->where('periodes.tahun', $selectedYear)
                    ->when($monthRange, fn($query) => $query->whereBetween('periodes.bulan', $monthRange))
                    ->sum('realisasis.realisasi_biaya');

                $kegiatanCount = Kegiatan::query()->where('program_id', $program->id)->count();

                return [
                    'program' => $program->nama,
                    'pilar' => $program->pilar?->nama ?? '-',
                    'pilar_color' => Pilar::colorForName($program->pilar?->nama),
                    'jumlah_kegiatan' => $kegiatanCount,
                    'rencana_biaya' => (float) $program->rencana_biaya,
                    'realisasi_biaya' => (float) $realisasi,
                    'capaian_persen' => (float) ($program->rencana_biaya > 0
                        ? round(($realisasi / $program->rencana_biaya) * 100, 2)
                        : 0),
                ];
            })
            ->values();

        $divisiPalette = ['#2563eb', '#dc2626', '#16a34a', '#d97706', '#7c3aed', '#0891b2', '#db2777', '#0f766e', '#4f46e5', '#ea580c'];

        $divisiRows = Divisi::query()
            ->whereHas('kegiatans.realisasis.periode', function (Builder $periodeQuery) use ($selectedYear, $monthRange) {
                $periodeQuery->where('tahun', $selectedYear);

                if ($monthRange) {
                    $periodeQuery->whereBetween('bulan', $monthRange);
                }
            })
            ->with(['kegiatans' => function ($query) use ($selectedYear, $monthRange) {
                $query->whereHas('realisasis.periode', function (Builder $periodeQuery) use ($selectedYear, $monthRange) {
                    $periodeQuery->where('tahun', $selectedYear);

                    if ($monthRange) {
                        $periodeQuery->whereBetween('bulan', $monthRange);
                    }
                });
            }])
            ->orderBy('nama')
            ->get();

        $realisasiByDivisi = Realisasi::query()
            ->selectRaw('kegiatans.divisi_id as divisi_id, COALESCE(SUM(realisasis.realisasi_biaya),0) as total')
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->where('periodes.tahun', $selectedYear)
            ->when($monthRange, fn($query) => $query->whereBetween('periodes.bulan', $monthRange))
            ->groupBy('kegiatans.divisi_id')
            ->pluck('total', 'divisi_id');

        $divisiSummary = $divisiRows->values()->map(function (Divisi $divisi, int $index) use ($realisasiByDivisi, $divisiPalette) {
            $rencana = (float) $divisi->kegiatans->sum('rencana_biaya');
            $realisasi = (float) ($realisasiByDivisi[$divisi->id] ?? 0);

            return [
                'id' => $divisi->id,
                'nama' => $divisi->nama,
                'color' => $divisiPalette[$index % count($divisiPalette)],
                'jumlah_kegiatan' => (int) $divisi->kegiatans->count(),
                'rencana_biaya' => $rencana,
                'realisasi_biaya' => $realisasi,
                'sisa_biaya' => max(0, $rencana - $realisasi),
                'capaian_persen' => $rencana > 0 ? round(($realisasi / $rencana) * 100, 2) : 0,
            ];
        });

        $periodRows = $this->buildPeriodRows($selectedYear, $periodMode, $periodValue);

        return view('public-dashboard', [
            'filters' => [
                'tahun' => $selectedYear,
                'period_mode' => $periodMode,
                'period_value' => $periodValue,
            ],
            'options' => [
                'years' => $tahunOptions,
                'period_modes' => [
                    ['value' => 'bulan', 'label' => 'Bulan'],
                    ['value' => 'triwulan', 'label' => 'Triwulan'],
                    ['value' => 'semester', 'label' => 'Semester'],
                ],
                'period_options' => [
                    'bulan' => [
                        ['value' => 1, 'label' => 'Jan'], ['value' => 2, 'label' => 'Feb'],
                        ['value' => 3, 'label' => 'Mar'], ['value' => 4, 'label' => 'Apr'],
                        ['value' => 5, 'label' => 'Mei'], ['value' => 6, 'label' => 'Jun'],
                        ['value' => 7, 'label' => 'Jul'], ['value' => 8, 'label' => 'Agu'],
                        ['value' => 9, 'label' => 'Sep'], ['value' => 10, 'label' => 'Okt'],
                        ['value' => 11, 'label' => 'Nov'], ['value' => 12, 'label' => 'Des'],
                    ],
                    'triwulan' => [
                        ['value' => 1, 'label' => 'Tw 1 (Jan-Mar)'],
                        ['value' => 2, 'label' => 'Tw 2 (Apr-Jun)'],
                        ['value' => 3, 'label' => 'Tw 3 (Jul-Sep)'],
                        ['value' => 4, 'label' => 'Tw 4 (Okt-Des)'],
                    ],
                    'semester' => [
                        ['value' => 1, 'label' => 'Sem 1 (Jan-Jun)'],
                        ['value' => 2, 'label' => 'Sem 2 (Jul-Des)'],
                    ],
                ],
            ],
            'summary' => [
                'total_anggaran_pilar' => $totalAnggaranPilar,
                'program_dianggarkan_count' => $programDianggarkanCount,
                'total_pagu' => $totalPagu,
                'total_rencana' => $totalRencana,
                'total_realisasi' => $totalRealisasiBiaya,
                'total_sisa_anggaran_pilar' => $totalSisaAnggaranPilar,
                'total_sisa_rencana' => $sisaRencanaBiaya,
                'serapan_persen' => max(0, min(100, $serapanPersen)),
                'target_output' => $totalTargetOutput,
                'realisasi_output' => $totalRealisasiOutput,
                'sisa_output' => $sisaTargetOutput,
                'target_output_persen' => max(0, min(100, $targetPersen)),
            ],
            'charts' => [
                'overview_labels' => ['Total Anggaran Pilar', 'Program Dianggarkan', 'Sisa Anggaran Pilar'],
                'overview_values' => [$totalAnggaranPilar, $programDianggarkanCount, $totalSisaAnggaranPilar],
                'allocation_labels' => ['Anggaran Pilar', 'Telah Direncanakan', 'Sisa Anggaran'],
                'allocation_values' => [$totalAnggaranPilar, $totalPagu, $totalSisaAnggaranPilar],
                'pilar_labels' => $pilarSummary->pluck('nama')->values(),
                'pilar_anggaran' => $pilarSummary->pluck('total_anggaran')->values(),
                'pilar_rencana' => $pilarSummary->pluck('total_rencana')->values(),
                'pilar_realisasi' => $pilarSummary->pluck('total_realisasi')->values(),
                'monthly_labels' => $periodRows->pluck('label')->values(),
                'monthly_realisasi' => $periodRows->pluck('total')->values(),
                'pie_labels' => ['Realisasi', 'Sisa Rencana'],
                'pie_values' => [$totalRealisasiBiaya, $sisaRencanaBiaya],
                'doughnut_labels' => ['Output Terealisasi', 'Sisa Target Output'],
                'doughnut_values' => [$totalRealisasiOutput, $sisaTargetOutput],
                'distribution_labels' => $pilarSummary->pluck('nama')->values(),
                'distribution_values' => $pilarSummary->pluck('total_anggaran')->values(),
                'realisasi_bar_labels' => $pilarSummary->pluck('nama')->values(),
                'realisasi_bar_values' => $pilarSummary->pluck('total_realisasi')->values(),
                'pilar_colors' => $pilarSummary->map(fn ($item) => Pilar::colorForName($item['nama']))->values(),
                'divisi_labels' => $divisiSummary->pluck('nama')->values(),
                'divisi_rencana' => $divisiSummary->pluck('rencana_biaya')->values(),
                'divisi_realisasi' => $divisiSummary->pluck('realisasi_biaya')->values(),
                'divisi_colors' => $divisiSummary->pluck('color')->values(),
            ],
            'pilarSummary' => $pilarSummary,
            'programTable' => $programTable,
            'divisiSummary' => $divisiSummary,
        ]);
    }

    private function periodToRange(string $mode, ?int $value): ?array
    {
        if (!$value) {
            return null;
        }

        if ($mode === 'triwulan') {
            return match ($value) {
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12],
                default => null,
            };
        }

        if ($mode === 'semester') {
            return match ($value) {
                1 => [1, 6],
                2 => [7, 12],
                default => null,
            };
        }

        return ($value >= 1 && $value <= 12) ? [$value, $value] : null;
    }

    private function buildPeriodRows(int $year, string $mode, ?int $value = null): Collection
    {
        if ($mode === 'triwulan') {
            $labels = [
                1 => 'Tw 1',
                2 => 'Tw 2',
                3 => 'Tw 3',
                4 => 'Tw 4',
            ];

            $rows = Realisasi::query()
                ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
                ->where('periodes.tahun', $year)
                ->selectRaw('CASE
                    WHEN periodes.bulan BETWEEN 1 AND 3 THEN 1
                    WHEN periodes.bulan BETWEEN 4 AND 6 THEN 2
                    WHEN periodes.bulan BETWEEN 7 AND 9 THEN 3
                    ELSE 4
                END as periode_key, COALESCE(SUM(realisasis.realisasi_biaya),0) as total')
                ->groupBy('periode_key')
                ->pluck('total', 'periode_key');

            return collect($labels)
                ->when($value, fn ($collection) => $collection->only([$value]))
                ->map(fn ($label, $key) => [
                    'label' => $label,
                    'total' => (float) ($rows[$key] ?? 0),
                ])
                ->values();
        }

        if ($mode === 'semester') {
            $labels = [
                1 => 'Semester 1',
                2 => 'Semester 2',
            ];

            $rows = Realisasi::query()
                ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
                ->where('periodes.tahun', $year)
                ->selectRaw('CASE
                    WHEN periodes.bulan BETWEEN 1 AND 6 THEN 1
                    ELSE 2
                END as periode_key, COALESCE(SUM(realisasis.realisasi_biaya),0) as total')
                ->groupBy('periode_key')
                ->pluck('total', 'periode_key');

            return collect($labels)
                ->when($value, fn ($collection) => $collection->only([$value]))
                ->map(fn ($label, $key) => [
                    'label' => $label,
                    'total' => (float) ($rows[$key] ?? 0),
                ])
                ->values();
        }

        $labels = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $rows = Realisasi::query()
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->where('periodes.tahun', $year)
            ->selectRaw('periodes.bulan as periode_key, COALESCE(SUM(realisasis.realisasi_biaya),0) as total')
            ->groupBy('periodes.bulan')
            ->pluck('total', 'periode_key');

        return collect($labels)
            ->when($value, fn ($collection) => $collection->only([$value]))
            ->map(fn ($label, $key) => [
                'label' => $label,
                'total' => (float) ($rows[$key] ?? 0),
            ])
            ->values();
    }
}