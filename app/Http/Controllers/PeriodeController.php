<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeriodeController extends Controller
{
    public function index()
    {
        return Inertia::render('Periode/Index', [
            'periodes' => Periode::orderBy('tahun', 'desc')->orderBy('triwulan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun'     => 'required|integer|min:2020|max:2099',
            'triwulan'  => 'required|in:Q1,Q2,Q3,Q4',
            'status'    => 'boolean',
        ]);
        Periode::create($data);
        return back()->with('success', 'Periode berhasil ditambahkan.');
    }

    public function update(Request $request, Periode $periode)
    {
        $data = $request->validate([
            'tahun'     => 'required|integer|min:2020|max:2099',
            'triwulan'  => 'required|in:Q1,Q2,Q3,Q4',
            'status'    => 'boolean',
        ]);
        $periode->update($data);
        return back()->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        $periode->delete();
        return back()->with('success', 'Periode berhasil dihapus.');
    }
}
