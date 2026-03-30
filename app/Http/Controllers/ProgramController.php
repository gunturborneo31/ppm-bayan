<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Program::with(['user', 'kegiatans' => function ($q) use ($user) {
            $q->with(['divisi', 'pilars']);
            if ($user->isDivisi()) {
                $q->where('divisi_id', $user->divisi_id);
            }
        }])->withCount(['kegiatans' => function ($q) use ($user) {
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
            ]);
        });

        return Inertia::render('Program/Index', compact('programs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:100',
            'rencana_biaya' => 'required|numeric|min:0',
        ]);
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
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:100',
            'rencana_biaya' => 'required|numeric|min:0',
        ]);

        // Budget check: total kegiatan rencana_biaya must not exceed new rencana_biaya
        if ($data['rencana_biaya'] < $program->total_kegiatan_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Anggaran program tidak boleh lebih kecil dari total rencana biaya kegiatan.']);
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
        $program->load(['user', 'kegiatans.divisi', 'kegiatans.pilars', 'kegiatans.realisasis.periode']);

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
