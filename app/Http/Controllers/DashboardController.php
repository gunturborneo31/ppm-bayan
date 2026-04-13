<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Pilar;
use App\Models\Program;
use App\Models\Realisasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $selectedUserId = request('user_id');
        $ownedByUserId = $user->isSuperadmin() ? null : (int) $user->id;
        $selectedDivisiId = $this->canonicalDivisiId(request('divisi_id'));
        $selectedPilarId = $this->canonicalPilarId(request('pilar_id'));
        $periodMode = in_array(request('period_mode'), ['bulan', 'triwulan', 'semester'], true)
            ? request('period_mode')
            : 'bulan';
        $periodValue = request('period_value') !== null && request('period_value') !== ''
            ? (int) request('period_value')
            : null;

        $forcedDivisiId = $user->isSuperadmin() ? null : $this->canonicalDivisiId($user->divisi_id);
        $kegiatanBaseQuery = $this->buildKegiatanQuery(
            $selectedUserId,
            $selectedDivisiId,
            $selectedPilarId,
            $forcedDivisiId,
            $ownedByUserId
        );

        $kegiatanIds = (clone $kegiatanBaseQuery)->pluck('id');
        $totalRencanaBiaya = (float) ((clone $kegiatanBaseQuery)->sum('rencana_biaya') ?? 0);
        $canonicalProgramIds = $this->canonicalProgramIdsQuery();

        $totalPagu = (float) Program::query()
            ->whereIn('programs.id', $canonicalProgramIds)
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->when($ownedByUserId, function ($q) use ($ownedByUserId) {
                return $q->whereHas('kegiatans', function ($kq) use ($ownedByUserId) {
                    $kq->whereExists(function ($sub) use ($ownedByUserId) {
                        $sub->selectRaw('1')
                            ->from('activity_logs')
                            ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                            ->where('activity_logs.subject_type', Kegiatan::class)
                            ->where('activity_logs.user_id', $ownedByUserId)
                            ->whereIn('activity_logs.action', ['create', 'submit']);
                    });
                });
            })
            ->when($selectedDivisiId || $forcedDivisiId, function ($q) use ($selectedDivisiId, $forcedDivisiId) {
                $targetDivisiId = $forcedDivisiId ?: $selectedDivisiId;
                return $q->whereHas('kegiatans', fn($kq) => $kq->where('divisi_id', $targetDivisiId));
            })
            ->when($selectedPilarId, function ($q) use ($selectedPilarId, $selectedDivisiId, $forcedDivisiId) {
                return $q->whereHas('kegiatans', function ($kq) use ($selectedPilarId, $selectedDivisiId, $forcedDivisiId) {
                    $targetDivisiId = $forcedDivisiId ?: $selectedDivisiId;
                    if ($targetDivisiId) {
                        $kq->where('divisi_id', $targetDivisiId);
                    }
                    $kq->whereHas('program', fn($pq) => $pq->where('pilar_id', $selectedPilarId));
                });
            })
            ->sum('rencana_biaya');

        $totalRealisasiBiaya = $kegiatanIds->isEmpty()
            ? 0
            : (float) Realisasi::query()
                ->whereIn('kegiatan_id', $kegiatanIds)
                ->sum('realisasi_biaya');

        $sisaAnggaran = $totalRencanaBiaya - $totalRealisasiBiaya;
        $persentaseRealisasi = $totalRencanaBiaya > 0 ? round(($totalRealisasiBiaya / $totalRencanaBiaya) * 100, 2) : 0;
        $persentaseSisa = $totalRencanaBiaya > 0 ? round(($sisaAnggaran / $totalRencanaBiaya) * 100, 2) : 0;

        $stats = [
            'summary' => [
                'total_pagu' => $ownedByUserId ? $totalRencanaBiaya : $totalPagu,
                'total_rencana_biaya' => $totalRencanaBiaya,
                'total_realisasi_biaya' => $totalRealisasiBiaya,
                'sisa_anggaran' => $sisaAnggaran,
                'persentase_realisasi' => max(0, $persentaseRealisasi),
                'persentase_sisa' => max(0, $persentaseSisa),
            ],
            'total_program' => Program::query()
                ->whereIn('programs.id', $canonicalProgramIds)
                ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
                ->when($ownedByUserId, function ($q) use ($ownedByUserId) {
                    return $q->whereHas('kegiatans', function ($kq) use ($ownedByUserId) {
                        $kq->whereExists(function ($sub) use ($ownedByUserId) {
                            $sub->selectRaw('1')
                                ->from('activity_logs')
                                ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                                ->where('activity_logs.subject_type', Kegiatan::class)
                                ->where('activity_logs.user_id', $ownedByUserId)
                                ->whereIn('activity_logs.action', ['create', 'submit']);
                        });
                    });
                })
                ->when($selectedDivisiId || $forcedDivisiId, function ($q) use ($selectedDivisiId, $forcedDivisiId) {
                    $targetDivisiId = $forcedDivisiId ?: $selectedDivisiId;
                    return $q->whereHas('kegiatans', fn($kq) => $kq->where('divisi_id', $targetDivisiId));
                })
                ->when($selectedPilarId, function ($q) use ($selectedPilarId, $selectedDivisiId, $forcedDivisiId) {
                    return $q->whereHas('kegiatans', function ($kq) use ($selectedPilarId, $selectedDivisiId, $forcedDivisiId) {
                        $targetDivisiId = $forcedDivisiId ?: $selectedDivisiId;
                        if ($targetDivisiId) {
                            $kq->where('divisi_id', $targetDivisiId);
                        }
                        $kq->whereHas('program', fn($pq) => $pq->where('pilar_id', $selectedPilarId));
                    });
                })
                ->count(),
            'total_kegiatan' => (clone $kegiatanBaseQuery)->count(),
            'kegiatan_status' => (clone $kegiatanBaseQuery)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'monthly_realisasi' => $this->buildMonthlyRealisasi($kegiatanIds),
            'quarterly_capaian' => $this->buildPeriodCapaian($kegiatanIds, $totalRencanaBiaya, $periodMode),
            'pilar_summary' => $this->buildPilarSummary($selectedUserId, $selectedDivisiId, $selectedPilarId, $forcedDivisiId, $ownedByUserId),
            'divisi_summary' => $this->buildDivisiSummary($selectedUserId, $selectedDivisiId, $selectedPilarId, $forcedDivisiId, $ownedByUserId),
            'recent_logs' => ActivityLog::with('user')
                ->when($ownedByUserId, fn($q) => $q->where('user_id', $ownedByUserId))
                ->latest()
                ->limit(10)
                ->get(),
            'filter_options' => [
                'users' => User::where('role', 'superadmin')->orWhere('role', 'divisi')->get(['id', 'name']),
                'divisis' => $this->canonicalDivisis(),
                'pilars' => $this->canonicalPilars(),
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
                                    ['value' => 1, 'label' => 'Tw 1 (Jan–Mar)'],
                                    ['value' => 2, 'label' => 'Tw 2 (Apr–Jun)'],
                                    ['value' => 3, 'label' => 'Tw 3 (Jul–Sep)'],
                                    ['value' => 4, 'label' => 'Tw 4 (Okt–Des)'],
                                ],
                                'semester' => [
                                    ['value' => 1, 'label' => 'Sem 1 (Jan–Jun)'],
                                    ['value' => 2, 'label' => 'Sem 2 (Jul–Des)'],
                                ],
                            ],
            ],
            'selected_filters' => [
                'user_id' => $ownedByUserId ?: $selectedUserId,
                'divisi_id' => $forcedDivisiId ?: $selectedDivisiId,
                'pilar_id' => $selectedPilarId,
                            'period_mode' => $periodMode,
                            'period_value' => $periodValue,
            ],
            'is_superadmin' => $user->isSuperadmin(),
            'chart_year' => Carbon::now()->year,
        ];

        return Inertia::render('Dashboard/Index', compact('stats'));
    }

    private function buildKegiatanQuery($selectedUserId = null, $selectedDivisiId = null, $selectedPilarId = null, $forcedDivisiId = null, $ownedByUserId = null): Builder
    {
        $canonicalKegiatanIds = DB::table('kegiatans')
            ->selectRaw('MIN(id) as id')
            ->groupBy('program_id', 'divisi_id', 'nama');

        return Kegiatan::query()
            ->whereIn('kegiatans.id', $canonicalKegiatanIds)
            ->when($selectedUserId, fn($q) => $q->whereHas('program', fn($q2) => $q2->where('user_id', $selectedUserId)))
            ->when($selectedDivisiId, fn($q) => $q->where('divisi_id', $selectedDivisiId))
            ->when($forcedDivisiId, fn($q) => $q->where('divisi_id', $forcedDivisiId))
            ->when($ownedByUserId, function ($q) use ($ownedByUserId) {
                return $q->whereExists(function ($sub) use ($ownedByUserId) {
                    $sub->selectRaw('1')
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                        ->where('activity_logs.subject_type', Kegiatan::class)
                        ->where('activity_logs.user_id', $ownedByUserId)
                        ->whereIn('activity_logs.action', ['create', 'submit']);
                });
            })
            ->when($selectedPilarId, fn($q) => $q->whereHas('program', fn($q2) => $q2->where('pilar_id', $selectedPilarId)));
    }

    private function canonicalProgramIdsQuery()
    {
        return DB::table('programs')
            ->selectRaw('MIN(id) as id')
            ->groupBy('nama');
    }

    private function canonicalDivisis()
    {
        $canonicalIds = DB::table('divisis')
            ->selectRaw('MIN(id) as id')
            ->groupBy('nama');

        return Divisi::query()
            ->whereIn('id', $canonicalIds)
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }

    private function canonicalPilars()
    {
        $canonicalIds = DB::table('pilars')
            ->selectRaw('MIN(id) as id')
            ->groupBy('nama');

        return Pilar::query()
            ->whereIn('id', $canonicalIds)
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }

    private function canonicalDivisiId($divisiId): ?int
    {
        if (!$divisiId) {
            return null;
        }

        $namaDivisi = Divisi::query()->whereKey($divisiId)->value('nama');

        if (!$namaDivisi) {
            return null;
        }

        return (int) Divisi::query()
            ->where('nama', $namaDivisi)
            ->min('id');
    }

    private function canonicalPilarId($pilarId): ?int
    {
        if (!$pilarId) {
            return null;
        }

        $namaPilar = Pilar::query()->whereKey($pilarId)->value('nama');

        if (!$namaPilar) {
            return null;
        }

        return (int) Pilar::query()
            ->where('nama', $namaPilar)
            ->min('id');
    }

    private function buildMonthlyRealisasi($kegiatanIds)
    {
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $year = Carbon::now()->year;
        $driver = strtolower((string) DB::connection()->getDriverName());
        $isSqlite = str_contains($driver, 'sqlite');
        $monthExpression = $isSqlite
            ? "CAST(strftime('%m', realisasis.created_at) AS INTEGER)"
            : 'MONTH(realisasis.created_at)';

        if ($kegiatanIds->isEmpty()) {
            return collect($monthLabels)->map(fn($label) => ['label' => $label, 'total' => 0])->values();
        }

        $rows = Realisasi::query()
            ->whereIn('kegiatan_id', $kegiatanIds)
            ->whereYear('created_at', $year)
            ->selectRaw("{$monthExpression} as bulan, COALESCE(SUM(realisasi_biaya), 0) as total")
            ->groupByRaw($monthExpression)
            ->pluck('total', 'bulan');

        return collect($monthLabels)->map(function ($label, $index) use ($rows) {
            $monthNumber = $index + 1;

            return [
                'label' => $label,
                'total' => (float) ($rows[$monthNumber] ?? 0),
            ];
        })->values();
    }

    private function buildQuarterlyCapaian($kegiatanIds, $totalRencanaBiaya)
    {
        return $this->buildPeriodCapaian($kegiatanIds, $totalRencanaBiaya, 'bulan');
    }

    private function buildPeriodCapaian($kegiatanIds, $totalRencanaBiaya, string $mode = 'bulan')
    {
        if ($mode === 'triwulan') {
            $groups = [
                1 => ['label' => 'Tw 1', 'from' => 1, 'to' => 3],
                2 => ['label' => 'Tw 2', 'from' => 4, 'to' => 6],
                3 => ['label' => 'Tw 3', 'from' => 7, 'to' => 9],
                4 => ['label' => 'Tw 4', 'from' => 10, 'to' => 12],
            ];
        } elseif ($mode === 'semester') {
            $groups = [
                1 => ['label' => 'Sem 1', 'from' => 1, 'to' => 6],
                2 => ['label' => 'Sem 2', 'from' => 7, 'to' => 12],
            ];
        } else {
            $groups = [
                1  => ['label' => 'Jan', 'from' => 1,  'to' => 1],
                2  => ['label' => 'Feb', 'from' => 2,  'to' => 2],
                3  => ['label' => 'Mar', 'from' => 3,  'to' => 3],
                4  => ['label' => 'Apr', 'from' => 4,  'to' => 4],
                5  => ['label' => 'Mei', 'from' => 5,  'to' => 5],
                6  => ['label' => 'Jun', 'from' => 6,  'to' => 6],
                7  => ['label' => 'Jul', 'from' => 7,  'to' => 7],
                8  => ['label' => 'Agu', 'from' => 8,  'to' => 8],
                9  => ['label' => 'Sep', 'from' => 9,  'to' => 9],
                10 => ['label' => 'Okt', 'from' => 10, 'to' => 10],
                11 => ['label' => 'Nov', 'from' => 11, 'to' => 11],
                12 => ['label' => 'Des', 'from' => 12, 'to' => 12],
            ];
        }

        if ($kegiatanIds->isEmpty()) {
            return collect($groups)->map(fn ($g) => ['label' => $g['label'], 'persentase' => 0, 'realisasi' => 0])->values();
        }

        $rows = Realisasi::query()
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->whereIn('realisasis.kegiatan_id', $kegiatanIds)
            ->whereNotNull('periodes.bulan')
            ->selectRaw('periodes.bulan, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->groupBy('periodes.bulan')
            ->pluck('total_realisasi', 'periodes.bulan');

        return collect($groups)->map(function ($g) use ($rows, $totalRencanaBiaya) {
            $realisasi = 0.0;
            for ($m = $g['from']; $m <= $g['to']; $m++) {
                $realisasi += (float) ($rows[$m] ?? 0);
            }
            $persentase = $totalRencanaBiaya > 0 ? round(($realisasi / $totalRencanaBiaya) * 100, 2) : 0;

            return [
                'label'      => $g['label'],
                'persentase' => max(0, $persentase),
                'realisasi'  => $realisasi,
            ];
        })->values();
    }

    private function buildPilarSummary($selectedUserId = null, $selectedDivisiId = null, $selectedPilarId = null, $forcedDivisiId = null, $ownedByUserId = null)
    {
        $kegiatanIds = $this->buildKegiatanQuery(
            $selectedUserId,
            $selectedDivisiId,
            $selectedPilarId,
            $forcedDivisiId,
            $ownedByUserId
        )
            ->pluck('id');

        $canonicalPilarIds = DB::table('pilars')
            ->selectRaw('MIN(id) as id')
            ->groupBy('nama');

        $pilars = Pilar::query()
            ->whereIn('id', $canonicalPilarIds)
            ->when($selectedPilarId, fn($q) => $q->where('id', $selectedPilarId))
            ->orderBy('nama')
            ->get(['id', 'nama', 'warna', 'icon']);

        if ($kegiatanIds->isEmpty()) {
            return $pilars->map(function ($p) {
                $warna = is_string($p->warna) && preg_match('/^#[0-9A-Fa-f]{6}$/', $p->warna)
                    ? strtoupper($p->warna)
                    : strtoupper(Pilar::colorForName($p->nama));
                $icon = trim((string) ($p->icon ?? ''));

                return [
                'id' => $p->id,
                'nama' => $p->nama,
                'icon' => $icon !== '' ? $icon : Pilar::iconForName($p->nama),
                'color' => $warna,
                'total_anggaran' => 0,
                'total_realisasi' => 0,
                'sisa' => 0,
                'persentase' => 0,
                ];
            })->values();
        }

        $anggaranByPilar = Kegiatan::query()
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->whereNotNull('programs.pilar_id')
            ->groupBy('programs.pilar_id')
            ->selectRaw('programs.pilar_id, COALESCE(SUM(kegiatans.rencana_biaya), 0) as total_anggaran')
            ->pluck('total_anggaran', 'programs.pilar_id');

        $realisasiByPilar = Realisasi::query()
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->whereNotNull('programs.pilar_id')
            ->groupBy('programs.pilar_id')
            ->selectRaw('programs.pilar_id, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->pluck('total_realisasi', 'programs.pilar_id');

        return $pilars->map(function ($p) use ($anggaranByPilar, $realisasiByPilar) {
            $anggaran = (float) ($anggaranByPilar[$p->id] ?? 0);
            $realisasi = (float) ($realisasiByPilar[$p->id] ?? 0);
            $persentase = $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0;
            $warna = is_string($p->warna) && preg_match('/^#[0-9A-Fa-f]{6}$/', $p->warna)
                ? strtoupper($p->warna)
                : strtoupper(Pilar::colorForName($p->nama));
            $icon = trim((string) ($p->icon ?? ''));

            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'icon' => $icon !== '' ? $icon : Pilar::iconForName($p->nama),
                'color' => $warna,
                'total_anggaran' => $anggaran,
                'total_realisasi' => $realisasi,
                'sisa' => $anggaran - $realisasi,
                'persentase' => $persentase,
            ];
        })->sortByDesc('total_anggaran')->values();
    }

    private function buildDivisiSummary($selectedUserId = null, $selectedDivisiId = null, $selectedPilarId = null, $forcedDivisiId = null, $ownedByUserId = null)
    {
        $canonicalDivisiIds = DB::table('divisis')
            ->selectRaw('MIN(id) as id')
            ->groupBy('nama');

        $divisis = Divisi::query()
            ->whereIn('id', $canonicalDivisiIds)
            ->when($selectedDivisiId, fn($q) => $q->where('id', $selectedDivisiId))
            ->when($forcedDivisiId, fn($q) => $q->where('id', $forcedDivisiId))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return $divisis->map(function ($divisi) use ($selectedUserId, $selectedPilarId, $ownedByUserId) {
            $kegiatanQuery = Kegiatan::query()
                ->where('divisi_id', $divisi->id)
                ->when($selectedUserId, fn($q) => $q->whereHas('program', fn($q2) => $q2->where('user_id', $selectedUserId)))
                ->when($ownedByUserId, function ($q) use ($ownedByUserId) {
                    return $q->whereExists(function ($sub) use ($ownedByUserId) {
                        $sub->selectRaw('1')
                            ->from('activity_logs')
                            ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                            ->where('activity_logs.subject_type', Kegiatan::class)
                            ->where('activity_logs.user_id', $ownedByUserId)
                            ->whereIn('activity_logs.action', ['create', 'submit']);
                    });
                })
                ->when($selectedPilarId, fn($q) => $q->whereHas('program', fn($q2) => $q2->where('pilar_id', $selectedPilarId)));

            $kegiatanIds = (clone $kegiatanQuery)->pluck('id');
            $totalAnggaran = (float) ((clone $kegiatanQuery)->sum('rencana_biaya') ?? 0);
            $totalRealisasi = $kegiatanIds->isEmpty()
                ? 0
                : (float) Realisasi::query()->whereIn('kegiatan_id', $kegiatanIds)->sum('realisasi_biaya');
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            $jumlahProgram = Program::query()
                ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
                ->whereHas('kegiatans', function ($q) use ($divisi, $selectedPilarId, $ownedByUserId) {
                    $q->where('divisi_id', $divisi->id)
                        ->when($ownedByUserId, function ($q2) use ($ownedByUserId) {
                            $q2->whereExists(function ($sub) use ($ownedByUserId) {
                                $sub->selectRaw('1')
                                    ->from('activity_logs')
                                    ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                                    ->where('activity_logs.subject_type', Kegiatan::class)
                                    ->where('activity_logs.user_id', $ownedByUserId)
                                    ->whereIn('activity_logs.action', ['create', 'submit']);
                            });
                        })
                        ->when($selectedPilarId, fn($q2) => $q2->whereHas('program', fn($q3) => $q3->where('pilar_id', $selectedPilarId)));
                })
                ->count();

            return [
                'id' => $divisi->id,
                'nama' => $divisi->nama,
                'jumlah_program' => $jumlahProgram,
                'jumlah_kegiatan' => (clone $kegiatanQuery)->count(),
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'persentase_serapan' => $persentaseSerapan,
            ];
        })->values();
    }
}
