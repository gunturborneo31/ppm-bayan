<?php

namespace App\Http\Controllers;

use App\Enums\KegiatanStatus;
use App\Models\ActivityLog;
use App\Models\FileLampiran;
use App\Models\Kegiatan;
use App\Models\KegiatanLokasi;
use App\Models\Periode;
use App\Models\Realisasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RealisasiController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $isSuperadmin = $user && $user->isSuperadmin();

        $query = Realisasi::with(['kegiatan.program.pilar', 'kegiatan.divisi', 'kegiatanLokasi', 'periode', 'files'])
            ->orderByDesc('tanggal_realisasi')
            ->orderByDesc('id');

        if (!$isSuperadmin) {
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
        }

        $orderedRealisasis = $query->get();

        $realisasis = $orderedRealisasis->map(fn(Realisasi $r) => array_merge($r->toArray(), [
            'progress' => $r->kegiatan ? $r->kegiatan->progress : 0,
            'tanggal_realisasi_label' => $r->tanggal_realisasi ? Carbon::parse($r->tanggal_realisasi)->format('Y-m-d') : null,
        ]))->values();

        $kegiatansQuery = Kegiatan::where('status', KegiatanStatus::DISETUJUI)
            ->with([
                'program:id,nama,pilar_id',
                'program.pilar:id,nama,warna',
                'divisi:id,nama',
                'lokasis:id,kegiatan_id,lokasi,target_output,satuan,rencana_biaya',
            ])
            ->select('id', 'program_id', 'divisi_id', 'nama', 'deskripsi', 'target_output', 'satuan', 'rencana_biaya', 'status');

        if (!$isSuperadmin) {
            $kegiatansQuery->whereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('activity_logs')
                    ->whereColumn('activity_logs.subject_id', 'kegiatans.id')
                    ->where('activity_logs.subject_type', Kegiatan::class)
                    ->where('activity_logs.user_id', $user->id)
                    ->whereIn('activity_logs.action', ['create', 'submit']);
            });
        }

        $kegiatans = $kegiatansQuery->get();

        $programSpendings = $orderedRealisasis
            ->groupBy(fn (Realisasi $row) => $row->kegiatan_lokasi_id ? 'lokasi:' . $row->kegiatan_lokasi_id : 'kegiatan:' . $row->kegiatan_id)
            ->map(fn ($items) => $items->first())
            ->filter(fn (Realisasi $row) => $row->kegiatan?->program_id)
            ->groupBy(fn (Realisasi $row) => (int) $row->kegiatan->program_id)
            ->map(fn ($items, $programId) => [
                'program_id' => (int) $programId,
                'total_terpakai' => (float) $items->sum('realisasi_biaya'),
            ])
            ->values();

        return Inertia::render('Realisasi/Index', [
            'realisasis' => $realisasis,
            'kegiatans'  => $kegiatans,
            'isSuperadmin' => $isSuperadmin,
            'programSpendings' => $programSpendings,
            'periodes'   => Periode::where('status', true)->orderBy('tahun', 'desc')->orderBy('bulan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'kegiatan_id'      => 'required|exists:kegiatans,id',
            'kegiatan_lokasi_id' => 'required|exists:kegiatan_lokasis,id',
            'tanggal_realisasi' => 'required|date',
            'realisasi_output' => 'nullable|numeric|min:0',
            'realisasi_biaya'  => 'nullable|numeric|min:0',
            'keterangan'       => 'nullable|string',
            'evidence_files'   => 'nullable|array',
            'evidence_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'laporan_files'    => 'nullable|array',
            'laporan_files.*'  => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $kegiatan = Kegiatan::findOrFail($data['kegiatan_id']);
        $kegiatanLokasi = KegiatanLokasi::findOrFail($data['kegiatan_lokasi_id']);

        if ((int) $kegiatanLokasi->kegiatan_id !== (int) $kegiatan->id) {
            return back()->withErrors(['kegiatan_lokasi_id' => 'Lokasi tidak sesuai dengan kegiatan yang dipilih.']);
        }

        if (!$this->ownsKegiatan($user, $kegiatan)) {
            return back()->withErrors(['kegiatan_id' => 'Anda hanya dapat menginput realisasi untuk kegiatan yang Anda ajukan.']);
        }

        if ($kegiatan->status !== KegiatanStatus::DISETUJUI) {
            return back()->withErrors(['kegiatan_id' => 'Hanya kegiatan yang disetujui yang dapat diisi realisasi.']);
        }

        $periode = $this->resolvePeriodeFromDate($data['tanggal_realisasi']);

        $data['periode_id'] = $periode->id;

        // Alert: realisasi biaya > rencana biaya
        if (isset($data['realisasi_biaya']) && $data['realisasi_biaya'] > $kegiatanLokasi->rencana_biaya) {
            return back()->withErrors(['realisasi_biaya' => 'Realisasi biaya melebihi rencana biaya lokasi kegiatan.'])->with('warning', 'Peringatan: Realisasi biaya melebihi anggaran lokasi!');
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
            'description'  => "Realisasi untuk kegiatan '{$kegiatan->nama}' lokasi '{$kegiatanLokasi->lokasi}' tanggal " . Carbon::parse($realisasi->tanggal_realisasi)->format('d/m/Y') . ' ditambahkan.',
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
            'kegiatan_lokasi_id' => 'nullable|exists:kegiatan_lokasis,id',
            'tanggal_realisasi' => 'required|date',
            'realisasi_output' => 'nullable|numeric|min:0',
            'realisasi_biaya'  => 'nullable|numeric|min:0',
            'keterangan'       => 'nullable|string',
            'evidence_files'   => 'nullable|array',
            'evidence_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'laporan_files'    => 'nullable|array',
            'laporan_files.*'  => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $kegiatan = $realisasi->kegiatan;
        $kegiatanLokasi = $realisasi->kegiatanLokasi;

        if (isset($data['kegiatan_lokasi_id']) && (int) $data['kegiatan_lokasi_id'] !== (int) ($realisasi->kegiatan_lokasi_id ?? 0)) {
            $candidateLokasi = KegiatanLokasi::findOrFail($data['kegiatan_lokasi_id']);
            if ((int) $candidateLokasi->kegiatan_id !== (int) $kegiatan->id) {
                return back()->withErrors(['kegiatan_lokasi_id' => 'Lokasi tidak sesuai dengan kegiatan realisasi.']);
            }

            $kegiatanLokasi = $candidateLokasi;
            $data['kegiatan_lokasi_id'] = $candidateLokasi->id;
        }

        $periode = $this->resolvePeriodeFromDate($data['tanggal_realisasi']);

        $data['periode_id'] = $periode->id;

        $maxBiaya = (float) ($kegiatanLokasi?->rencana_biaya ?? $kegiatan->rencana_biaya);
        if (isset($data['realisasi_biaya']) && $data['realisasi_biaya'] > $maxBiaya) {
            return back()->withErrors(['realisasi_biaya' => 'Realisasi biaya melebihi rencana biaya lokasi kegiatan.']);
        }

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'module'       => 'realisasi',
            'action'       => 'update',
            'subject_type' => Realisasi::class,
            'subject_id'   => $realisasi->id,
            'description'  => "Realisasi untuk kegiatan '{$kegiatan->nama}'" . ($kegiatanLokasi ? " lokasi '{$kegiatanLokasi->lokasi}'" : '') . " tanggal {$data['tanggal_realisasi']} diperbarui.",
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

    private function resolvePeriodeFromDate(string $tanggalRealisasi): Periode
    {
        $date = Carbon::parse($tanggalRealisasi);

        $existing = Periode::query()
            ->where('tahun', $date->year)
            ->where('bulan', $date->month)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Periode::create([
            'tahun' => $date->year,
            'bulan' => $date->month,
            'triwulan' => $this->monthLabel($date->month),
            'status' => true,
        ]);
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
