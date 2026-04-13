<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ActivityLog;
use App\Models\Pilar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Program::with(['user', 'kegiatans' => function ($q) use ($user) {
            $q->with([
                'divisi',
                'program.pilar',
                'activityLogs' => function ($al) {
                    $al->with('user:id,name')
                        ->whereIn('action', ['create', 'submit'])
                        ->oldest();
                },
            ]);
            if ($user->isDivisi()) {
                $q->where('divisi_id', $user->divisi_id);
            }
        }, 'pilar'])->withCount(['kegiatans' => function ($q) use ($user) {
            if ($user->isDivisi()) {
                $q->where('divisi_id', $user->divisi_id);
            }
        }]);

        // Filter: superadmin sees all, divisi sees only programs with kegiatan in their divisi
        if ($user->isDivisi()) {
            $query->whereHas('kegiatans', function ($q) use ($user) {
                $q->where('divisi_id', $user->divisi_id);
            });
        }

        $programs = $query->latest()->get()->map(function (Program $p) use ($user) {
            // If user is divisi, calculate only for their divisi kegiatan
            if ($user->isDivisi()) {
                $userKegiatans = $p->kegiatans->where('divisi_id', $user->divisi_id);
                $totalBiaya = $userKegiatans->sum('rencana_biaya');
            } else {
                $totalBiaya = $p->total_kegiatan_biaya;
            }

            return array_merge($p->toArray(), [
                'total_kegiatan_biaya' => $totalBiaya,
                'sisa_anggaran'        => $p->rencana_biaya - $totalBiaya,
                'kegiatans'            => $p->kegiatans->map(function ($k) {
                    $creator = $k->activityLogs->first()?->user;

                    return array_merge($k->toArray(), [
                        'creator_name' => $creator?->name ?? '-',
                    ]);
                })->values()->toArray(),
            ]);
        });

        return Inertia::render('Program/Index', [
            'programs' => $programs,
            'pilars' => Pilar::query()->orderBy('nama')->get(['id', 'nama', 'rencana_biaya']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pilar_id'      => 'required|exists:pilars,id',
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'nullable|numeric|min:0',
            'satuan'        => 'nullable|string|max:100',
            'rencana_biaya' => 'nullable|numeric|min:0',
        ]);

        $data['target_output'] = (float) ($data['target_output'] ?? 0);
        $data['satuan'] = trim((string) ($data['satuan'] ?? ''));
        $data['rencana_biaya'] = (float) ($data['rencana_biaya'] ?? 0);

        $pilar = Pilar::findOrFail($data['pilar_id']);
        $usedBudget = (float) Program::query()->where('pilar_id', $pilar->id)->sum('rencana_biaya');
        if (($usedBudget + (float) $data['rencana_biaya']) > (float) $pilar->rencana_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Total anggaran program melebihi anggaran pilar.']);
        }

        $data['user_id'] = Auth::id();
        $program = Program::create($data);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'program',
            'action'       => 'create',
            'subject_type' => Program::class,
            'subject_id'   => $program->id,
            'description'  => "Program '{$program->nama}' dibuat.",
        ]);

        return back()->with('success', 'Program berhasil ditambahkan.');
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'pilar_id'      => 'required|exists:pilars,id',
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'nullable|numeric|min:0',
            'satuan'        => 'nullable|string|max:100',
            'rencana_biaya' => 'nullable|numeric|min:0',
        ]);

        $data['target_output'] = (float) ($data['target_output'] ?? 0);
        $data['satuan'] = trim((string) ($data['satuan'] ?? ''));
        $data['rencana_biaya'] = (float) ($data['rencana_biaya'] ?? 0);

        // Budget check: total kegiatan rencana_biaya must not exceed new rencana_biaya
        if ($data['rencana_biaya'] < $program->total_kegiatan_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Anggaran program tidak boleh lebih kecil dari total rencana biaya kegiatan.']);
        }

        $pilar = Pilar::findOrFail($data['pilar_id']);
        $usedBudget = (float) Program::query()
            ->where('pilar_id', $pilar->id)
            ->where('id', '!=', $program->id)
            ->sum('rencana_biaya');
        if (($usedBudget + (float) $data['rencana_biaya']) > (float) $pilar->rencana_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Total anggaran program melebihi anggaran pilar.']);
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'program',
            'action'       => 'update',
            'subject_type' => Program::class,
            'subject_id'   => $program->id,
            'description'  => "Program '{$program->nama}' diperbarui.",
        ]);

        $program->update($data);
        return back()->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program)
    {
        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'program',
            'action'       => 'delete',
            'subject_type' => Program::class,
            'subject_id'   => $program->id,
            'description'  => "Program '{$program->nama}' dihapus.",
        ]);

        $program->delete();
        return back()->with('success', 'Program berhasil dihapus.');
    }

    public function show(Program $program)
    {
        $program->load(['user', 'pilar', 'kegiatans.divisi', 'kegiatans.program.pilar', 'kegiatans.realisasis.periode']);

        // Fetch activity logs for this program
        $logs = ActivityLog::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->with('user')
            ->latest()
            ->get();

        return Inertia::render('Program/Show', [
            'program' => array_merge($program->toArray(), [
                'total_kegiatan_biaya' => $program->total_kegiatan_biaya,
                'sisa_anggaran' => $program->rencana_biaya - $program->total_kegiatan_biaya,
            ]),
            'logs' => $logs,
        ]);
    }
}
