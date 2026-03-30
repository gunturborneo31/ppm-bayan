<?php

namespace App\Http\Controllers;

use App\Enums\KegiatanStatus;
use App\Models\ActivityLog;
use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Pilar;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KegiatanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isCdo = $this->isCdo($user);

        $query = Kegiatan::with(['program', 'divisi', 'pilars'])
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
            $q->with(['divisi', 'pilars', 'activityLogs' => function ($al) {
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
                    $creator = $k->activityLogs->first()?->user;
                    return array_merge($k->toArray(), [
                        'creator_name' => $creator?->name ?? '-',
                    ]);
                })->values()->toArray(),
            ]);
        });

        // For form dropdown: all programs available for selection
        $allPrograms = Program::select('id', 'nama')->latest()->get();

        return Inertia::render('Kegiatan/Index', [
            'kegiatans'   => $kegiatans,
            'programs'    => $programs,
            'allPrograms' => $allPrograms,
            'divisis'     => Divisi::select('id', 'nama')->where('status', true)->get(),
            'pilars'      => Pilar::select('id', 'nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'program_id'    => 'required|exists:programs,id',
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:50',
            'rencana_biaya' => 'required|numeric|min:0',
            'pilar_ids'     => 'nullable|array',
            'pilar_ids.*'   => 'exists:pilars,id',
        ];

        if ($this->isCdo($user)) {
            $rules['divisi_id'] = 'required|exists:divisis,id';
        }

        $data = $request->validate($rules);

        if (!$this->isCdo($user)) {
            if (!$user->divisi_id) {
                return back()->withErrors(['divisi_id' => 'Divisi user belum diatur oleh admin.']);
            }
            $data['divisi_id'] = $user->divisi_id;
        }

        // Budget validation
        $program = Program::findOrFail($data['program_id']);
        $usedBudget = $program->total_kegiatan_biaya;
        if (($usedBudget + $data['rencana_biaya']) > $program->rencana_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Total rencana biaya kegiatan melebihi anggaran program.']);
        }

        $pilarIds = $data['pilar_ids'] ?? [];
        unset($data['pilar_ids']);

        $data['status'] = KegiatanStatus::DIAJUKAN;
        $kegiatan = Kegiatan::create($data);
        if ($pilarIds) {
            $kegiatan->pilars()->sync($pilarIds);
        }

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
        $user = Auth::user();

        if (!$this->isCdo($user) && !$this->ownsKegiatan($user, $kegiatan)) {
            return back()->withErrors(['status' => 'Anda hanya dapat mengubah kegiatan yang Anda ajukan.']);
        }

        // Locked if already submitted (can only edit revisi)
        if (in_array($kegiatan->status, [KegiatanStatus::DISETUJUI, KegiatanStatus::DIAJUKAN])) {
            return back()->withErrors(['status' => 'Kegiatan yang sudah diajukan tidak dapat diedit.']);
        }

        $rules = [
            'program_id'    => 'required|exists:programs,id',
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:50',
            'rencana_biaya' => 'required|numeric|min:0',
            'pilar_ids'     => 'nullable|array',
            'pilar_ids.*'   => 'exists:pilars,id',
        ];

        if ($this->isCdo($user)) {
            $rules['divisi_id'] = 'required|exists:divisis,id';
        }

        $data = $request->validate($rules);

        if (!$this->isCdo($user)) {
            $data['divisi_id'] = $user->divisi_id;
        }

        // Budget validation (exclude this kegiatan's current budget)
        $program    = Program::findOrFail($data['program_id']);
        $usedBudget = $program->kegiatans()->where('id', '!=', $kegiatan->id)->sum('rencana_biaya');
        if (($usedBudget + $data['rencana_biaya']) > $program->rencana_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Total rencana biaya kegiatan melebihi anggaran program.']);
        }

        $pilarIds = $data['pilar_ids'] ?? [];
        unset($data['pilar_ids']);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'update',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' diperbarui.",
        ]);

        $kegiatan->update($data);
        $kegiatan->pilars()->sync($pilarIds);

        return back()->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $user = Auth::user();

        if (!$this->isCdo($user)) {
            return back()->withErrors(['status' => 'Penghapusan harus disetujui CPO.']);
        }

        if (in_array($kegiatan->status, [KegiatanStatus::DISETUJUI, KegiatanStatus::DIAJUKAN])) {
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
        $user = Auth::user();

        if (!$this->isCdo($user) && !$this->ownsKegiatan($user, $kegiatan)) {
            return back()->withErrors(['status' => 'Anda hanya dapat mengajukan ulang kegiatan yang Anda ajukan.']);
        }

        // No standalone submit anymore since kegiatan are created as DIAJUKAN
        if ($kegiatan->status !== KegiatanStatus::REVISI) {
            return back()->withErrors(['status' => 'Hanya kegiatan berstatus Revisi yang dapat diajukan ulang.']);
        }

        $kegiatan->update(['status' => KegiatanStatus::DIAJUKAN]);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'submit',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' diajukan untuk persetujuan.",
        ]);

        return back()->with('success', 'Kegiatan berhasil diajukan.');
    }

    public function approve(Request $request, Kegiatan $kegiatan)
    {
        if ($kegiatan->status !== KegiatanStatus::DIAJUKAN) {
            return back()->withErrors(['status' => 'Hanya kegiatan berstatus Diajukan yang dapat disetujui.']);
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

        if ($kegiatan->status !== KegiatanStatus::DIAJUKAN) {
            return back()->withErrors(['status' => 'Hanya kegiatan berstatus Diajukan yang dapat ditolak/direvisi.']);
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
        $user = Auth::user();

        if (!$this->isCdo($user) && !$this->ownsKegiatan($user, $kegiatan)) {
            abort(403, 'Anda hanya dapat melihat kegiatan yang Anda ajukan.');
        }

        $kegiatan->load(['program', 'divisi', 'pilars', 'realisasis.periode', 'files.uploadedBy']);

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
        ]);
    }

    public function perencanaan()
    {
        $user = Auth::user();
        $isCdo = $this->isCdo($user);

        $programs = Program::with(['kegiatans' => function ($q) use ($user, $isCdo) {
            $q->with(['divisi', 'pilars', 'program'])->orderBy('nama');
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

        return Inertia::render('Perencanaan/Index', [
            'programs' => $programs,
            'isSuperadmin' => $isCdo,
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
}
