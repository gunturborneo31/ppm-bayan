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
use Illuminate\Support\Facades\Schema;
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
        $filename = $this->buildExportFilename($payload);

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

    private function buildExportFilename(array $payload): string
    {
        $resumeType = (string) ($payload['resume_type'] ?? 'pilar');
        $selected = (array) ($payload['selected_filters'] ?? []);

        $typeLabel = match ($resumeType) {
            'program' => 'program',
            'divisi' => 'divisi',
            'user' => 'user',
            default => 'pilar',
        };

        $periodMode = (string) ($selected['period_mode'] ?? 'bulan');
        $periodValue = $selected['period_value'] ?? null;

        $modeLabel = match ($periodMode) {
            'triwulan' => 'triwulan',
            'semester' => 'semester',
            default => 'bulan',
        };

        $periodPart = 'berdasarkan-' . $modeLabel;

        if ($periodValue !== null && $periodValue !== '') {
            $periodPart .= '-' . $this->periodValueLabelForFilename($periodMode, (int) $periodValue);
        }

        $filename = 'export-data-' . $typeLabel . '-' . $periodPart;

        return $this->slugifyFilename($filename);
    }

    private function periodValueLabelForFilename(string $periodMode, int $periodValue): string
    {
        if ($periodMode === 'triwulan') {
            return 'triwulan-' . $periodValue;
        }

        if ($periodMode === 'semester') {
            return 'semester-' . $periodValue;
        }

        $months = [
            1 => 'januari',
            2 => 'februari',
            3 => 'maret',
            4 => 'april',
            5 => 'mei',
            6 => 'juni',
            7 => 'juli',
            8 => 'agustus',
            9 => 'september',
            10 => 'oktober',
            11 => 'november',
            12 => 'desember',
        ];

        return $months[$periodValue] ?? ('bulan-' . $periodValue);
    }

    private function slugifyFilename(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: 'export-data';
        $slug = trim($slug, '-');

        return $slug === '' ? 'export-data' : $slug;
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
        $periodMode = in_array($request->input('period_mode'), ['bulan', 'triwulan', 'semester'], true)
            ? $request->input('period_mode')
            : 'bulan';
        $periodValue = $request->input('period_value',
            $request->input('bulan', $request->input('triwulan')));
        if ($periodMode === 'bulan') {
            $periodValue = $this->normalizeBulanFilter($periodValue);
        } elseif ($periodValue !== null && $periodValue !== '') {
            $periodValue = (string) (int) $periodValue;
        } else {
            $periodValue = null;
        }
        $bulanRange = $this->parsePeriodRange($periodMode, $periodValue);
        $selectedBulan = $bulanRange ? (string) $bulanRange[0] : null;
        $search = trim((string) $request->input('q', ''));
        $sortBy = $request->input('sort_by', 'program');
        $sortDirection = $request->input('sort_direction', 'asc');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(10, min(100, (int) $request->input('per_page', 15)));
        $forcedDivisiId = ($user->isSuperadmin() || $user->isPimpinan()) ? null : $user->divisi_id;

        $kegiatanQuery = $this->buildFilteredKegiatanQuery(
            $selectedUserId,
            $selectedProgramId,
            $selectedDivisiId,
            $selectedPilarId,
            $selectedStatus,
            $selectedTahun,
            $bulanRange,
            $search,
            $forcedDivisiId
        );

        $kegiatanIds = (clone $kegiatanQuery)->pluck('kegiatans.id');
        $programIds = (clone $kegiatanQuery)->pluck('kegiatans.program_id')->unique()->values();
        $latestRealisasis = $this->latestFilteredRealisasis($kegiatanIds, $selectedTahun, $bulanRange);

        $totalAnggaran = (float) ((clone $kegiatanQuery)->sum('kegiatans.rencana_biaya') ?? 0);
        $totalPagu = $programIds->isEmpty()
            ? 0
            : (float) Program::query()->whereIn('id', $programIds)->sum('rencana_biaya');
        $totalRealisasi = (float) $latestRealisasis->sum(fn (Realisasi $row) => (float) $row->realisasi_biaya);
        $sisaAnggaran = $totalAnggaran - $totalRealisasi;
        $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

        $detailRows = $this->buildDetailRows(
            $kegiatanQuery,
            $latestRealisasis,
            $forExport,
            $perPage,
            $page,
            $sortBy,
            $sortDirection
        );

        $bulanOptions = [
            ['value' => 1, 'label' => 'Jan'],
            ['value' => 2, 'label' => 'Feb'],
            ['value' => 3, 'label' => 'Mar'],
            ['value' => 4, 'label' => 'Apr'],
            ['value' => 5, 'label' => 'Mei'],
            ['value' => 6, 'label' => 'Jun'],
            ['value' => 7, 'label' => 'Jul'],
            ['value' => 8, 'label' => 'Agu'],
            ['value' => 9, 'label' => 'Sep'],
            ['value' => 10, 'label' => 'Okt'],
            ['value' => 11, 'label' => 'Nov'],
            ['value' => 12, 'label' => 'Des'],
        ];

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
            'program_summary' => $this->buildProgramSummary($kegiatanIds, $selectedProgramId, $latestRealisasis),
            'pilar_summary' => $this->buildPilarSummary($kegiatanIds, $selectedPilarId, $latestRealisasis),
            'divisi_summary' => $this->buildDivisiSummary($kegiatanIds, $selectedDivisiId, $forcedDivisiId, $latestRealisasis),
            'user_summary' => $this->buildUserSummary($kegiatanIds, $selectedUserId, $latestRealisasis),
            'summary_drilldown' => $this->buildSummaryDrilldown($kegiatanIds, $resumeType, $latestRealisasis),
            'chart_rows' => $this->buildChartRows($resumeType, $kegiatanIds, $selectedProgramId, $selectedPilarId, $selectedDivisiId, $forcedDivisiId, $selectedUserId, $latestRealisasis),
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
                'bulans' => $bulanOptions,
                'triwulans' => $bulanOptions,
                'period_modes' => [
                    ['value' => 'bulan', 'label' => 'Bulan'],
                    ['value' => 'triwulan', 'label' => 'Triwulan'],
                    ['value' => 'semester', 'label' => 'Semester'],
                ],
                'period_options' => [
                    'bulan' => $bulanOptions,
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
                    default => Pilar::query()
                        ->when(Schema::hasColumn('pilars', 'no_urut'), fn (Builder $query) => $query->orderBy('no_urut'))
                        ->orderBy('nama')
                        ->get(['id', 'nama'])
                        ->map(fn (Pilar $pilar) => [
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
                'bulan' => $selectedBulan,
                'triwulan' => $selectedBulan,
                'period_mode' => $periodMode,
                'period_value' => $periodValue,
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
                $periodMode,
                $periodValue,
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

    private function buildSummaryDrilldown(Collection $kegiatanIds, string $resumeType, Collection $latestRealisasis): array
    {
        if ($kegiatanIds->isEmpty()) {
            return [];
        }

        $kegiatans = Kegiatan::query()
            ->whereIn('id', $kegiatanIds)
            ->with([
                'program:id,nama,user_id,pilar_id',
                'program.pilar:id,nama',
                'program.user:id,name',
                'lokasis:id,kegiatan_id,lokasi,target_output,satuan,rencana_biaya',
            ])
            ->orderBy('program_id')
            ->orderBy('nama')
            ->get(['id', 'nama', 'program_id', 'divisi_id', 'rencana_biaya']);

        [$latestByLokasiId, $latestByKegiatanFallback] = $this->latestRealisasiLookups($latestRealisasis);

        $drilldown = [];

        foreach ($kegiatans as $kegiatan) {
            $categoryIds = match ($resumeType) {
                'program' => [$kegiatan->program_id],
                'divisi' => [$kegiatan->divisi_id],
                'user' => [$kegiatan->program?->user_id],
                default => [$kegiatan->program?->pilar_id],
            };

            $lokasiRows = collect($kegiatan->lokasis)->map(function ($lokasi) use ($latestByLokasiId) {
                $anggaranLokasi = (float) ($lokasi->rencana_biaya ?? 0);
                $latest = $latestByLokasiId->get((int) $lokasi->id);
                $realisasiLokasi = (float) ($latest?->realisasi_biaya ?? 0);
                $evidenceFiles = $latest?->files?->where('kategori', 'evidence')->values() ?? collect();
                $laporanFiles = $latest?->files?->where('kategori', 'laporan')->values() ?? collect();
                $keterangan = trim((string) ($latest?->keterangan ?? ''));

                return [
                    'id' => (int) $lokasi->id,
                    'nama' => (string) ($lokasi->lokasi ?? '-'),
                    'anggaran' => $anggaranLokasi,
                    'realisasi' => $realisasiLokasi,
                    'sisa_anggaran' => $anggaranLokasi - $realisasiLokasi,
                    'persentase_serapan' => $anggaranLokasi > 0 ? round(($realisasiLokasi / $anggaranLokasi) * 100, 2) : 0,
                    'evidence_count' => $evidenceFiles->count(),
                    'laporan_count' => $laporanFiles->count(),
                    'evidence_files' => $evidenceFiles->map(fn ($file) => [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_type' => $file->file_type,
                    ])->all(),
                    'laporan_files' => $laporanFiles->map(fn ($file) => [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_type' => $file->file_type,
                    ])->all(),
                    'keterangan' => $keterangan,
                ];
            })->values();

            if ($lokasiRows->isEmpty()) {
                $fallbackAnggaran = (float) ($kegiatan->rencana_biaya ?? 0);
                $fallbackLatest = $latestByKegiatanFallback->get((int) $kegiatan->id);
                $fallbackRealisasi = (float) ($fallbackLatest?->realisasi_biaya ?? 0);
                $fallbackEvidence = $fallbackLatest?->files?->where('kategori', 'evidence')->values() ?? collect();
                $fallbackLaporan = $fallbackLatest?->files?->where('kategori', 'laporan')->values() ?? collect();
                $fallbackKeterangan = trim((string) ($fallbackLatest?->keterangan ?? ''));

                $lokasiRows = collect([[
                    'id' => null,
                    'nama' => '-',
                    'anggaran' => $fallbackAnggaran,
                    'realisasi' => $fallbackRealisasi,
                    'sisa_anggaran' => $fallbackAnggaran - $fallbackRealisasi,
                    'persentase_serapan' => $fallbackAnggaran > 0 ? round(($fallbackRealisasi / $fallbackAnggaran) * 100, 2) : 0,
                    'evidence_count' => $fallbackEvidence->count(),
                    'laporan_count' => $fallbackLaporan->count(),
                    'evidence_files' => $fallbackEvidence->map(fn ($file) => [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_type' => $file->file_type,
                    ])->all(),
                    'laporan_files' => $fallbackLaporan->map(fn ($file) => [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_type' => $file->file_type,
                    ])->all(),
                    'keterangan' => $fallbackKeterangan,
                ]]);
            }

            $anggaran = (float) $lokasiRows->sum('anggaran');
            $realisasi = (float) $lokasiRows->sum('realisasi');
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
                        'user_name' => $kegiatan->program?->user?->name ?? '-',
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
                    'user_name' => $kegiatan->program?->user?->name ?? '-',
                    'lokasis' => $lokasiRows->all(),
                ];
            }
        }

        return collect($drilldown)
            ->map(function ($programGroups) {
                return collect($programGroups)
                    ->values()
                    ->map(function ($program) {
                        $kegiatans = collect($program['kegiatans'])->values();
                        $totalAnggaran = (float) $kegiatans->sum('anggaran');
                        $totalRealisasi = (float) $kegiatans->sum('realisasi');
                        $sisaAnggaran = $totalAnggaran - $totalRealisasi;
                        $persentaseSerapan = $totalAnggaran > 0
                            ? round(($totalRealisasi / $totalAnggaran) * 100, 2)
                            : 0;

                        return [
                            'id' => $program['id'],
                            'nama' => $program['nama'],
                            'user_name' => $program['user_name'] ?? '-',
                            'jumlah_kegiatan' => $kegiatans->count(),
                            'total_anggaran' => $totalAnggaran,
                            'total_realisasi' => $totalRealisasi,
                            'sisa_anggaran' => $sisaAnggaran,
                            'persentase_serapan' => $persentaseSerapan,
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
        ?array $bulanRange,
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
            ->when($selectedPilarId, fn (Builder $query) => $query->whereHas('program', fn (Builder $programQuery) => $programQuery->where('pilar_id', $selectedPilarId)))
            ->when($selectedStatus, fn (Builder $query) => $query->where('status', $selectedStatus))
            ->when($search !== '', function (Builder $query) use ($search) {
                $keyword = '%' . str_replace(' ', '%', $search) . '%';

                $query->where(function (Builder $nested) use ($keyword) {
                    $nested->where('kegiatans.nama', 'like', $keyword)
                        ->orWhere('kegiatans.deskripsi', 'like', $keyword)
                        ->orWhereHas('program', fn (Builder $programQuery) => $programQuery
                            ->where('nama', 'like', $keyword)
                            ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $keyword)))
                        ->orWhereHas('divisi', fn (Builder $divisiQuery) => $divisiQuery->where('nama', 'like', $keyword))
                        ->orWhereHas('program.pilar', fn (Builder $pilarQuery) => $pilarQuery->where('nama', 'like', $keyword));
                });
            });
    }

    private function buildProgramSummary(Collection $kegiatanIds, ?string $selectedProgramId, Collection $latestRealisasis): array
    {
        $programs = Program::query()
            ->when($selectedProgramId, fn (Builder $query) => $query->where('id', $selectedProgramId))
            ->with('user:id,name')
            ->orderBy('nama')
            ->get(['id', 'nama', 'user_id']);

        if ($programs->isEmpty()) {
            return [];
        }

        if ($kegiatanIds->isEmpty()) {
            return $programs->map(fn (Program $program) => [
                'id' => $program->id,
                'nama' => $program->nama,
                'user_name' => $program->user?->name ?? '-',
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

        $realisasiRows = $latestRealisasis
            ->filter(fn (Realisasi $row) => in_array((int) $row->kegiatan_id, $kegiatanIds->map(fn ($id) => (int) $id)->all(), true))
            ->groupBy(fn (Realisasi $row) => (int) ($row->kegiatan?->program_id ?? 0))
            ->map(fn ($items) => (float) $items->sum('realisasi_biaya'));

        return $programs->map(function (Program $program) use ($anggaranRows, $realisasiRows) {
            $anggaranRow = $anggaranRows->get($program->id);
            $totalAnggaran = (float) ($anggaranRow->total_anggaran ?? 0);
            $totalRealisasi = (float) ($realisasiRows[$program->id] ?? 0);
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            return [
                'id' => $program->id,
                'nama' => $program->nama,
                'user_name' => $program->user?->name ?? '-',
                'jumlah_kegiatan' => (int) ($anggaranRow->jumlah_kegiatan ?? 0),
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
                'persentase_serapan' => $persentaseSerapan,
            ];
        })->values()->all();
    }

    private function latestFilteredRealisasis(Collection $kegiatanIds, ?string $selectedTahun, ?array $bulanRange): Collection
    {
        if ($kegiatanIds->isEmpty()) {
            return collect();
        }

        return Realisasi::query()
            ->whereIn('kegiatan_id', $kegiatanIds)
            ->when($selectedTahun || $bulanRange, function (Builder $query) use ($selectedTahun, $bulanRange) {
                return $query->whereHas('periode', function (Builder $periodeQuery) use ($selectedTahun, $bulanRange) {
                    $periodeQuery
                        ->when($selectedTahun, fn (Builder $nested) => $nested->where('tahun', $selectedTahun))
                        ->when($bulanRange, fn (Builder $nested) => $nested->whereBetween('bulan', $bulanRange));
                });
            })
            ->with(['kegiatan.program.pilar', 'kegiatan.program.user', 'kegiatan.divisi', 'kegiatanLokasi', 'files:id,realisasi_id,file_name,file_type,kategori', 'periode:id,tahun,bulan,triwulan'])
            ->orderByDesc('tanggal_realisasi')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (Realisasi $row) => $row->kegiatan_lokasi_id ? 'lokasi:' . $row->kegiatan_lokasi_id : 'kegiatan:' . $row->kegiatan_id)
            ->map(fn ($items) => $items->first())
            ->values();
    }

    private function buildPilarSummary(Collection $kegiatanIds, ?string $selectedPilarId, Collection $latestRealisasis): array
    {
        $hasNoUrut = Schema::hasColumn('pilars', 'no_urut');

        $pilars = Pilar::query()
            ->when($selectedPilarId, fn (Builder $query) => $query->where('id', $selectedPilarId))
            ->when($hasNoUrut, fn (Builder $query) => $query->orderBy('no_urut'))
            ->orderBy('nama')
            ->get($hasNoUrut ? ['id', 'nama', 'no_urut'] : ['id', 'nama']);

        if ($pilars->isEmpty()) {
            return [];
        }

        if ($kegiatanIds->isEmpty()) {
            return $pilars->map(fn (Pilar $pilar) => [
                'id' => $pilar->id,
                'nama' => $pilar->nama,
                'no_urut' => (int) ($pilar->no_urut ?? 0),
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
            ->whereNotNull('programs.pilar_id')
            ->groupBy('programs.pilar_id')
            ->selectRaw('programs.pilar_id, COUNT(DISTINCT kegiatans.program_id) as jumlah_program, COUNT(DISTINCT kegiatans.id) as jumlah_kegiatan, COALESCE(SUM(kegiatans.rencana_biaya), 0) as total_anggaran')
            ->get()
            ->keyBy('pilar_id');

        $realisasiRows = $latestRealisasis
            ->filter(fn (Realisasi $row) => ($row->kegiatan?->program?->pilar_id) !== null)
            ->groupBy(fn (Realisasi $row) => (int) ($row->kegiatan?->program?->pilar_id ?? 0))
            ->map(fn ($items) => (float) $items->sum('realisasi_biaya'));

        return $pilars->map(function (Pilar $pilar) use ($anggaranRows, $realisasiRows) {
            $anggaranRow = $anggaranRows->get($pilar->id);
            $totalAnggaran = (float) ($anggaranRow->total_anggaran ?? 0);
            $totalRealisasi = (float) ($realisasiRows[$pilar->id] ?? 0);
            $persentaseSerapan = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 2) : 0;

            return [
                'id' => $pilar->id,
                'nama' => $pilar->nama,
                'no_urut' => (int) ($pilar->no_urut ?? 0),
                'jumlah_program' => (int) ($anggaranRow->jumlah_program ?? 0),
                'jumlah_kegiatan' => (int) ($anggaranRow->jumlah_kegiatan ?? 0),
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalAnggaran - $totalRealisasi,
                'persentase_serapan' => $persentaseSerapan,
            ];
        })->values()->all();
    }

    private function buildDivisiSummary(Collection $kegiatanIds, ?string $selectedDivisiId, ?int $forcedDivisiId, Collection $latestRealisasis): array
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

        $realisasiRows = $latestRealisasis
            ->groupBy(fn (Realisasi $row) => (int) ($row->kegiatan?->divisi_id ?? 0))
            ->map(fn ($items) => (float) $items->sum('realisasi_biaya'));

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

    private function buildUserSummary(Collection $kegiatanIds, ?string $selectedUserId, Collection $latestRealisasis): array
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

        $realisasiRows = $latestRealisasis
            ->filter(fn (Realisasi $row) => ($row->kegiatan?->program?->user_id) !== null)
            ->groupBy(fn (Realisasi $row) => (int) ($row->kegiatan?->program?->user_id ?? 0))
            ->map(fn ($items) => (float) $items->sum('realisasi_biaya'));

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
        Collection $latestRealisasis,
        bool $forExport,
        int $perPage,
        int $page,
        string $sortBy,
        string $sortDirection
    ): array
    {
        $kegiatans = (clone $kegiatanQuery)
            ->with([
                'program:id,nama,deskripsi,user_id,pilar_id',
                'program.pilar:id,nama',
                'program.user:id,name',
                'divisi:id,nama',
                'lokasis:id,kegiatan_id,lokasi,tanggal_mulai,tanggal_selesai,target_output,satuan,rencana_biaya',
            ])
            ->orderBy('divisi_id')
            ->orderBy('program_id')
            ->orderBy('nama')
            ->get();

        $kegiatanIds = $kegiatans->pluck('id')->values();

        [$realisasiByLokasiId, $realisasiByKegiatanFallback] = $this->latestRealisasiLookups($latestRealisasis);

        $rows = $kegiatans
            ->flatMap(fn (Kegiatan $kegiatan) => $this->transformDetailRowsByLokasi($kegiatan, $realisasiByLokasiId, $realisasiByKegiatanFallback));

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
            'lokasi_kegiatan',
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

            if ($comparison === 0) {
                $comparison = strcasecmp((string) ($left['lokasi_kegiatan'] ?? ''), (string) ($right['lokasi_kegiatan'] ?? ''));
            }

            return $descending ? -$comparison : $comparison;
        });
    }

    private function transformDetailRowsByLokasi(
        Kegiatan $kegiatan,
        Collection $realisasiByLokasiId,
        Collection $realisasiByKegiatanFallback
    ): Collection
    {
        $baseRef = 'KGT.' . str_pad((string) $kegiatan->id, 3, '0', STR_PAD_LEFT);

        $lokasiRows = collect($kegiatan->lokasis)->values();

        if ($lokasiRows->isEmpty()) {
            $fallback = $realisasiByKegiatanFallback->get((int) $kegiatan->id);
            $realisasiBiaya = (float) ($fallback?->realisasi_biaya ?? 0);
            $realisasiOutput = (float) ($fallback?->realisasi_output ?? 0);
            $anggaran = (float) ($kegiatan->rencana_biaya ?? 0);
            $progress = $kegiatan->target_output > 0
                ? round(($realisasiOutput / (float) $kegiatan->target_output) * 100, 2)
                : 0;

            return collect([[
                'id' => $baseRef,
                'kegiatan_id' => $kegiatan->id,
                'kegiatan_lokasi_id' => null,
                'kode_ref' => $baseRef,
                'program' => $kegiatan->program?->nama,
                'kegiatan' => $kegiatan->nama,
                'lokasi_kegiatan' => '-',
                'waktu_pelaksanaan' => '-',
                'nilai_manfaat_program' => $kegiatan->program?->deskripsi,
                'keterangan' => $fallback?->keterangan ?: $kegiatan->deskripsi,
                'divisi' => $kegiatan->divisi?->nama,
                'user_penginput' => $kegiatan->program?->user?->name,
                'pilar' => $kegiatan->program?->pilar?->nama ?? '-',
                'status' => $kegiatan->status?->value ?? (string) $kegiatan->status,
                'status_label' => $kegiatan->status?->label() ?? ucfirst((string) $kegiatan->status),
                'target_output' => (float) ($kegiatan->target_output ?? 0),
                'satuan' => $kegiatan->satuan,
                'anggaran' => $anggaran,
                'realisasi' => $realisasiBiaya,
                'sisa_anggaran' => $anggaran - $realisasiBiaya,
                'persentase_serapan' => $anggaran > 0 ? round(($realisasiBiaya / $anggaran) * 100, 2) : 0,
                'realisasi_output' => $realisasiOutput,
                'progress' => min(100, max(0, $progress)),
            ]]);
        }

        return $lokasiRows->map(function ($lokasi, $index) use ($kegiatan, $baseRef, $realisasiByLokasiId, $realisasiByKegiatanFallback) {
            $realisasiRow = $realisasiByLokasiId->get((int) $lokasi->id);

            if (!$realisasiRow && $index === 0) {
                $realisasiRow = $realisasiByKegiatanFallback->get((int) $kegiatan->id);
            }

            $realisasiBiaya = (float) ($realisasiRow?->realisasi_biaya ?? 0);
            $realisasiOutput = (float) ($realisasiRow?->realisasi_output ?? 0);
            $targetOutput = (float) ($lokasi->target_output ?? 0);
            $anggaran = (float) ($lokasi->rencana_biaya ?? 0);
            $progress = $targetOutput > 0 ? round(($realisasiOutput / $targetOutput) * 100, 2) : 0;

            $mulai = $lokasi->tanggal_mulai;
            $selesai = $lokasi->tanggal_selesai;
            $waktuPelaksanaan = '-';
            if ($mulai || $selesai) {
                $start = $mulai ? $mulai->translatedFormat('M Y') : null;
                $end = $selesai ? $selesai->translatedFormat('M Y') : null;
                $waktuPelaksanaan = ($start && $end && $start !== $end)
                    ? ($start . ' - ' . $end)
                    : ($start ?: $end ?: '-');
            }

            return [
                'id' => $baseRef . '-L' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'kegiatan_id' => $kegiatan->id,
                'kegiatan_lokasi_id' => $lokasi->id,
                'kode_ref' => $baseRef,
                'program' => $kegiatan->program?->nama,
                'kegiatan' => $kegiatan->nama,
                'lokasi_kegiatan' => $lokasi->lokasi ?? '-',
                'waktu_pelaksanaan' => $waktuPelaksanaan,
                'nilai_manfaat_program' => $kegiatan->program?->deskripsi,
                'keterangan' => $realisasiRow?->keterangan ?: $kegiatan->deskripsi,
                'divisi' => $kegiatan->divisi?->nama,
                'user_penginput' => $kegiatan->program?->user?->name,
                'pilar' => $kegiatan->program?->pilar?->nama ?? '-',
                'status' => $kegiatan->status?->value ?? (string) $kegiatan->status,
                'status_label' => $kegiatan->status?->label() ?? ucfirst((string) $kegiatan->status),
                'target_output' => $targetOutput,
                'satuan' => $lokasi->satuan,
                'tanggal_realisasi' => $realisasiRow?->tanggal_realisasi?->format('Y-m-d'),
                'anggaran' => $anggaran,
                'realisasi' => $realisasiBiaya,
                'sisa_anggaran' => $anggaran - $realisasiBiaya,
                'persentase_serapan' => $anggaran > 0 ? round(($realisasiBiaya / $anggaran) * 100, 2) : 0,
                'realisasi_output' => $realisasiOutput,
                'progress' => min(100, max(0, $progress)),
            ];
        })->values();
    }

    private function latestRealisasiLookups(Collection $latestRealisasis): array
    {
        $byLokasiId = $latestRealisasis
            ->filter(fn (Realisasi $row) => $row->kegiatan_lokasi_id !== null)
            ->keyBy(fn (Realisasi $row) => (int) $row->kegiatan_lokasi_id);

        $byKegiatanFallback = $latestRealisasis
            ->filter(fn (Realisasi $row) => $row->kegiatan_lokasi_id === null)
            ->keyBy(fn (Realisasi $row) => (int) $row->kegiatan_id);

        return [$byLokasiId, $byKegiatanFallback];
    }

    private function buildChartRows(
        string $resumeType,
        Collection $kegiatanIds,
        ?string $selectedProgramId,
        ?string $selectedPilarId,
        ?string $selectedDivisiId,
        ?int $forcedDivisiId,
        ?string $selectedUserId,
        Collection $latestRealisasis
    ): array {
        return match ($resumeType) {
            'program' => $this->buildProgramSummary($kegiatanIds, $selectedProgramId, $latestRealisasis),
            'divisi' => $this->buildDivisiSummary($kegiatanIds, $selectedDivisiId, $forcedDivisiId, $latestRealisasis),
            'user' => $this->buildUserSummary($kegiatanIds, $selectedUserId, $latestRealisasis),
            default => $this->buildPilarSummary($kegiatanIds, $selectedPilarId, $latestRealisasis),
        };
    }

    private function buildFilterSummary(
        string $resumeType,
        ?string $selectedCategoryId,
        ?string $selectedUserId,
        ?string $selectedDivisiId,
        ?string $selectedPilarId,
        ?string $selectedStatus,
        ?string $selectedTahun,
        string $periodMode,
        ?string $periodValue,
        string $search
    ): array {
        $kategori = match ($resumeType) {
            'program' => $selectedCategoryId ? Program::query()->whereKey($selectedCategoryId)->value('nama') : 'Semua Program',
            'divisi' => $selectedCategoryId ? Divisi::query()->whereKey($selectedCategoryId)->value('nama') : 'Semua Divisi',
            'user' => $selectedCategoryId ? User::query()->whereKey($selectedCategoryId)->value('name') : 'Semua User',
            default => $selectedCategoryId ? Pilar::query()->whereKey($selectedCategoryId)->value('nama') : 'Semua Pilar',
        };

        $periodeLabel = $periodValue ? $this->periodLabel($periodMode, (int) $periodValue) : 'Semua Periode';

        return [
            'kategori' => $kategori,
            'user' => $selectedUserId ? User::query()->whereKey($selectedUserId)->value('name') : 'Semua User',
            'divisi' => $selectedDivisiId ? Divisi::query()->whereKey($selectedDivisiId)->value('nama') : 'Semua Divisi',
            'pilar' => $selectedPilarId ? Pilar::query()->whereKey($selectedPilarId)->value('nama') : 'Semua Pilar',
            'status' => $selectedStatus ? ucfirst($selectedStatus) : 'Semua Status',
            'tahun' => $selectedTahun ?: 'Semua Tahun',
            'bulan' => $periodeLabel,
            'triwulan' => $periodeLabel,
            'periode' => $periodeLabel,
            'q' => $search !== '' ? $search : '-',
        ];
    }

    private function parsePeriodRange(string $mode, ?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $v = (int) $value;

        if ($mode === 'bulan') {
            return ($v >= 1 && $v <= 12) ? [$v, $v] : null;
        }

        if ($mode === 'triwulan') {
            return match ($v) {
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12],
                default => null,
            };
        }

        if ($mode === 'semester') {
            return match ($v) {
                1 => [1, 6],
                2 => [7, 12],
                default => null,
            };
        }

        return null;
    }

    private function periodLabel(string $mode, int $value): string
    {
        if ($mode === 'triwulan') {
            return match ($value) {
                1 => 'Tw 1 (Jan–Mar)',
                2 => 'Tw 2 (Apr–Jun)',
                3 => 'Tw 3 (Jul–Sep)',
                4 => 'Tw 4 (Okt–Des)',
                default => 'Tw ' . $value,
            };
        }

        if ($mode === 'semester') {
            return match ($value) {
                1 => 'Sem 1 (Jan–Jun)',
                2 => 'Sem 2 (Jul–Des)',
                default => 'Sem ' . $value,
            };
        }

        return $this->monthLabel($value);
    }

    private function normalizeBulanFilter(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        if (is_numeric($normalized)) {
            $month = (int) $normalized;

            return $month >= 1 && $month <= 12 ? (string) $month : null;
        }

        $map = [
            'tw 1' => 1,
            'tw 2' => 4,
            'tw 3' => 7,
            'tw 4' => 10,
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'mei' => 5,
            'jun' => 6,
            'jul' => 7,
            'agu' => 8,
            'sep' => 9,
            'okt' => 10,
            'nov' => 11,
            'des' => 12,
        ];

        return array_key_exists($normalized, $map) ? (string) $map[$normalized] : null;
    }

    private function monthLabel(int $bulan): string
    {
        return match ($bulan) {
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            default => 'Des',
        };
    }
}
