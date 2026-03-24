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
        $programs = Program::with(['user', 'kegiatans'])
            ->withCount('kegiatans')
            ->latest()
            ->get()
            ->map(function ($p) {
                return array_merge($p->toArray(), [
                    'total_kegiatan_biaya' => $p->total_kegiatan_biaya,
                    'sisa_anggaran'        => $p->rencana_biaya - $p->total_kegiatan_biaya,
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
}
