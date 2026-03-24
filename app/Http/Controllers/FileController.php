<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FileLampiran;
use Illuminate\Http\Request;
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

    public function destroy(FileLampiran $file)
    {
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
}
