<?php

namespace App\Http\Controllers;

use App\Models\Pilar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class PilarController extends Controller
{
    public function index()
    {
        $hasNoUrut = Schema::hasColumn('pilars', 'no_urut');

        $colorPresets = collect(Pilar::colorMap())
            ->map(fn (string $warna, string $nama) => [
                'nama' => $nama,
                'warna' => strtoupper($warna),
            ])
            ->values();

        $iconPresets = collect(Pilar::iconMap())
            ->map(fn (string $icon, string $nama) => [
                'nama' => $nama,
                'icon' => $icon,
            ])
            ->values();

        $iconOptions = collect([
            ['label' => 'Kategori', 'icon' => 'category'],
            ['label' => 'Sekolah', 'icon' => 'school'],
            ['label' => 'Kesehatan', 'icon' => 'medical_services'],
            ['label' => 'Pertumbuhan', 'icon' => 'trending_up'],
            ['label' => 'Ekonomi', 'icon' => 'payments'],
            ['label' => 'Komunitas', 'icon' => 'groups'],
            ['label' => 'Lingkungan', 'icon' => 'eco'],
            ['label' => 'Jaringan', 'icon' => 'hub'],
            ['label' => 'Infrastruktur', 'icon' => 'construction'],
            ['label' => 'Relawan', 'icon' => 'volunteer_activism'],
            ['label' => 'Bangunan', 'icon' => 'apartment'],
            ['label' => 'Perpustakaan', 'icon' => 'local_library'],
            ['label' => 'Pertanian', 'icon' => 'agriculture'],
            ['label' => 'Hutan', 'icon' => 'forest'],
            ['label' => 'Teknik', 'icon' => 'engineering'],
            ['label' => 'Rumah', 'icon' => 'home_work'],
            ['label' => 'Air', 'icon' => 'water_drop'],
            ['label' => 'Listrik', 'icon' => 'bolt'],
            ['label' => 'Transportasi', 'icon' => 'commute'],
            ['label' => 'Publik', 'icon' => 'public'],
        ]);

        $pilarsQuery = Pilar::query()->withSum('programs as total_program_biaya', 'rencana_biaya');

        if ($hasNoUrut) {
            $pilarsQuery->orderBy('no_urut')->orderBy('nama');
        } else {
            $pilarsQuery->latest();
        }

        $pilars = $pilarsQuery
            ->get()
            ->map(function (Pilar $pilar) {
                $usedBudget = (float) ($pilar->total_program_biaya ?? 0);

                return array_merge($pilar->toArray(), [
                    'no_urut' => (int) ($pilar->no_urut ?? 0),
                    'total_program_biaya' => $usedBudget,
                    'sisa_anggaran' => (float) $pilar->rencana_biaya - $usedBudget,
                ]);
            });

        $summary = [
            'total_anggaran' => (float) $pilars->sum('rencana_biaya'),
            'total_terpakai' => (float) $pilars->sum('total_program_biaya'),
            'total_sisa' => (float) $pilars->sum('sisa_anggaran'),
        ];

        return Inertia::render('Pilar/Index', [
            'pilars' => $pilars,
            'summary' => $summary,
            'colorPresets' => $colorPresets,
            'iconPresets' => $iconPresets,
            'iconOptions' => $iconOptions,
        ]);
    }

    public function store(Request $request)
    {
        $hasNoUrut = Schema::hasColumn('pilars', 'no_urut');

        $rules = [
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'rencana_biaya' => 'required|numeric|min:0',
            'warna' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => 'required|string|max:32',
        ];

        if ($hasNoUrut) {
            $rules['no_urut'] = 'required|integer|min:0';
        }

        $data = $request->validate($rules);
        Pilar::create($data);
        return back()->with('success', 'Pilar berhasil ditambahkan.');
    }

    public function update(Request $request, Pilar $pilar)
    {
        $hasNoUrut = Schema::hasColumn('pilars', 'no_urut');

        $rules = [
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'rencana_biaya' => 'required|numeric|min:0',
            'warna' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => 'required|string|max:32',
        ];

        if ($hasNoUrut) {
            $rules['no_urut'] = 'required|integer|min:0';
        }

        $data = $request->validate($rules);

        $usedBudget = (float) $pilar->programs()->sum('rencana_biaya');
        if ((float) $data['rencana_biaya'] < $usedBudget) {
            return back()->withErrors(['rencana_biaya' => 'Anggaran pilar tidak boleh lebih kecil dari total anggaran program di dalamnya.']);
        }

        $pilar->update($data);
        return back()->with('success', 'Pilar berhasil diperbarui.');
    }

    public function destroy(Pilar $pilar)
    {
        if ($pilar->programs()->exists()) {
            return back()->withErrors(['pilar' => 'Pilar tidak dapat dihapus karena masih memiliki program.']);
        }

        $pilar->delete();
        return back()->with('success', 'Pilar berhasil dihapus.');
    }
}
