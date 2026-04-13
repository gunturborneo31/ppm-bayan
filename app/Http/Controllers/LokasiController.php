<?php

namespace App\Http\Controllers;

use App\Models\KegiatanLokasi;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LokasiController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $lokasis = Lokasi::query()
            ->select(['id', 'nama', 'created_at'])
            ->selectSub(function ($query) {
                $query->from('kegiatan_lokasis')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('kegiatan_lokasis.lokasi', 'lokasis.nama');
            }, 'kegiatan_lokasis_count');

        if ($search !== '') {
            $lokasis->where('nama', 'like', '%' . $search . '%');
        }

        $lokasis = $lokasis
            ->orderBy('nama')
            ->get();

        return Inertia::render('Lokasi/Index', [
            'lokasis' => $lokasis,
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'nama' => trim((string) $request->input('nama')),
        ]);

        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:lokasis,nama',
        ]);

        Lokasi::create($data);

        return back()->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function update(Request $request, Lokasi $lokasi)
    {
        $request->merge([
            'nama' => trim((string) $request->input('nama')),
        ]);

        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:lokasis,nama,' . $lokasi->id,
        ]);

        $newName = $data['nama'];
        $oldName = $lokasi->nama;

        DB::transaction(function () use ($lokasi, $oldName, $newName) {
            $lokasi->update(['nama' => $newName]);

            if ($oldName !== $newName) {
                KegiatanLokasi::query()
                    ->where('lokasi', $oldName)
                    ->update(['lokasi' => $newName]);
            }
        });

        return back()->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Lokasi $lokasi)
    {
        $usageCount = KegiatanLokasi::query()
            ->where('lokasi', $lokasi->nama)
            ->count();

        if ($usageCount > 0) {
            return back()->withErrors([
                'lokasi' => 'Lokasi tidak dapat dihapus karena masih digunakan pada kegiatan.',
            ]);
        }

        $lokasi->delete();

        return back()->with('success', 'Lokasi berhasil dihapus.');
    }

    public function merge(Request $request, Lokasi $lokasi)
    {
        $data = $request->validate([
            'target_id' => 'required|exists:lokasis,id',
        ]);

        $target = Lokasi::query()->findOrFail($data['target_id']);

        if ((int) $target->id === (int) $lokasi->id) {
            return back()->withErrors([
                'target_id' => 'Lokasi tujuan harus berbeda.',
            ]);
        }

        DB::transaction(function () use ($lokasi, $target) {
            KegiatanLokasi::query()
                ->where('lokasi', $lokasi->nama)
                ->update(['lokasi' => $target->nama]);

            $lokasi->delete();
        });

        return back()->with('success', 'Lokasi berhasil digabungkan.');
    }
}