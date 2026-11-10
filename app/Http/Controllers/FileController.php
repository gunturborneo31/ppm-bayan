<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FileLampiran;
use App\Models\Kegiatan;
use App\Models\Realisasi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'kategori'     => 'required|in:evidence,laporan',
            'kegiatan_id'  => 'nullable|exists:kegiatans,id',
            'realisasi_id' => 'nullable|exists:realisasis,id',
        ]);

        $kegiatanId = $request->kegiatan_id
            ?? ($request->realisasi_id ? Realisasi::find($request->realisasi_id)?->kegiatan_id : null);
        if (!$this->canManage($kegiatanId)) {
            abort(403, 'Anda hanya dapat mengunggah file untuk kegiatan yang Anda ajukan.');
        }

        $file = $request->file('file');
        $path = $file->store('uploads/' . date('Y/m'), 'public');

        $lampiran = FileLampiran::create([
            'kegiatan_id'  => $request->kegiatan_id,
            'realisasi_id' => $request->realisasi_id,
            'file_path'    => $path,
            'file_name'    => $file->getClientOriginalName(),
            'file_type'    => $file->getMimeType(),
            'file_size'    => $file->getSize(),
            'kategori'     => $request->kategori,
            'uploaded_by'  => Auth::id(),
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'module'      => 'file',
            'action'      => 'upload',
            'subject_type'=> FileLampiran::class,
            'subject_id'  => $lampiran->id,
            'description' => "File '{$file->getClientOriginalName()}' diunggah.",
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function download(FileLampiran $file): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($file->file_path), 404);
        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    public function preview(FileLampiran $file): Response
    {
        abort_unless(Storage::disk('public')->exists($file->file_path), 404);

        return Storage::disk('public')->response($file->file_path, $file->file_name, [
            'Content-Disposition' => 'inline; filename="' . addslashes($file->file_name) . '"',
        ]);
    }

    public function destroy(FileLampiran $file)
    {
        $user = Auth::user();
        if (!$user->isSuperadmin() && (int) $file->uploaded_by !== (int) $user->id) {
            abort(403, 'Anda hanya dapat menghapus file yang Anda unggah.');
        }

        Storage::disk('public')->delete($file->file_path);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'module'      => 'file',
            'action'      => 'delete',
            'subject_type'=> FileLampiran::class,
            'subject_id'  => $file->id,
            'description' => "File '{$file->file_name}' dihapus.",
        ]);

        $file->delete();
        return back()->with('success', 'File berhasil dihapus.');
    }

    // Superadmin boleh semua; user lain hanya untuk kegiatan yang ia ajukan.
    private function canManage(?int $kegiatanId): bool
    {
        $user = Auth::user();
        if ($user->isSuperadmin()) {
            return true;
        }
        if (!$kegiatanId) {
            return false;
        }

        return ActivityLog::where('subject_type', Kegiatan::class)
            ->where('subject_id', $kegiatanId)
            ->where('user_id', $user->id)
            ->whereIn('action', ['create', 'submit'])
            ->exists();
    }
}
