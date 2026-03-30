<?php

namespace App\Http\Controllers;

use App\Enums\KegiatanStatus;
use App\Models\ActivityLog;
use App\Models\FileLampiran;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\Realisasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RealisasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Realisasi::with(['kegiatan.program', 'kegiatan.divisi', 'periode', 'files'])
            ->latest();

        $query->whereHas('kegiatan', function ($q) use ($user) {
            $q->whereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('activity_logs')
                    ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                    ->where('activity_logs.subject_type', Kegiatan::class)
                    ->where('activity_logs.user_id', $user->id)
                    ->whereIn('activity_logs.action', ['create', 'submit']);
            });
        });

        $realisasis = $query->get()->map(fn(Realisasi $r) => array_merge($r->toArray(), [
            'progress' => $r->kegiatan ? $r->kegiatan->progress : 0,
        ]));

        $kegiatans = Kegiatan::where('status', KegiatanStatus::DISETUJUI)
            ->whereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('activity_logs')
                    ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                    ->where('activity_logs.subject_type', Kegiatan::class)
                    ->where('activity_logs.user_id', $user->id)
                    ->whereIn('activity_logs.action', ['create', 'submit']);
            })
            ->with(['program:id,nama', 'divisi:id,nama', 'pilars:id,nama'])
            ->select('id', 'program_id', 'divisi_id', 'nama', 'deskripsi', 'target_output', 'satuan', 'rencana_biaya', 'status')
            ->get();

        $programIds = $kegiatans->pluck('program_id')->filter()->unique()->values();
        $programSpendings = collect();

        if ($programIds->isNotEmpty()) {
            $programSpendings = Realisasi::query()
                ->join('kegiatans', 'kegiatans.id', '=', 'realisasis.kegiatan_id')
                ->whereIn('kegiatans.program_id', $programIds)
                ->selectRaw('kegiatans.program_id, realisasis.periode_id, COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_terpakai')
                ->groupBy('kegiatans.program_id', 'realisasis.periode_id')
                ->get()
                ->map(fn($row) => [
                    'program_id' => (int) $row->program_id,
                    'periode_id' => (int) $row->periode_id,
                    'total_terpakai' => (float) $row->total_terpakai,
                ])
                ->values();
        }

        return Inertia::render('Realisasi/Index', [
            'realisasis' => $realisasis,
            'kegiatans'  => $kegiatans,
            'programSpendings' => $programSpendings,
            'periodes'   => Periode::where('status', true)->orderBy('tahun', 'desc')->orderBy('triwulan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'kegiatan_id'      => 'required|exists:kegiatans,id',
            'periode_id'       => 'required|exists:periodes,id',
            'realisasi_output' => 'nullable|numeric|min:0',
            'realisasi_biaya'  => 'nullable|numeric|min:0',
            'keterangan'       => 'nullable|string',
            'evidence_files'   => 'nullable|array',
            'evidence_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'laporan_files'    => 'nullable|array',
            'laporan_files.*'  => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $kegiatan = Kegiatan::findOrFail($data['kegiatan_id']);

        if (!$this->ownsKegiatan($user, $kegiatan)) {
            return back()->withErrors(['kegiatan_id' => 'Anda hanya dapat menginput realisasi untuk kegiatan yang Anda ajukan.']);
        }

        if ($kegiatan->status !== KegiatanStatus::DISETUJUI) {
            return back()->withErrors(['kegiatan_id' => 'Hanya kegiatan yang disetujui yang dapat diisi realisasi.']);
        }

        $exists = Realisasi::where('kegiatan_id', $data['kegiatan_id'])
            ->where('periode_id', $data['periode_id'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['periode_id' => 'Realisasi untuk kegiatan dan triwulan ini sudah ada. Silakan gunakan edit.']);
        }

        // Alert: realisasi biaya > rencana biaya
        if (isset($data['realisasi_biaya']) && $data['realisasi_biaya'] > $kegiatan->rencana_biaya) {
            return back()->withErrors(['realisasi_biaya' => 'Realisasi biaya melebihi rencana biaya kegiatan.'])->with('warning', 'Peringatan: Realisasi biaya melebihi anggaran!');
        }

        $attachments = [
            'evidence_files' => $data['evidence_files'] ?? [],
            'laporan_files' => $data['laporan_files'] ?? [],
        ];
        unset($data['evidence_files'], $data['laporan_files']);

        $realisasi = Realisasi::create($data);
        $this->storeFiles($attachments['evidence_files'], 'evidence', $realisasi->id);
        $this->storeFiles($attachments['laporan_files'], 'laporan', $realisasi->id);

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
        $user = Auth::user();

        if (!$this->ownsKegiatan($user, $realisasi->kegiatan)) {
            return back()->withErrors(['status' => 'Anda hanya dapat mengubah realisasi untuk kegiatan yang Anda ajukan.']);
        }

        $data = $request->validate([
            'realisasi_output' => 'nullable|numeric|min:0',
            'realisasi_biaya'  => 'nullable|numeric|min:0',
            'keterangan'       => 'nullable|string',
            'evidence_files'   => 'nullable|array',
            'evidence_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'laporan_files'    => 'nullable|array',
            'laporan_files.*'  => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
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

        $attachments = [
            'evidence_files' => $data['evidence_files'] ?? [],
            'laporan_files' => $data['laporan_files'] ?? [],
        ];
        unset($data['evidence_files'], $data['laporan_files']);

        $realisasi->update($data);
        $this->storeFiles($attachments['evidence_files'], 'evidence', $realisasi->id);
        $this->storeFiles($attachments['laporan_files'], 'laporan', $realisasi->id);
        return back()->with('success', 'Realisasi berhasil diperbarui.');
    }

    public function destroy(Realisasi $realisasi)
    {
        $user = Auth::user();

        if (!$this->ownsKegiatan($user, $realisasi->kegiatan)) {
            return back()->withErrors(['status' => 'Anda hanya dapat menghapus realisasi untuk kegiatan yang Anda ajukan.']);
        }

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

    private function ownsKegiatan(User $user, Kegiatan $kegiatan): bool
    {
        return ActivityLog::where('subject_type', Kegiatan::class)
            ->where('subject_id', $kegiatan->id)
            ->where('user_id', $user->id)
            ->whereIn('action', ['create', 'submit'])
            ->exists();
    }

    private function storeFiles(array $files, string $kategori, int $realisasiId): void
    {
        foreach ($files as $file) {
            $path = $file->store('uploads/' . date('Y/m'), 'public');

            FileLampiran::create([
                'realisasi_id' => $realisasiId,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'kategori' => $kategori,
                'uploaded_by' => Auth::id(),
            ]);
        }
    }
}
