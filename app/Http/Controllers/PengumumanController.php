<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengumumanController extends Controller
{
    public function index()
    {
        return Inertia::render('Pengumuman/Index', [
            'pengumumans' => Pengumuman::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'nullable|string',
            'image' => 'nullable|string|max:1000',
            'prioritas' => 'required|in:penting,info,umum',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        Pengumuman::create($data);

        return back()->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'nullable|string',
            'image' => 'nullable|string|max:1000',
            'prioritas' => 'required|in:penting,info,umum',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $pengumuman->update($data);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
