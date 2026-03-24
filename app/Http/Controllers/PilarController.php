<?php

namespace App\Http\Controllers;

use App\Models\Pilar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PilarController extends Controller
{
    public function index()
    {
        return Inertia::render('Pilar/Index', [
            'pilars' => Pilar::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);
        Pilar::create($data);
        return back()->with('success', 'Pilar berhasil ditambahkan.');
    }

    public function update(Request $request, Pilar $pilar)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);
        $pilar->update($data);
        return back()->with('success', 'Pilar berhasil diperbarui.');
    }

    public function destroy(Pilar $pilar)
    {
        $pilar->delete();
        return back()->with('success', 'Pilar berhasil dihapus.');
    }
}
