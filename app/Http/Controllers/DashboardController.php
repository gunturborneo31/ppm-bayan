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
        $selectedDivisiId = $this->canonicalDivisiId(request('divisi_id'));
        $selectedPilarId = $this->canonicalPilarId(request('pilar_id'));

        $forcedDivisiId = $user->isSuperadmin() ? null : $this->canonicalDivisiId($user->divisi_id);
        $kegiatanBaseQuery = $this->buildKegiatanQuery(
            $selectedUserId,
            $selectedDivisiId,
            $selectedPilarId,
            $forcedDivisiId
        );

        $kegiatanIds = (clone $kegiatanBaseQuery)->pluck('id');
        $totalRencanaBiaya = (float) ((clone $kegiatanBaseQuery)->sum('rencana_biaya') ?? 0);
        $canonicalProgramIds = $this->canonicalProgramIdsQuery();

        $totalPagu = (float) Program::query()
            ->whereIn('programs.id', $canonicalProgramIds)
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
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
                    $kq->whereHas('pilars', fn($pq) => $pq->where('pilars.id', $selectedPilarId));
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
                'total_pagu' => $totalPagu,
                'total_rencana_biaya' => $totalRencanaBiaya,
                'total_realisasi_biaya' => $totalRealisasiBiaya,
                'sisa_anggaran' => $sisaAnggaran,
                'persentase_realisasi' => max(0, $persentaseRealisasi),
                'persentase_sisa' => max(0, $persentaseSisa),
            ],
            'total_program' => Program::query()
                ->whereIn('programs.id', $canonicalProgramIds)
                ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
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
                        $kq->whereHas('pilars', fn($pq) => $pq->where('pilars.id', $selectedPilarId));
                    });
                })
                ->count(),
            'total_kegiatan' => (clone $kegiatanBaseQuery)->count(),
            'kegiatan_status' => (clone $kegiatanBaseQuery)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'monthly_realisasi' => $this->buildMonthlyRealisasi($kegiatanIds),
            'quarterly_capaian' => $this->buildQuarterlyCapaian($kegiatanIds, $totalRencanaBiaya),
            'pilar_summary' => $this->buildPilarSummary($selectedUserId, $selectedDivisiId, $selectedPilarId, $forcedDivisiId),
            'divisi_summary' => $this->buildDivisiSummary($selectedUserId, $selectedDivisiId, $selectedPilarId, $forcedDivisiId),
            'recent_logs' => ActivityLog::with('user')->latest()->limit(10)->get(),
            'filter_options' => [
                'users' => User::where('role', 'superadmin')->orWhere('role', 'divisi')->get(['id', 'name']),
                'divisis' => $this->canonicalDivisis(),
                'pilars' => $this->canonicalPilars(),
            ],
            'selected_filters' => [
                'user_id' => $selectedUserId,
                'divisi_id' => $forcedDivisiId ?: $selectedDivisiId,
                'pilar_id' => $selectedPilarId,
            ],
            'is_superadmin' => $user->isSuperadmin(),
            'chart_year' => Carbon::now()->year,
        ];

        return Inertia::render('Dashboard/Index', compact('stats'));
    }

    private function buildKegiatanQuery($selectedUserId = null, $selectedDivisiId = null, $selectedPilarId = null, $forcedDivisiId = null): Builder
    {
        $canonicalKegiatanIds = DB::table('kegiatans')
            ->selectRaw('MIN(id) as id')
            ->groupBy('program_id', 'divisi_id', 'nama');

        return Kegiatan::query()
            ->whereIn('kegiatans.id', $canonicalKegiatanIds)
            ->when($selectedUserId, fn($q) => $q->whereHas('program', fn($q2) => $q2->where('user_id', $selectedUserId)))
            ->when($selectedDivisiId, fn($q) => $q->where('divisi_id', $selectedDivisiId))
            ->when($forcedDivisiId, fn($q) => $q->where('divisi_id', $forcedDivisiId))
            ->when($selectedPilarId, fn($q) => $q->whereHas('pilars', fn($q2) => $q2->where('pilars.id', $selectedPilarId)));
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
        $labels = ['Tw 1', 'Tw 2', 'Tw 3', 'Tw 4'];

        if ($kegiatanIds->isEmpty()) {
            return collect($labels)->map(fn($label) => ['label' => $label, 'persentase' => 0, 'realisasi' => 0])->values();
        }

        $rows = Realisasi::query()
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->whereIn('realisasis.kegiatan_id', $kegiatanIds)
            ->selectRaw('periodes.triwulan, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->groupBy('periodes.triwulan')
            ->pluck('total_realisasi', 'periodes.triwulan');

        return collect($labels)->map(function ($label) use ($rows, $totalRencanaBiaya) {
            $realisasi = (float) ($rows[$label] ?? 0);
            $persentase = $totalRencanaBiaya > 0 ? round(($realisasi / $totalRencanaBiaya) * 100, 2) : 0;

            return [
                'label' => $label,
                'persentase' => max(0, $persentase),
                'realisasi' => $realisasi,
            ];
        })->values();
    }

    private function buildPilarSummary($selectedUserId = null, $selectedDivisiId = null, $selectedPilarId = null, $forcedDivisiId = null)
    {
        $kegiatanIds = $this->buildKegiatanQuery(
            $selectedUserId,
            $selectedDivisiId,
            $selectedPilarId,
            $forcedDivisiId
        )
            ->pluck('id');

        $canonicalPilarIds = DB::table('pilars')
            ->selectRaw('MIN(id) as id')
            ->groupBy('nama');

        $pilars = Pilar::query()
            ->whereIn('id', $canonicalPilarIds)
            ->when($selectedPilarId, fn($q) => $q->where('id', $selectedPilarId))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        if ($kegiatanIds->isEmpty()) {
            return $pilars->map(function ($p, $index) {
                $icons = ['AP', 'EK', 'SD', 'IN', 'KM', 'KR'];

                return [
                'id' => $p->id,
                'nama' => $p->nama,
                'icon' => $icons[$index % count($icons)],
                'total_anggaran' => 0,
                'total_realisasi' => 0,
                'sisa' => 0,
                'persentase' => 0,
                ];
            })->values();
        }

        $anggaranByPilar = Kegiatan::query()
            ->join('kegiatan_pilar', 'kegiatans.id', '=', 'kegiatan_pilar.kegiatan_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->groupBy('kegiatan_pilar.pilar_id')
            ->selectRaw('kegiatan_pilar.pilar_id, COALESCE(SUM(kegiatans.rencana_biaya), 0) as total_anggaran')
            ->pluck('total_anggaran', 'kegiatan_pilar.pilar_id');

        $realisasiByPilar = Realisasi::query()
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('kegiatan_pilar', 'kegiatans.id', '=', 'kegiatan_pilar.kegiatan_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->groupBy('kegiatan_pilar.pilar_id')
            ->selectRaw('kegiatan_pilar.pilar_id, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->pluck('total_realisasi', 'kegiatan_pilar.pilar_id');

        return $pilars->map(function ($p, $index) use ($anggaranByPilar, $realisasiByPilar) {
            $anggaran = (float) ($anggaranByPilar[$p->id] ?? 0);
            $realisasi = (float) ($realisasiByPilar[$p->id] ?? 0);
            $persentase = $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0;
            $icons = ['AP', 'EK', 'SD', 'IN', 'KM', 'KR'];

            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'icon' => $icons[$index % count($icons)],
                'total_anggaran' => $anggaran,
                'total_realisasi' => $realisasi,
                'sisa' => $anggaran - $realisasi,
                'persentase' => $persentase,
            ];
        })->sortByDesc('total_anggaran')->values();
    }

    private function buildDivisiSummary($selectedUserId = null, $selectedDivisiId = null, $selectedPilarId = null, $forcedDivisiId = null)
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

        return $divisis->map(function ($divisi) use ($selectedUserId, $selectedPilarId) {
            $kegiatanQuery = Kegiatan::query()
                ->where('divisi_id', $divisi->id)
                ->when($selectedUserId, fn($q) => $q->whereHas('program', fn($q2) => $q2->where('user_id', $selectedUserId)))
                ->when($selectedPilarId, fn($q) => $q->whereHas('pilars', fn($q2) => $q2->where('pilars.id', $selectedPilarId)));

            $kegiatanIds = (clone $kegiatanQuery)->pluck('id');
            $totalAnggaran = (float) ((clone $kegiatanQuery)->sum('rencana_biaya') ?? 0);
            $totalRealisasi = $kegiatanIds->isEmpty()
                ? 0
                : (float) Realisasi::query()->whereIn('kegiatan_id', $kegiatanIds)->sum('realisasi_biaya');
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            $jumlahProgram = Program::query()
                ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
                ->whereHas('kegiatans', function ($q) use ($divisi, $selectedPilarId) {
                    $q->where('divisi_id', $divisi->id)
                        ->when($selectedPilarId, fn($q2) => $q2->whereHas('pilars', fn($q3) => $q3->where('pilars.id', $selectedPilarId)));
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
