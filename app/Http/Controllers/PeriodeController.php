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
            'periodes' => Periode::orderBy('tahun', 'desc')->orderBy('bulan')->get(),
            'bulan_options' => $this->monthOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun'     => 'required|integer|min:2020|max:2099',
            'bulan'     => 'required|integer|min:1|max:12',
            'status'    => 'required|boolean',
        ]);

        $data['triwulan'] = $this->monthLabel((int) $data['bulan']);

        Periode::create($data);
        return back()->with('success', 'Periode bulanan berhasil ditambahkan.');
    }

    public function update(Request $request, Periode $periode)
    {
        $data = $request->validate([
            'tahun'     => 'required|integer|min:2020|max:2099',
            'bulan'     => 'required|integer|min:1|max:12',
            'status'    => 'required|boolean',
        ]);

        $data['triwulan'] = $this->monthLabel((int) $data['bulan']);

        $periode->update($data);
        return back()->with('success', 'Periode bulanan berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        $periode->delete();
        return back()->with('success', 'Periode berhasil dihapus.');
    }

    private function monthOptions(): array
    {
        return [
            ['value' => 1, 'label' => 'Jan'],
            ['value' => 2, 'label' => 'Feb'],
            ['value' => 3, 'label' => 'Mar'],
            ['value' => 4, 'label' => 'Apr'],
            ['value' => 5, 'label' => 'Mei'],
            ['value' => 6, 'label' => 'Jun'],
            ['value' => 7, 'label' => 'Jul'],
            ['value' => 8, 'label' => 'Agu'],
            ['value' => 9, 'label' => 'Sep'],
            ['value' => 10, 'label' => 'Okt'],
            ['value' => 11, 'label' => 'Nov'],
            ['value' => 12, 'label' => 'Des'],
        ];
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
