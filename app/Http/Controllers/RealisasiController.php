<?php

namespace App\Http\Controllers;

use App\Enums\KegiatanStatus;
use App\Models\ActivityLog;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\Realisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RealisasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Realisasi::with(['kegiatan.program', 'kegiatan.divisi', 'periode'])
            ->latest();

        if ($user->isDivisi()) {
            $query->whereHas('kegiatan', fn($q) => $q->where('divisi_id', $user->divisi_id));
        }

        $realisasis = $query->get()->map(fn($r) => array_merge($r->toArray(), [
            'progress' => $r->kegiatan ? $r->kegiatan->progress : 0,
        ]));

        $kegiatans = Kegiatan::where('status', KegiatanStatus::DISETUJUI)
            ->when($user->isDivisi(), fn($q) => $q->where('divisi_id', $user->divisi_id))
            ->select('id', 'nama', 'target_output', 'rencana_biaya')
            ->get();

        return Inertia::render('Realisasi/Index', [
            'realisasis' => $realisasis,
            'kegiatans'  => $kegiatans,
            'periodes'   => Periode::where('status', true)->orderBy('tahun', 'desc')->orderBy('triwulan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kegiatan_id'      => 'required|exists:kegiatans,id',
            'periode_id'       => 'required|exists:periodes,id',
            'realisasi_output' => 'nullable|numeric|min:0',
            'realisasi_biaya'  => 'nullable|numeric|min:0',
            'keterangan'       => 'nullable|string',
        ]);

        $kegiatan = Kegiatan::findOrFail($data['kegiatan_id']);
        if ($kegiatan->status !== KegiatanStatus::DISETUJUI) {
            return back()->withErrors(['kegiatan_id' => 'Hanya kegiatan yang disetujui yang dapat diisi realisasi.']);
        }

        // Alert: realisasi biaya > rencana biaya
        if (isset($data['realisasi_biaya']) && $data['realisasi_biaya'] > $kegiatan->rencana_biaya) {
            return back()->withErrors(['realisasi_biaya' => 'Realisasi biaya melebihi rencana biaya kegiatan.'])->with('warning', 'Peringatan: Realisasi biaya melebihi anggaran!');
        }

        $realisasi = Realisasi::create($data);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'realisasi',
            'action'       => 'create',
            'subject_type' => Realisasi::class,
            'subject_id'   => $realisasi->id,
            'description'  => "Realisasi untuk kegiatan '{$kegiatan->nama}' ditambahkan.",
        ]);

        return back()->with('success', 'Realisasi berhasil ditambahkan.');
    }

    public function update(Request $request, Realisasi $realisasi)
    {
        $data = $request->validate([
            'realisasi_output' => 'nullable|numeric|min:0',
            'realisasi_biaya'  => 'nullable|numeric|min:0',
            'keterangan'       => 'nullable|string',
        ]);

        $kegiatan = $realisasi->kegiatan;
        if (isset($data['realisasi_biaya']) && $data['realisasi_biaya'] > $kegiatan->rencana_biaya) {
            return back()->withErrors(['realisasi_biaya' => 'Realisasi biaya melebihi rencana biaya kegiatan.']);
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'realisasi',
            'action'       => 'update',
            'subject_type' => Realisasi::class,
            'subject_id'   => $realisasi->id,
            'description'  => "Realisasi untuk kegiatan '{$kegiatan->nama}' diperbarui.",
        ]);

        $realisasi->update($data);
        return back()->with('success', 'Realisasi berhasil diperbarui.');
    }

    public function destroy(Realisasi $realisasi)
    {
        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'realisasi',
            'action'       => 'delete',
            'subject_type' => Realisasi::class,
            'subject_id'   => $realisasi->id,
            'description'  => "Realisasi dihapus.",
        ]);

        $realisasi->delete();
        return back()->with('success', 'Realisasi berhasil dihapus.');
    }
}
