<?php

namespace App\Http\Controllers;

use App\Enums\KegiatanStatus;
use App\Models\ActivityLog;
use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Pilar;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KegiatanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Kegiatan::with(['program', 'divisi', 'pilars'])
            ->withCount('realisasis');

        if ($user->isDivisi()) {
            $query->where('divisi_id', $user->divisi_id);
        }

        $kegiatans = $query->latest()->get()->map(fn($k) => array_merge(
            $k->toArray(),
            ['progress' => $k->progress, 'status_label' => $k->status->label(), 'status_color' => $k->status->color()]
        ));

        return Inertia::render('Kegiatan/Index', [
            'kegiatans' => $kegiatans,
            'programs'  => Program::select('id', 'nama', 'rencana_biaya')->get(),
            'divisis'   => Divisi::select('id', 'nama')->where('status', true)->get(),
            'pilars'    => Pilar::select('id', 'nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'program_id'    => 'required|exists:programs,id',
            'divisi_id'     => 'required|exists:divisis,id',
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'required|numeric|min:0',
            'rencana_biaya' => 'required|numeric|min:0',
            'pilar_ids'     => 'nullable|array',
            'pilar_ids.*'   => 'exists:pilars,id',
        ]);

        // Budget validation
        $program = Program::findOrFail($data['program_id']);
        $usedBudget = $program->total_kegiatan_biaya;
        if (($usedBudget + $data['rencana_biaya']) > $program->rencana_biaya) {
            return back()->withErrors(['rencana_biaya' => 'Total rencana biaya kegiatan melebihi anggaran program.']);
        }

        $pilarIds = $data['pilar_ids'] ?? [];
        unset($data['pilar_ids']);

        $kegiatan = Kegiatan::create($data);
        if ($pilarIds) {
            $kegiatan->pilars()->sync($pilarIds);
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'kegiatan',
            'action'       => 'create',
            'subject_type' => Kegiatan::class,
            'subject_id'   => $kegiatan->id,
            'description'  => "Kegiatan '{$kegiatan->nama}' dibuat.",
        ]);

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        // Locked if approved
        if ($kegiatan->status === KegiatanStatus::DISETUJUI) {
            return back()->withErrors(['status' => 'Kegiatan yang sudah disetujui tidak dapat diedit langsung.']);
        }

        $data = $request->validate([
            'program_id'    => 'required|exists:programs,id',
            'divisi_id'     => 'required|exists:divisis,id',
            'nama'          => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'target_output' => 'required|numeric|min:0',
            'rencana_biaya' => 'required|numeric|min:0',
            'pilar_ids'     => 'nullable|array',
            'pilar_ids.*'   => 'exists:pilars,id',
        ]);

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
        if ($kegiatan->status === KegiatanStatus::DISETUJUI) {
            return back()->withErrors(['status' => 'Kegiatan yang sudah disetujui tidak dapat dihapus.']);
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
        if (!in_array($kegiatan->status, [KegiatanStatus::DRAFT, KegiatanStatus::REVISI])) {
            return back()->withErrors(['status' => 'Kegiatan tidak dapat diajukan dari status ini.']);
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
        $kegiatan->load(['program', 'divisi', 'pilars', 'realisasis.periode', 'files.uploadedBy']);

        return Inertia::render('Kegiatan/Show', [
            'kegiatan' => array_merge($kegiatan->toArray(), [
                'progress'     => $kegiatan->progress,
                'status_label' => $kegiatan->status->label(),
                'status_color' => $kegiatan->status->color(),
            ]),
        ]);
    }
}
