<?php

namespace App\Http\Controllers;

use App\Exports\ResumeExport;
use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\Pilar;
use App\Models\Program;
use App\Models\Realisasi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ResumeController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Resume/Index', [
            'resume' => $this->buildResumePayload($request, false, 'pilar'),
        ]);
    }

    public function pilar(Request $request)
    {
        return Inertia::render('Resume/Index', [
            'resume' => $this->buildResumePayload($request, false, 'pilar'),
        ]);
    }

    public function program(Request $request)
    {
        return Inertia::render('Resume/Index', [
            'resume' => $this->buildResumePayload($request, false, 'program'),
        ]);
    }

    public function divisi(Request $request)
    {
        return Inertia::render('Resume/Index', [
            'resume' => $this->buildResumePayload($request, false, 'divisi'),
        ]);
    }

    public function user(Request $request)
    {
        return Inertia::render('Resume/Index', [
            'resume' => $this->buildResumePayload($request, false, 'user'),
        ]);
    }

    public function export(Request $request, ?string $type = null)
    {
        $resumeType = in_array((string) $type, ['pilar', 'program', 'divisi', 'user'], true) ? (string) $type : 'pilar';
        $payload = $this->buildResumePayload($request, true, $resumeType);
        $format = $request->string('format')->lower()->value() ?: 'excel';
        $filename = 'resume-' . $resumeType . '-ppm-bayan-' . now()->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.resume', [
                'summary' => $payload['summary'],
                'pilarSummary' => $payload['pilar_summary'],
                'programSummary' => $payload['program_summary'],
                'divisiSummary' => $payload['divisi_summary'],
                'userSummary' => $payload['user_summary'],
                'detailRows' => $payload['detail_rows'],
                'selectedFilters' => $payload['selected_filters'],
                'filterSummary' => $payload['filter_summary'],
                'resumeType' => $payload['resume_type'],
            ])->setPaper('a4', 'landscape');

            return $pdf->download($filename . '.pdf');
        }

        return Excel::download(
            new ResumeExport($payload),
            $filename . '.xlsx'
        );
    }

    private function buildResumePayload(Request $request, bool $forExport = false, string $resumeType = 'pilar'): array
    {
        /** @var User $user */
        $user = Auth::user();
        $resumeType = in_array($resumeType, ['pilar', 'program', 'divisi', 'user'], true) ? $resumeType : 'pilar';

        $selectedCategoryId = $request->input('kategori_id');
        $selectedProgramId = $resumeType === 'program' ? $selectedCategoryId : $request->input('program_id');
        $selectedUserId = $resumeType === 'user' ? $selectedCategoryId : $request->input('user_id');
        $selectedDivisiId = $resumeType === 'divisi' ? $selectedCategoryId : $request->input('divisi_id');
        $selectedPilarId = $resumeType === 'pilar' ? $selectedCategoryId : $request->input('pilar_id');
        $selectedStatus = $request->input('status');
        $selectedTahun = $request->input('tahun');
        $selectedTriwulan = $request->input('triwulan');
        $search = trim((string) $request->input('q', ''));
        $sortBy = $request->input('sort_by', 'program');
        $sortDirection = $request->input('sort_direction', 'asc');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(10, min(100, (int) $request->input('per_page', 15)));
        $forcedDivisiId = $user->isSuperadmin() ? null : $user->divisi_id;

        $kegiatanQuery = $this->buildFilteredKegiatanQuery(
            $selectedUserId,
            $selectedProgramId,
            $selectedDivisiId,
            $selectedPilarId,
            $selectedStatus,
            $selectedTahun,
            $selectedTriwulan,
            $search,
            $forcedDivisiId
        );

        $kegiatanIds = (clone $kegiatanQuery)->pluck('kegiatans.id');
        $programIds = (clone $kegiatanQuery)->pluck('kegiatans.program_id')->unique()->values();
        $filteredRealisasiQuery = $this->buildFilteredRealisasiQuery($kegiatanIds, $selectedTahun, $selectedTriwulan);

        $totalAnggaran = (float) ((clone $kegiatanQuery)->sum('kegiatans.rencana_biaya') ?? 0);
        $totalPagu = $programIds->isEmpty()
            ? 0
            : (float) Program::query()->whereIn('id', $programIds)->sum('rencana_biaya');
        $totalRealisasi = $kegiatanIds->isEmpty()
            ? 0
            : (float) (clone $filteredRealisasiQuery)->sum('realisasi_biaya');
        $sisaAnggaran = $totalAnggaran - $totalRealisasi;
        $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

        $detailRows = $this->buildDetailRows(
            $kegiatanQuery,
            $selectedTahun,
            $selectedTriwulan,
            $forExport,
            $perPage,
            $page,
            $sortBy,
            $sortDirection
        );

        return [
            'summary' => [
                'total_pagu' => $totalPagu,
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $sisaAnggaran,
                'persentase_serapan' => max(0, $persentaseSerapan),
                'total_program' => $programIds->count(),
                'total_kegiatan' => $kegiatanIds->count(),
                'total_user' => Program::query()
                    ->whereIn('id', $programIds)
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count('user_id'),
                'status_breakdown' => (clone $kegiatanQuery)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status'),
            ],
            'program_summary' => $this->buildProgramSummary($kegiatanIds, $selectedProgramId, $selectedTahun, $selectedTriwulan),
            'pilar_summary' => $this->buildPilarSummary($kegiatanIds, $selectedPilarId, $selectedTahun, $selectedTriwulan),
            'divisi_summary' => $this->buildDivisiSummary($kegiatanIds, $selectedDivisiId, $forcedDivisiId, $selectedTahun, $selectedTriwulan),
            'user_summary' => $this->buildUserSummary($kegiatanIds, $selectedUserId, $selectedTahun, $selectedTriwulan),
            'summary_drilldown' => $this->buildSummaryDrilldown($kegiatanIds, $resumeType, $selectedTahun, $selectedTriwulan),
            'detail_rows' => $detailRows,
            'filter_options' => [
                'users' => User::query()
                    ->whereIn('role', ['superadmin', 'divisi', 'cdo'])
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'divisis' => Divisi::query()->orderBy('nama')->get(['id', 'nama']),
                'pilars' => Pilar::query()->orderBy('nama')->get(['id', 'nama']),
                'programs' => Program::query()->orderBy('nama')->get(['id', 'nama']),
                'years' => Periode::query()->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun')->values(),
                'triwulans' => [
                    ['value' => 'Tw 1', 'label' => 'Tw 1'],
                    ['value' => 'Tw 2', 'label' => 'Tw 2'],
                    ['value' => 'Tw 3', 'label' => 'Tw 3'],
                    ['value' => 'Tw 4', 'label' => 'Tw 4'],
                ],
                'per_page_options' => [15, 25, 50, 100],
                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'diajukan', 'label' => 'Diajukan'],
                    ['value' => 'revisi', 'label' => 'Revisi'],
                    ['value' => 'disetujui', 'label' => 'Disetujui'],
                    ['value' => 'ditolak', 'label' => 'Ditolak'],
                    ['value' => 'selesai', 'label' => 'Selesai'],
                ],
                'categories' => match ($resumeType) {
                    'program' => Program::query()->orderBy('nama')->get(['id', 'nama'])->map(fn (Program $program) => [
                        'id' => $program->id,
                        'nama' => $program->nama,
                    ])->values()->all(),
                    'divisi' => Divisi::query()->orderBy('nama')->get(['id', 'nama'])->map(fn (Divisi $divisi) => [
                        'id' => $divisi->id,
                        'nama' => $divisi->nama,
                    ])->values()->all(),
                    'user' => User::query()
                        ->whereIn('role', ['superadmin', 'divisi', 'cdo'])
                        ->orderBy('name')
                        ->get(['id', 'name'])
                        ->map(fn (User $item) => [
                            'id' => $item->id,
                            'nama' => $item->name,
                        ])->values()->all(),
                    default => Pilar::query()->orderBy('nama')->get(['id', 'nama'])->map(fn (Pilar $pilar) => [
                        'id' => $pilar->id,
                        'nama' => $pilar->nama,
                    ])->values()->all(),
                },
            ],
            'selected_filters' => [
                'resume_type' => $resumeType,
                'kategori_id' => $selectedCategoryId,
                'user_id' => $selectedUserId,
                'program_id' => $selectedProgramId,
                'divisi_id' => $forcedDivisiId ?: $selectedDivisiId,
                'pilar_id' => $selectedPilarId,
                'status' => $selectedStatus,
                'tahun' => $selectedTahun,
                'triwulan' => $selectedTriwulan,
                'q' => $search,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
            'filter_summary' => $this->buildFilterSummary(
                $resumeType,
                $selectedCategoryId,
                $selectedUserId,
                $forcedDivisiId ?: $selectedDivisiId,
                $selectedPilarId,
                $selectedStatus,
                $selectedTahun,
                $selectedTriwulan,
                $search
            ),
            'is_superadmin' => $user->isSuperadmin(),
            'resume_type' => $resumeType,
            'category_label' => match ($resumeType) {
                'program' => 'Program',
                'divisi' => 'Divisi',
                'user' => 'User',
                default => 'Pilar',
            },
        ];
    }

    private function buildSummaryDrilldown(Collection $kegiatanIds, string $resumeType, ?string $selectedTahun, ?string $selectedTriwulan): array
    {
        if ($kegiatanIds->isEmpty()) {
            return [];
        }

        $kegiatans = Kegiatan::query()
            ->whereIn('id', $kegiatanIds)
            ->with([
                'program:id,nama,user_id',
                'pilars:id,nama',
            ])
            ->withSum([
                'realisasis as total_realisasi_biaya' => function (Builder $query) use ($selectedTahun, $selectedTriwulan) {
                    $query->when($selectedTahun || $selectedTriwulan, function (Builder $nested) use ($selectedTahun, $selectedTriwulan) {
                        return $nested->whereHas('periode', function (Builder $periodeQuery) use ($selectedTahun, $selectedTriwulan) {
                            $periodeQuery
                                ->when($selectedTahun, fn (Builder $periodeNested) => $periodeNested->where('tahun', $selectedTahun))
                                ->when($selectedTriwulan, fn (Builder $periodeNested) => $periodeNested->where('triwulan', $selectedTriwulan));
                        });
                    });
                },
            ], 'realisasi_biaya')
            ->orderBy('program_id')
            ->orderBy('nama')
            ->get(['id', 'nama', 'program_id', 'divisi_id', 'rencana_biaya']);

        $drilldown = [];

        foreach ($kegiatans as $kegiatan) {
            $categoryIds = match ($resumeType) {
                'program' => [$kegiatan->program_id],
                'divisi' => [$kegiatan->divisi_id],
                'user' => [$kegiatan->program?->user_id],
                default => $kegiatan->pilars->pluck('id')->all(),
            };

            $anggaran = (float) ($kegiatan->rencana_biaya ?? 0);
            $realisasi = (float) ($kegiatan->total_realisasi_biaya ?? 0);
            $persentaseSerapan = $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0;

            foreach ($categoryIds as $categoryId) {
                if (!$categoryId) {
                    continue;
                }

                if (!isset($drilldown[$categoryId])) {
                    $drilldown[$categoryId] = [];
                }

                if (!isset($drilldown[$categoryId][$kegiatan->program_id])) {
                    $drilldown[$categoryId][$kegiatan->program_id] = [
                        'id' => $kegiatan->program_id,
                        'nama' => $kegiatan->program?->nama ?? '-',
                        'kegiatans' => [],
                    ];
                }

                $drilldown[$categoryId][$kegiatan->program_id]['kegiatans'][] = [
                    'id' => $kegiatan->id,
                    'nama' => $kegiatan->nama,
                    'anggaran' => $anggaran,
                    'realisasi' => $realisasi,
                    'sisa_anggaran' => $anggaran - $realisasi,
                    'persentase_serapan' => $persentaseSerapan,
                ];
            }
        }

        return collect($drilldown)
            ->map(function ($programGroups) {
                return collect($programGroups)
                    ->values()
                    ->map(function ($program) {
                        $kegiatans = collect($program['kegiatans'])->values();

                        return [
                            'id' => $program['id'],
                            'nama' => $program['nama'],
                            'jumlah_kegiatan' => $kegiatans->count(),
                            'kegiatans' => $kegiatans->all(),
                        ];
                    })
                    ->values()
                    ->all();
            })
            ->toArray();
    }

    private function buildFilteredKegiatanQuery(
        ?string $selectedUserId,
        ?string $selectedProgramId,
        ?string $selectedDivisiId,
        ?string $selectedPilarId,
        ?string $selectedStatus,
        ?string $selectedTahun,
        ?string $selectedTriwulan,
        string $search,
        ?int $forcedDivisiId
    ): Builder {
        return Kegiatan::query()
            ->when($selectedUserId, fn (Builder $query) => $query->whereHas('program', fn (Builder $programQuery) => $programQuery->where('user_id', $selectedUserId)))
            ->when($selectedProgramId, fn (Builder $query) => $query->where('program_id', $selectedProgramId))
            ->when($selectedDivisiId || $forcedDivisiId, function (Builder $query) use ($selectedDivisiId, $forcedDivisiId) {
                $targetDivisiId = $forcedDivisiId ?: $selectedDivisiId;

                return $query->where('divisi_id', $targetDivisiId);
            })
            ->when($selectedPilarId, fn (Builder $query) => $query->whereHas('pilars', fn (Builder $pilarQuery) => $pilarQuery->where('pilars.id', $selectedPilarId)))
            ->when($selectedStatus, fn (Builder $query) => $query->where('status', $selectedStatus))
            ->when($selectedTahun || $selectedTriwulan, function (Builder $query) use ($selectedTahun, $selectedTriwulan) {
                return $query->whereHas('realisasis.periode', function (Builder $periodeQuery) use ($selectedTahun, $selectedTriwulan) {
                    $periodeQuery
                        ->when($selectedTahun, fn (Builder $nested) => $nested->where('tahun', $selectedTahun))
                        ->when($selectedTriwulan, fn (Builder $nested) => $nested->where('triwulan', $selectedTriwulan));
                });
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $keyword = '%' . str_replace(' ', '%', $search) . '%';

                $query->where(function (Builder $nested) use ($keyword) {
                    $nested->where('kegiatans.nama', 'like', $keyword)
                        ->orWhere('kegiatans.deskripsi', 'like', $keyword)
                        ->orWhereHas('program', fn (Builder $programQuery) => $programQuery
                            ->where('nama', 'like', $keyword)
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $keyword)))
                        ->orWhereHas('divisi', fn (Builder $divisiQuery) => $divisiQuery->where('nama', 'like', $keyword))
                        ->orWhereHas('pilars', fn (Builder $pilarQuery) => $pilarQuery->where('nama', 'like', $keyword));
                });
            });
    }

    private function buildProgramSummary(Collection $kegiatanIds, ?string $selectedProgramId, ?string $selectedTahun, ?string $selectedTriwulan): array
    {
        $programs = Program::query()
            ->when($selectedProgramId, fn (Builder $query) => $query->where('id', $selectedProgramId))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        if ($programs->isEmpty()) {
            return [];
        }

        if ($kegiatanIds->isEmpty()) {
            return $programs->map(fn (Program $program) => [
                'id' => $program->id,
                'nama' => $program->nama,
                'jumlah_kegiatan' => 0,
                'total_anggaran' => 0,
                'total_realisasi' => 0,
                'sisa_anggaran' => 0,
                'persentase_serapan' => 0,
            ])->values()->all();
        }

        $anggaranRows = Kegiatan::query()
            ->whereIn('id', $kegiatanIds)
            ->groupBy('program_id')
            ->selectRaw('program_id, COUNT(*) as jumlah_kegiatan, COALESCE(SUM(rencana_biaya), 0) as total_anggaran')
            ->get()
            ->keyBy('program_id');

        $realisasiRows = Realisasi::query()
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->when($selectedTahun, fn (Builder $query) => $query->where('periodes.tahun', $selectedTahun))
            ->when($selectedTriwulan, fn (Builder $query) => $query->where('periodes.triwulan', $selectedTriwulan))
            ->groupBy('kegiatans.program_id')
            ->selectRaw('kegiatans.program_id, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->pluck('total_realisasi', 'kegiatans.program_id');

        return $programs->map(function (Program $program) use ($anggaranRows, $realisasiRows) {
            $anggaranRow = $anggaranRows->get($program->id);
            $totalAnggaran = (float) ($anggaranRow->total_anggaran ?? 0);
            $totalRealisasi = (float) ($realisasiRows[$program->id] ?? 0);
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            return [
                'id' => $program->id,
                'nama' => $program->nama,
                'jumlah_kegiatan' => (int) ($anggaranRow->jumlah_kegiatan ?? 0),
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
                'persentase_serapan' => $persentaseSerapan,
            ];
        })->values()->all();
    }

    private function buildFilteredRealisasiQuery(Collection $kegiatanIds, ?string $selectedTahun, ?string $selectedTriwulan): Builder
    {
        return Realisasi::query()
            ->whereIn('kegiatan_id', $kegiatanIds)
            ->when($selectedTahun || $selectedTriwulan, function (Builder $query) use ($selectedTahun, $selectedTriwulan) {
                return $query->whereHas('periode', function (Builder $periodeQuery) use ($selectedTahun, $selectedTriwulan) {
                    $periodeQuery
                        ->when($selectedTahun, fn (Builder $nested) => $nested->where('tahun', $selectedTahun))
                        ->when($selectedTriwulan, fn (Builder $nested) => $nested->where('triwulan', $selectedTriwulan));
                });
            });
    }

    private function buildPilarSummary(Collection $kegiatanIds, ?string $selectedPilarId, ?string $selectedTahun, ?string $selectedTriwulan): array
    {
        $pilars = Pilar::query()
            ->when($selectedPilarId, fn (Builder $query) => $query->where('id', $selectedPilarId))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        if ($pilars->isEmpty()) {
            return [];
        }

        if ($kegiatanIds->isEmpty()) {
            return $pilars->map(fn (Pilar $pilar) => [
                'id' => $pilar->id,
                'nama' => $pilar->nama,
                'jumlah_program' => 0,
                'jumlah_kegiatan' => 0,
                'total_anggaran' => 0,
                'total_realisasi' => 0,
                'sisa_anggaran' => 0,
                'persentase_serapan' => 0,
            ])->values()->all();
        }

        $anggaranRows = Kegiatan::query()
            ->join('kegiatan_pilar', 'kegiatans.id', '=', 'kegiatan_pilar.kegiatan_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->groupBy('kegiatan_pilar.pilar_id')
            ->selectRaw('kegiatan_pilar.pilar_id, COUNT(DISTINCT kegiatans.program_id) as jumlah_program, COUNT(DISTINCT kegiatans.id) as jumlah_kegiatan, COALESCE(SUM(kegiatans.rencana_biaya), 0) as total_anggaran')
            ->get()
            ->keyBy('pilar_id');

        $realisasiRows = Realisasi::query()
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->join('kegiatan_pilar', 'kegiatans.id', '=', 'kegiatan_pilar.kegiatan_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->when($selectedTahun, fn (Builder $query) => $query->where('periodes.tahun', $selectedTahun))
            ->when($selectedTriwulan, fn (Builder $query) => $query->where('periodes.triwulan', $selectedTriwulan))
            ->groupBy('kegiatan_pilar.pilar_id')
            ->selectRaw('kegiatan_pilar.pilar_id, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->pluck('total_realisasi', 'kegiatan_pilar.pilar_id');

        return $pilars->map(function (Pilar $pilar) use ($anggaranRows, $realisasiRows) {
            $anggaranRow = $anggaranRows->get($pilar->id);
            $totalAnggaran = (float) ($anggaranRow->total_anggaran ?? 0);
            $totalRealisasi = (float) ($realisasiRows[$pilar->id] ?? 0);
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            return [
                'id' => $pilar->id,
                'nama' => $pilar->nama,
                'jumlah_program' => (int) ($anggaranRow->jumlah_program ?? 0),
                'jumlah_kegiatan' => (int) ($anggaranRow->jumlah_kegiatan ?? 0),
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
                'persentase_serapan' => $persentaseSerapan,
            ];
        })->sortByDesc('total_anggaran')->values()->all();
    }

    private function buildDivisiSummary(Collection $kegiatanIds, ?string $selectedDivisiId, ?int $forcedDivisiId, ?string $selectedTahun, ?string $selectedTriwulan): array
    {
        $divisis = Divisi::query()
            ->when($selectedDivisiId, fn (Builder $query) => $query->where('id', $selectedDivisiId))
            ->when($forcedDivisiId, fn (Builder $query) => $query->where('id', $forcedDivisiId))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        if ($divisis->isEmpty()) {
            return [];
        }

        if ($kegiatanIds->isEmpty()) {
            return $divisis->map(fn (Divisi $divisi) => [
                'id' => $divisi->id,
                'nama' => $divisi->nama,
                'jumlah_program' => 0,
                'jumlah_kegiatan' => 0,
                'total_anggaran' => 0,
                'total_realisasi' => 0,
                'sisa_anggaran' => 0,
                'persentase_serapan' => 0,
            ])->values()->all();
        }

        $anggaranRows = Kegiatan::query()
            ->whereIn('id', $kegiatanIds)
            ->groupBy('divisi_id')
            ->selectRaw('divisi_id, COUNT(DISTINCT program_id) as jumlah_program, COUNT(*) as jumlah_kegiatan, COALESCE(SUM(rencana_biaya), 0) as total_anggaran')
            ->get()
            ->keyBy('divisi_id');

        $realisasiRows = Realisasi::query()
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->when($selectedTahun, fn (Builder $query) => $query->where('periodes.tahun', $selectedTahun))
            ->when($selectedTriwulan, fn (Builder $query) => $query->where('periodes.triwulan', $selectedTriwulan))
            ->groupBy('kegiatans.divisi_id')
            ->selectRaw('kegiatans.divisi_id, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->pluck('total_realisasi', 'kegiatans.divisi_id');

        return $divisis->map(function (Divisi $divisi) use ($anggaranRows, $realisasiRows) {
            $anggaranRow = $anggaranRows->get($divisi->id);
            $totalAnggaran = (float) ($anggaranRow->total_anggaran ?? 0);
            $totalRealisasi = (float) ($realisasiRows[$divisi->id] ?? 0);
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            return [
                'id' => $divisi->id,
                'nama' => $divisi->nama,
                'jumlah_program' => (int) ($anggaranRow->jumlah_program ?? 0),
                'jumlah_kegiatan' => (int) ($anggaranRow->jumlah_kegiatan ?? 0),
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
                'persentase_serapan' => $persentaseSerapan,
            ];
        })->values()->all();
    }

    private function buildUserSummary(Collection $kegiatanIds, ?string $selectedUserId, ?string $selectedTahun, ?string $selectedTriwulan): array
    {
        $users = User::query()
            ->whereIn('role', ['superadmin', 'divisi', 'cdo'])
            ->when($selectedUserId, fn (Builder $query) => $query->where('id', $selectedUserId))
            ->orderBy('name')
            ->with('divisi:id,nama')
            ->get(['id', 'name', 'divisi_id']);

        if ($users->isEmpty()) {
            return [];
        }

        if ($kegiatanIds->isEmpty()) {
            return $users->map(fn (User $item) => [
                'id' => $item->id,
                'nama' => $item->name,
                'divisi' => $item->divisi?->nama ?? '-',
                'jumlah_program' => 0,
                'jumlah_kegiatan' => 0,
                'total_anggaran' => 0,
                'total_realisasi' => 0,
                'sisa_anggaran' => 0,
                'persentase_serapan' => 0,
            ])->values()->all();
        }

        $anggaranRows = Kegiatan::query()
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->whereNotNull('programs.user_id')
            ->groupBy('programs.user_id')
            ->selectRaw('programs.user_id, COUNT(DISTINCT kegiatans.program_id) as jumlah_program, COUNT(*) as jumlah_kegiatan, COALESCE(SUM(kegiatans.rencana_biaya), 0) as total_anggaran')
            ->get()
            ->keyBy('user_id');

        $realisasiRows = Realisasi::query()
            ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->join('periodes', 'periodes.id', '=', 'realisasis.periode_id')
            ->whereIn('kegiatans.id', $kegiatanIds)
            ->whereNotNull('programs.user_id')
            ->when($selectedTahun, fn (Builder $query) => $query->where('periodes.tahun', $selectedTahun))
            ->when($selectedTriwulan, fn (Builder $query) => $query->where('periodes.triwulan', $selectedTriwulan))
            ->groupBy('programs.user_id')
            ->selectRaw('programs.user_id, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
            ->pluck('total_realisasi', 'programs.user_id');

        return $users->map(function (User $item) use ($anggaranRows, $realisasiRows) {
            $anggaranRow = $anggaranRows->get($item->id);
            $totalAnggaran = (float) ($anggaranRow->total_anggaran ?? 0);
            $totalRealisasi = (float) ($realisasiRows[$item->id] ?? 0);
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            return [
                'id' => $item->id,
                'nama' => $item->name,
                'divisi' => $item->divisi?->nama ?? '-',
                'jumlah_program' => (int) ($anggaranRow->jumlah_program ?? 0),
                'jumlah_kegiatan' => (int) ($anggaranRow->jumlah_kegiatan ?? 0),
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
                'persentase_serapan' => $persentaseSerapan,
            ];
        })->values()->all();
    }

    private function buildDetailRows(
        Builder $kegiatanQuery,
        ?string $selectedTahun,
        ?string $selectedTriwulan,
        bool $forExport,
        int $perPage,
        int $page,
        string $sortBy,
        string $sortDirection
    ): array
    {
        $detailQuery = (clone $kegiatanQuery)
            ->with([
                'program:id,nama,user_id',
                'program.user:id,name',
                'divisi:id,nama',
                'pilars:id,nama',
            ])
            ->withSum([
                'realisasis as total_realisasi_biaya' => function (Builder $query) use ($selectedTahun, $selectedTriwulan) {
                    $query->when($selectedTahun || $selectedTriwulan, function (Builder $nested) use ($selectedTahun, $selectedTriwulan) {
                        return $nested->whereHas('periode', function (Builder $periodeQuery) use ($selectedTahun, $selectedTriwulan) {
                            $periodeQuery
                                ->when($selectedTahun, fn (Builder $periodeNested) => $periodeNested->where('tahun', $selectedTahun))
                                ->when($selectedTriwulan, fn (Builder $periodeNested) => $periodeNested->where('triwulan', $selectedTriwulan));
                        });
                    });
                },
            ], 'realisasi_biaya')
            ->withSum([
                'realisasis as total_realisasi_output' => function (Builder $query) use ($selectedTahun, $selectedTriwulan) {
                    $query->when($selectedTahun || $selectedTriwulan, function (Builder $nested) use ($selectedTahun, $selectedTriwulan) {
                        return $nested->whereHas('periode', function (Builder $periodeQuery) use ($selectedTahun, $selectedTriwulan) {
                            $periodeQuery
                                ->when($selectedTahun, fn (Builder $periodeNested) => $periodeNested->where('tahun', $selectedTahun))
                                ->when($selectedTriwulan, fn (Builder $periodeNested) => $periodeNested->where('triwulan', $selectedTriwulan));
                        });
                    });
                },
            ], 'realisasi_output')
            ->orderBy('divisi_id')
            ->orderBy('program_id')
            ->orderBy('nama');

        $rows = $detailQuery
            ->get()
            ->map(fn (Kegiatan $kegiatan) => $this->transformDetailRow($kegiatan));

        $sortedRows = $this->sortDetailRows($rows, $sortBy, $sortDirection)->values();

        if ($forExport) {
            return $sortedRows->all();
        }

        $offset = ($page - 1) * $perPage;
        $pageItems = $sortedRows->slice($offset, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageItems,
            $sortedRows->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return $paginator->toArray();
    }

    private function sortDetailRows(Collection $rows, string $sortBy, string $sortDirection): Collection
    {
        $allowedSorts = [
            'kode_ref',
            'program',
            'kegiatan',
            'divisi',
            'pilar',
            'user_penginput',
            'status_label',
            'anggaran',
            'realisasi',
            'sisa_anggaran',
            'persentase_serapan',
            'progress',
        ];

        $targetSort = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'program';
        $descending = strtolower($sortDirection) === 'desc';

        return $rows->sort(function (array $left, array $right) use ($targetSort, $descending) {
            $leftValue = $left[$targetSort] ?? null;
            $rightValue = $right[$targetSort] ?? null;

            $comparison = match ($targetSort) {
                'anggaran', 'realisasi', 'sisa_anggaran', 'persentase_serapan', 'progress' => (float) $leftValue <=> (float) $rightValue,
                default => strcasecmp((string) $leftValue, (string) $rightValue),
            };

            if ($comparison === 0) {
                $comparison = strcasecmp((string) ($left['kegiatan'] ?? ''), (string) ($right['kegiatan'] ?? ''));
            }

            return $descending ? -$comparison : $comparison;
        });
    }

    private function transformDetailRow(Kegiatan $kegiatan): array
    {
        $anggaran = (float) ($kegiatan->rencana_biaya ?? 0);
        $realisasi = (float) ($kegiatan->total_realisasi_biaya ?? 0);
        $progress = $kegiatan->target_output > 0
            ? round((((float) $kegiatan->total_realisasi_output) / (float) $kegiatan->target_output) * 100, 2)
            : 0;
        $persentaseSerapan = $anggaran > 0 ? round(($realisasi / $anggaran) * 100, 2) : 0;

        return [
            'id' => $kegiatan->id,
            'kode_ref' => 'KGT.' . str_pad((string) $kegiatan->id, 3, '0', STR_PAD_LEFT),
            'program' => $kegiatan->program?->nama,
            'kegiatan' => $kegiatan->nama,
            'divisi' => $kegiatan->divisi?->nama,
            'user_penginput' => $kegiatan->program?->user?->name,
            'pilar' => $kegiatan->pilars->pluck('nama')->join(', '),
            'status' => $kegiatan->status?->value ?? (string) $kegiatan->status,
            'status_label' => $kegiatan->status?->label() ?? ucfirst((string) $kegiatan->status),
            'target_output' => (float) ($kegiatan->target_output ?? 0),
            'satuan' => $kegiatan->satuan,
            'anggaran' => $anggaran,
            'realisasi' => $realisasi,
            'sisa_anggaran' => $anggaran - $realisasi,
            'persentase_serapan' => $persentaseSerapan,
            'realisasi_output' => (float) ($kegiatan->total_realisasi_output ?? 0),
            'progress' => min(100, max(0, $progress)),
        ];
    }

    private function buildFilterSummary(
        string $resumeType,
        ?string $selectedCategoryId,
        ?string $selectedUserId,
        ?string $selectedDivisiId,
        ?string $selectedPilarId,
        ?string $selectedStatus,
        ?string $selectedTahun,
        ?string $selectedTriwulan,
        string $search
    ): array {
        $kategori = match ($resumeType) {
            'program' => $selectedCategoryId ? Program::query()->whereKey($selectedCategoryId)->value('nama') : 'Semua Program',
            'divisi' => $selectedCategoryId ? Divisi::query()->whereKey($selectedCategoryId)->value('nama') : 'Semua Divisi',
            'user' => $selectedCategoryId ? User::query()->whereKey($selectedCategoryId)->value('name') : 'Semua User',
            default => $selectedCategoryId ? Pilar::query()->whereKey($selectedCategoryId)->value('nama') : 'Semua Pilar',
        };

        return [
            'kategori' => $kategori,
            'user' => $selectedUserId ? User::query()->whereKey($selectedUserId)->value('name') : 'Semua User',
            'divisi' => $selectedDivisiId ? Divisi::query()->whereKey($selectedDivisiId)->value('nama') : 'Semua Divisi',
            'pilar' => $selectedPilarId ? Pilar::query()->whereKey($selectedPilarId)->value('nama') : 'Semua Pilar',
            'status' => $selectedStatus ? ucfirst($selectedStatus) : 'Semua Status',
            'tahun' => $selectedTahun ?: 'Semua Tahun',
            'triwulan' => $selectedTriwulan ?: 'Semua Triwulan',
            'q' => $search !== '' ? $search : '-',
        ];
    }
}