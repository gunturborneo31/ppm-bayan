<?php

namespace App\Http\Controllers;

use App\Enums\KegiatanStatus;
use App\Models\ActivityLog;
use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\KegiatanComment;
use App\Models\Lokasi;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\KegiatanCommentNotification;
use Illuminate\Database\QueryException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KegiatanController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $isCdo = $this->isCdo($user);

        $query = Kegiatan::with(['program.pilar', 'divisi', 'lokasis'])
            ->withCount('realisasis');

        if (!$isCdo) {
            $query->whereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('activity_logs')
                    ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                    ->where('activity_logs.subject_type', Kegiatan::class)
                    ->where('activity_logs.user_id', $user->id)
                    ->whereIn('activity_logs.action', ['create', 'submit']);
            });
        }

        $kegiatans = $query->latest()->get()->map(fn(Kegiatan $k) => array_merge(
            $k->toArray(),
            ['progress' => $k->progress, 'status_label' => $k->status->label(), 'status_color' => $k->status->color()]
        ));

        // Fetch programs with their kegiatan for sidebar/list display
        $programsQuery = Program::with(['kegiatans' => function ($q) use ($user) {
            $q->with(['divisi', 'program.pilar', 'lokasis', 'activityLogs' => function ($al) {
                $al->with('user:id,name')->whereIn('action', ['create', 'submit'])->oldest();
            }])->orderBy('nama');
            if (!$this->isCdo($user)) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                        ->where('activity_logs.subject_type', Kegiatan::class)
                        ->where('activity_logs.user_id', $user->id)
                        ->whereIn('activity_logs.action', ['create', 'submit']);
                });
            }
        }])->withCount(['kegiatans' => function ($q) use ($user) {
            if (!$this->isCdo($user)) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                        ->where('activity_logs.subject_type', Kegiatan::class)
                        ->where('activity_logs.user_id', $user->id)
                        ->whereIn('activity_logs.action', ['create', 'submit']);
                });
            }
        }]);

        if (!$isCdo) {
            // Non-CDO only sees programs containing their own submitted kegiatan.
            $programsQuery->whereHas('kegiatans', function ($q) use ($user) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                        ->where('activity_logs.subject_type', Kegiatan::class)
                        ->where('activity_logs.user_id', $user->id)
                        ->whereIn('activity_logs.action', ['create', 'submit']);
                });
            });
        }

        $programRows = $programsQuery->latest()->get();
        $programIds = $programRows->pluck('id')->values();

        $totalUsageByProgram = collect();
        $ownUsageByProgram = collect();

        if ($programIds->isNotEmpty()) {
            $totalUsageByProgram = Kegiatan::query()
                ->whereIn('program_id', $programIds)
                ->select('program_id', DB::raw('COALESCE(SUM(rencana_biaya), 0) as total_terpakai'))
                ->groupBy('program_id')
                ->pluck('total_terpakai', 'program_id');

            $ownUsageByProgram = Kegiatan::query()
                ->whereIn('program_id', $programIds)
                ->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                        ->where('activity_logs.subject_type', Kegiatan::class)
                        ->where('activity_logs.user_id', $user->id)
                        ->whereIn('activity_logs.action', ['create', 'submit']);
                })
                ->select('program_id', DB::raw('COALESCE(SUM(rencana_biaya), 0) as total_terpakai'))
                ->groupBy('program_id')
                ->pluck('total_terpakai', 'program_id');
        }

        $programs = $programRows->map(function (Program $p) use ($totalUsageByProgram, $ownUsageByProgram) {
            // Calculate stats for kegiatan in this program
            $kegiatanInDivisi = $p->kegiatans;
            $totalBiaya = (float) ($totalUsageByProgram[$p->id] ?? 0);
            $ownBiaya = (float) ($ownUsageByProgram[$p->id] ?? 0);
            $otherBiaya = max(0, $totalBiaya - $ownBiaya);
            $avgProgress = $kegiatanInDivisi->count() > 0 
                ? round($kegiatanInDivisi->avg('progress'))
                : 0;

            return array_merge($p->toArray(), [
                'total_kegiatan_biaya' => $totalBiaya,
                'total_kegiatan_biaya_user' => $ownBiaya,
                'total_kegiatan_biaya_user_lain' => $otherBiaya,
                'sisa_anggaran' => $p->rencana_biaya - $totalBiaya,
                'avg_progress' => $avgProgress,
                'kegiatans' => $p->kegiatans->map(function ($k) {
                    $creator = $k->activityLogs->first()?->user
                        ?? ActivityLog::where('subject_type', Kegiatan::class)
                            ->where('subject_id', $k->id)
                            ->whereIn('action', ['create', 'submit'])
                            ->with('user:id,name')
                            ->oldest()
                            ->first()?->user
                        ?? ActivityLog::where('module', 'kegiatan')
                            ->where('subject_id', $k->id)
                            ->whereIn('action', ['create', 'submit'])
                            ->with('user:id,name')
                            ->oldest()
                            ->first()?->user;

                    return array_merge($k->toArray(), [
                        'creator_name' => $creator?->name ?? '-',
                        'pilars' => $k->program?->pilar ? [[
                            'id' => $k->program->pilar->id,
                            'nama' => $k->program->pilar->nama,
                        ]] : [],
                    ]);
                })->values()->toArray(),
            ]);
        });

        $allPrograms = $this->allProgramsForForm();

        return Inertia::render('Kegiatan/Index', [
            'kegiatans'   => $kegiatans,
            'programs'    => $programs,
            'allPrograms' => $allPrograms,
            'divisis'     => Divisi::select('id', 'nama')->where('status', true)->get(),
            'lokasiOptions' => Lokasi::query()->orderBy('nama')->pluck('nama')->values(),
        ]);
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if planning is locked for non-superadmin users
        if (!$user->isSuperadmin() && Setting::isPlanningLocked()) {
            return back()->withErrors(['status' => 'Pengaturan perencanaan sedang dikunci. Hubungi administrator untuk informasi lebih lanjut.']);
        }

        $rules = $this->kegiatanRules($user);

        if ($this->isCdo($user)) {
            $rules['divisi_id'] = 'required|exists:divisis,id';
        }

        $data = $request->validate($rules);
        $data['nama'] = trim((string) ($data['nama'] ?? ''));

        if (!$this->isCdo($user)) {
            if (!$user->divisi_id) {
                return back()->withErrors(['divisi_id' => 'Divisi user belum diatur oleh admin.']);
            }
            $data['divisi_id'] = $user->divisi_id;
        }

        $lokasis = $this->normalizeLokasis($data['lokasis'] ?? []);
        if ($error = $this->invalidLokasiDateError($lokasis)) {
            return back()->withErrors($error)->withInput();
        }

        $payload = $this->extractKegiatanPayload($data, $lokasis);

        $duplicate = $this->findDuplicateKegiatan(
            null,
            (int) $payload['program_id'],
            (int) $payload['divisi_id'],
            (string) $payload['nama']
        );

        if ($duplicate && !$duplicate->trashed()) {
            return back()->withErrors([
                'nama' => 'Nama kegiatan sudah digunakan pada program dan divisi yang sama.',
            ])->withInput();
        }

        // Budget validation
        $program = Program::findOrFail($data['program_id']);
        $usedBudget = $program->total_kegiatan_biaya;
        if (($usedBudget + $payload['rencana_biaya']) > $program->rencana_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Total rencana biaya kegiatan melebihi anggaran program.']);
        }

        $payload['status'] = KegiatanStatus::DIAJUKAN;

        if ($duplicate && $duplicate->trashed()) {
            $duplicate->restore();
            $duplicate->update($payload);
            $kegiatan = $duplicate;
        } else {
            try {
                $kegiatan = Kegiatan::create($payload);
            } catch (QueryException $e) {
                return back()->withErrors([
                    'nama' => 'Nama kegiatan sudah digunakan pada program dan divisi yang sama.',
                ])->withInput();
            }
        }

        $this->syncLokasis($kegiatan, $lokasis);
        $this->syncMasterLokasis($lokasis);
        $this->syncKegiatanPilarFromProgram($kegiatan, $program);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'submit',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' dibuat dan diajukan untuk persetujuan.",
        ]);

        return back()->with('success', 'Kegiatan berhasil ditambahkan dan diajukan.');
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if planning is locked for non-superadmin users
        if (!$user->isSuperadmin() && Setting::isPlanningLocked()) {
            return back()->withErrors(['status' => 'Pengaturan perencanaan sedang dikunci. Hubungi administrator untuk informasi lebih lanjut.']);
        }

        if (!$this->isCdo($user) && !$this->ownsKegiatan($user, $kegiatan)) {
            return back()->withErrors(['status' => 'Anda hanya dapat mengubah kegiatan yang Anda ajukan.']);
        }

        // Locked if already submitted (can only edit revisi)
        if (in_array($kegiatan->status, [KegiatanStatus::DISETUJUI, KegiatanStatus::DIAJUKAN, KegiatanStatus::DIAJUKAN_ULANG], true)) {
            return back()->withErrors(['status' => 'Kegiatan yang sudah diajukan tidak dapat diedit.']);
        }

        $rules = $this->kegiatanRules($user);

        if ($this->isCdo($user)) {
            $rules['divisi_id'] = 'required|exists:divisis,id';
        }

        $data = $request->validate($rules);
        $data['nama'] = trim((string) ($data['nama'] ?? ''));

        if (!$this->isCdo($user)) {
            $data['divisi_id'] = $user->divisi_id;
        }

        $lokasis = $this->normalizeLokasis($data['lokasis'] ?? []);
        if ($error = $this->invalidLokasiDateError($lokasis)) {
            return back()->withErrors($error)->withInput();
        }

        $payload = $this->extractKegiatanPayload($data, $lokasis);

        $duplicate = $this->findDuplicateKegiatan(
            $kegiatan->id,
            (int) $payload['program_id'],
            (int) $payload['divisi_id'],
            (string) $payload['nama']
        );

        if ($duplicate) {
            return back()->withErrors([
                'nama' => 'Nama kegiatan sudah digunakan pada program dan divisi yang sama.',
            ])->withInput();
        }

        // Budget validation (exclude this kegiatan's current budget)
        $program    = Program::findOrFail($data['program_id']);
        $usedBudget = $program->kegiatans()->where('id', '!=', $kegiatan->id)->sum('rencana_biaya');
        if (($usedBudget + $payload['rencana_biaya']) > $program->rencana_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Total rencana biaya kegiatan melebihi anggaran program.']);
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'update',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' diperbarui.",
        ]);

        try {
            $kegiatan->update($payload);
        } catch (QueryException $e) {
            return back()->withErrors([
                'nama' => 'Nama kegiatan sudah digunakan pada program dan divisi yang sama.',
            ])->withInput();
        }

        $this->syncLokasis($kegiatan, $lokasis);
        $this->syncMasterLokasis($lokasis);
        $this->syncKegiatanPilarFromProgram($kegiatan, $program);

        return back()->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$this->isCdo($user)) {
            return back()->withErrors(['status' => 'Penghapusan harus disetujui CPO.']);
        }

        if (in_array($kegiatan->status, [KegiatanStatus::DISETUJUI, KegiatanStatus::DIAJUKAN, KegiatanStatus::DIAJUKAN_ULANG], true)) {
            return back()->withErrors(['status' => 'Kegiatan yang sudah diajukan tidak dapat dihapus.']);
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'delete',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' dihapus.",
        ]);

        $kegiatan->delete();
        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function submit(Kegiatan $kegiatan)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$this->isCdo($user) && !$this->ownsKegiatan($user, $kegiatan)) {
            return back()->withErrors(['status' => 'Anda hanya dapat mengajukan ulang kegiatan yang Anda ajukan.']);
        }

        if (!in_array($kegiatan->status, [KegiatanStatus::REVISI, KegiatanStatus::DITOLAK], true)) {
            return back()->withErrors(['status' => 'Hanya kegiatan berstatus Revisi atau Ditolak yang dapat diajukan ulang.']);
        }

        $kegiatan->update(['status' => KegiatanStatus::DIAJUKAN_ULANG]);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'submit',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' diajukan ulang untuk persetujuan.",
        ]);

        return back()->with('success', 'Kegiatan berhasil diajukan ulang.');
    }

    public function approve(Request $request, Kegiatan $kegiatan)
    {
        if (!in_array($kegiatan->status, [KegiatanStatus::DIAJUKAN, KegiatanStatus::DIAJUKAN_ULANG], true)) {
            return back()->withErrors(['status' => 'Hanya kegiatan berstatus Diajukan atau Diajukan Ulang yang dapat disetujui.']);
        }

        $kegiatan->update(['status' => KegiatanStatus::DISETUJUI, 'catatan_revisi' => null]);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'approve',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' disetujui.",
        ]);

        return back()->with('success', 'Kegiatan berhasil disetujui.');
    }

    public function reject(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'catatan_revisi' => 'required|string',
            'tipe'           => 'required|in:revisi,ditolak',
        ]);

        if (!in_array($kegiatan->status, [KegiatanStatus::DIAJUKAN, KegiatanStatus::DIAJUKAN_ULANG], true)) {
            return back()->withErrors(['status' => 'Hanya kegiatan berstatus Diajukan atau Diajukan Ulang yang dapat ditolak/direvisi.']);
        }

        $newStatus = $data['tipe'] === 'revisi' ? KegiatanStatus::REVISI : KegiatanStatus::DITOLAK;

        $kegiatan->update([
            'status'          => $newStatus,
            'catatan_revisi'  => $data['catatan_revisi'],
            'version'         => $kegiatan->version + ($data['tipe'] === 'revisi' ? 1 : 0),
        ]);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => $data['tipe'],
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' " . ($data['tipe'] === 'revisi' ? 'dikembalikan untuk revisi' : 'ditolak') . ". Catatan: {$data['catatan_revisi']}",
        ]);

        return back()->with('success', 'Kegiatan berhasil ' . ($data['tipe'] === 'revisi' ? 'dikembalikan untuk revisi' : 'ditolak') . '.');
    }

    public function show(Kegiatan $kegiatan)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$this->isCdo($user) && !$user->isPimpinan() && !$this->ownsKegiatan($user, $kegiatan)) {
            abort(403, 'Anda hanya dapat melihat kegiatan yang Anda ajukan.');
        }

        $kegiatan->load(['program.pilar', 'divisi', 'lokasis', 'realisasis.periode', 'realisasis.kegiatanLokasi', 'files.uploadedBy', 'comments.user']);

        DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->where('type', KegiatanCommentNotification::class)
            ->where('data->kegiatan_id', $kegiatan->id)
            ->update(['read_at' => now()]);

        // Fetch activity logs for this kegiatan
        $logs = ActivityLog::where('subject_type', Kegiatan::class)
            ->where('subject_id', $kegiatan->id)
            ->with('user')
            ->latest()
            ->get();

        return Inertia::render('Kegiatan/Show', [
            'kegiatan' => array_merge($kegiatan->toArray(), [
                'progress'     => $kegiatan->progress,
                'status_label' => $kegiatan->status->label(),
                'status_color' => $kegiatan->status->color(),
            ]),
            'logs' => $logs,
            'can_comment' => $user->isPimpinan(),
        ]);
    }

    public function storeComment(Request $request, Kegiatan $kegiatan)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isPimpinan()) {
            abort(403, 'Hanya pimpinan yang dapat memberi komentar.');
        }

        $data = $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        $comment = KegiatanComment::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $user->id,
            'comment' => trim((string) $data['comment']),
        ]);

        ActivityLog::create([
            'user_id'      => $user->id,
            'module'       => 'kegiatan',
            'action'       => 'comment',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Komentar pimpinan ditambahkan pada kegiatan '{$kegiatan->nama}'.",
        ]);

        $recipientIds = ActivityLog::query()
            ->where('subject_type', Kegiatan::class)
            ->where('subject_id', $kegiatan->id)
            ->whereIn('action', ['create', 'submit'])
            ->pluck('user_id')
            ->push(User::query()->where('role', 'superadmin')->pluck('id'))
            ->flatten()
            ->filter(fn ($id) => (int) $id !== (int) $user->id)
            ->unique()
            ->values();

        if ($recipientIds->isNotEmpty()) {
            User::query()
                ->whereIn('id', $recipientIds)
                ->get()
                ->each(fn (User $recipient) => $recipient->notify(new KegiatanCommentNotification($kegiatan, $comment, $user)));
        }

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function perencanaan()
    {
        /** @var User $user */
        $user = Auth::user();
        $isCdo = $this->isCdo($user);

        $programRows = Program::with(['kegiatans' => function ($q) use ($user, $isCdo) {
            $q->with(['divisi', 'program.pilar', 'program', 'lokasis'])->orderBy('nama');
            if (!$isCdo) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                        ->where('activity_logs.subject_type', Kegiatan::class)
                        ->where('activity_logs.user_id', $user->id)
                        ->whereIn('activity_logs.action', ['create', 'submit']);
                });
            }
        }])->withCount(['kegiatans' => function ($q) use ($user, $isCdo) {
            if (!$isCdo) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('activity_logs')
                        ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                        ->where('activity_logs.subject_type', Kegiatan::class)
                        ->where('activity_logs.user_id', $user->id)
                        ->whereIn('activity_logs.action', ['create', 'submit']);
                });
            }
        }])->latest()->get();

        $kegiatanIds = $programRows
            ->pluck('kegiatans')
            ->flatten(1)
            ->pluck('id')
            ->filter()
            ->values();

        $creatorNamesByKegiatanId = collect();

        if ($kegiatanIds->isNotEmpty()) {
            $creatorNamesByKegiatanId = ActivityLog::query()
                ->where('subject_type', Kegiatan::class)
                ->whereIn('subject_id', $kegiatanIds)
                ->whereIn('action', ['create', 'submit'])
                ->with('user:id,name')
                ->orderBy('id')
                ->get()
                ->groupBy('subject_id')
                ->map(fn($logs) => $logs->first()?->user?->name);

            $missingKegiatanIds = $kegiatanIds
                ->filter(fn($id) => empty($creatorNamesByKegiatanId->get($id)))
                ->values();

            if ($missingKegiatanIds->isNotEmpty()) {
                $fallbackCreatorByKegiatanId = ActivityLog::query()
                    ->where('module', 'kegiatan')
                    ->whereIn('subject_id', $missingKegiatanIds)
                    ->whereIn('action', ['create', 'submit'])
                    ->with('user:id,name')
                    ->orderBy('id')
                    ->get()
                    ->groupBy('subject_id')
                    ->map(fn($logs) => $logs->first()?->user?->name)
                    ->filter();

                $creatorNamesByKegiatanId = $creatorNamesByKegiatanId->merge($fallbackCreatorByKegiatanId);
            }
        }

        $programs = $programRows->map(function (Program $program) use ($creatorNamesByKegiatanId) {
            return array_merge($program->toArray(), [
                'kegiatans' => $program->kegiatans->map(function (Kegiatan $kegiatan) use ($creatorNamesByKegiatanId) {
                    return array_merge($kegiatan->toArray(), [
                        'creator_name' => $creatorNamesByKegiatanId->get($kegiatan->id, '-'),
                    ]);
                })->values()->toArray(),
            ]);
        })->values();

        return Inertia::render('Perencanaan/Index', [
            'programs' => $programs,
            'allPrograms' => $this->allProgramsForForm(),
            'divisis' => Divisi::select('id', 'nama')->where('status', true)->get(),
            'lokasiOptions' => Lokasi::query()->orderBy('nama')->pluck('nama')->values(),
            'isSuperadmin' => $isCdo,
            'planningLocked' => Setting::isPlanningLocked(),
        ]);
    }

    private function isCdo(User $user): bool
    {
        return $user->isCdo();
    }

    private function ownsKegiatan(User $user, Kegiatan $kegiatan): bool
    {
        return ActivityLog::where('subject_type', Kegiatan::class)
            ->where('subject_id', $kegiatan->id)
            ->where('user_id', $user->id)
            ->whereIn('action', ['create', 'submit'])
            ->exists();
    }

    private function kegiatanRules(User $user): array
    {
        $rules = [
            'program_id' => 'required|exists:programs,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'berdasarkan' => 'nullable|string|max:255',
            'lokasis' => 'required|array|min:1',
            'lokasis.*.lokasi' => 'required|string|max:255',
            'lokasis.*.tanggal_mulai' => 'nullable|date',
            'lokasis.*.tanggal_selesai' => 'nullable|date',
            'lokasis.*.target_output' => 'nullable|numeric|min:0',
            'lokasis.*.satuan' => 'nullable|string|max:50',
            'lokasis.*.rencana_biaya' => 'nullable|numeric|min:0',
        ];

        if ($this->isCdo($user)) {
            $rules['divisi_id'] = 'required|exists:divisis,id';
        }

        return $rules;
    }

    private function normalizeLokasis(array $lokasis): array
    {
        return collect($lokasis)
            ->map(function (array $lokasi) {
                return [
                    'lokasi' => trim((string) ($lokasi['lokasi'] ?? '')),
                    'tanggal_mulai' => $lokasi['tanggal_mulai'] ?? null,
                    'tanggal_selesai' => $lokasi['tanggal_selesai'] ?? null,
                    'target_output' => (float) ($lokasi['target_output'] ?? 0),
                    'satuan' => trim((string) ($lokasi['satuan'] ?? '')),
                    'rencana_biaya' => (float) ($lokasi['rencana_biaya'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function invalidLokasiDateError(array $lokasis): ?array
    {
        foreach ($lokasis as $index => $lokasi) {
            if (!empty($lokasi['tanggal_mulai']) && !empty($lokasi['tanggal_selesai']) && $lokasi['tanggal_selesai'] < $lokasi['tanggal_mulai']) {
                return [
                    "lokasis.$index.tanggal_selesai" => 'Waktu pelaksanaan akhir harus sama atau setelah waktu mulai.',
                ];
            }
        }

        return null;
    }

    private function extractKegiatanPayload(array $data, array $lokasis): array
    {
        $units = collect($lokasis)->pluck('satuan')->filter()->unique()->values();

        return [
            'program_id' => $data['program_id'],
            'divisi_id' => $data['divisi_id'],
            'nama' => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'berdasarkan' => $data['berdasarkan'] ?? null,
            'target_output' => collect($lokasis)->sum('target_output'),
            'target_bulanan' => 0,
            'satuan' => $units->count() === 1
                ? (string) $units->first()
                : ($units->count() > 1 ? 'Multi Satuan' : null),
            'rencana_biaya' => collect($lokasis)->sum('rencana_biaya'),
        ];
    }

    private function syncLokasis(Kegiatan $kegiatan, array $lokasis): void
    {
        $kegiatan->lokasis()->delete();
        $kegiatan->lokasis()->createMany($lokasis);
    }

    private function syncMasterLokasis(array $lokasis): void
    {
        foreach ($lokasis as $lokasi) {
            $nama = trim((string) ($lokasi['lokasi'] ?? ''));
            if ($nama === '') {
                continue;
            }

            Lokasi::firstOrCreate(['nama' => $nama]);
        }
    }

    private function findDuplicateKegiatan(?int $exceptId, int $programId, int $divisiId, string $nama): ?Kegiatan
    {
        $query = Kegiatan::withTrashed()
            ->where('program_id', $programId)
            ->where('divisi_id', $divisiId)
            ->where('nama', $nama);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->first();
    }

    private function allProgramsForForm()
    {
        return Program::query()
            ->select('id', 'nama', 'deskripsi', 'rencana_biaya', 'pilar_id')
            ->with('pilar:id,nama,deskripsi')
            ->withSum('kegiatans as total_kegiatan_biaya', 'rencana_biaya')
            ->latest()
            ->get()
            ->map(function (Program $program) {
                $totalKegiatanBiaya = (float) ($program->total_kegiatan_biaya ?? 0);

                return [
                    'id' => $program->id,
                    'nama' => $program->nama,
                    'deskripsi' => $program->deskripsi,
                    'pilar_id' => $program->pilar_id,
                    'pilar' => $program->pilar ? [
                        'id' => $program->pilar->id,
                        'nama' => $program->pilar->nama,
                        'deskripsi' => $program->pilar->deskripsi,
                    ] : null,
                    'rencana_biaya' => (float) $program->rencana_biaya,
                    'total_kegiatan_biaya' => $totalKegiatanBiaya,
                    'sisa_anggaran' => (float) $program->rencana_biaya - $totalKegiatanBiaya,
                ];
            })
            ->values();
    }

    private function syncKegiatanPilarFromProgram(Kegiatan $kegiatan, Program $program): void
    {
        if ($program->pilar_id) {
            $kegiatan->pilars()->sync([$program->pilar_id]);
            return;
        }

        $kegiatan->pilars()->detach();
    }
}
