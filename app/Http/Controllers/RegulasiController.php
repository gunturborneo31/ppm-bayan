<?php

namespace App\Http\Controllers;

use App\Models\Regulasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegulasiController extends Controller
{
    public function index()
    {
        return Inertia::render('Regulasi/Index', [
            'regulasis' => Regulasi::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'nomor_regulasi' => 'required|string|max:255',
            'link_dokumen' => 'nullable|url|max:1000',
            'dokumen_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('dokumen_file')) {
            $path = $request->file('dokumen_file')->store('regulasi/' . date('Y/m'), 'public');
            $data['link_dokumen'] = asset('storage/' . $path);
        }

        unset($data['dokumen_file']);

        Regulasi::create($data);

        return back()->with('success', 'Dasar hukum pelaksanaan berhasil ditambahkan.');
    }

    public function update(Request $request, Regulasi $regulasi)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'nomor_regulasi' => 'required|string|max:255',
            'link_dokumen' => 'nullable|url|max:1000',
            'dokumen_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('dokumen_file')) {
            $this->deleteStoredDocument($regulasi->link_dokumen);

            $path = $request->file('dokumen_file')->store('regulasi/' . date('Y/m'), 'public');
            $data['link_dokumen'] = asset('storage/' . $path);
        } elseif (($data['link_dokumen'] ?? null) !== $regulasi->link_dokumen) {
            $this->deleteStoredDocument($regulasi->link_dokumen);
        }

        unset($data['dokumen_file']);

        $regulasi->update($data);

        return back()->with('success', 'Dasar hukum pelaksanaan berhasil diperbarui.');
    }

    public function destroy(Regulasi $regulasi)
    {
        $this->deleteStoredDocument($regulasi->link_dokumen);
        $regulasi->delete();

        return back()->with('success', 'Dasar hukum pelaksanaan berhasil dihapus.');
    }

    private function deleteStoredDocument(?string $url): void
    {
        if (!$url) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (Str::startsWith($path, '/storage/')) {
            $relativePath = Str::after($path, '/storage/');
            Storage::disk('public')->delete($relativePath);
        }
    }
}
