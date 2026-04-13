@php
    $laporanTahun = request('laporan_tahun');
    $laporanPilar = request('laporan_pilar');
    $laporanSearch = trim((string) request('laporan_search', ''));
    $pilarColorMap = \App\Models\Pilar::colorMap();

    $pilars = \App\Models\Pilar::query()
        ->orderBy('nama')
        ->get(['id', 'nama']);

    $laporans = \Illuminate\Support\Facades\DB::table('pilars')
        ->leftJoin('programs', 'programs.pilar_id', '=', 'pilars.id')
        ->leftJoin('kegiatans', 'kegiatans.program_id', '=', 'programs.id')
        ->leftJoin('realisasis', 'realisasis.kegiatan_id', '=', 'kegiatans.id')
        ->leftJoin('periodes', 'periodes.id', '=', 'realisasis.periode_id')
        ->when($laporanTahun, fn ($query) => $query->where('periodes.tahun', $laporanTahun))
        ->when($laporanPilar, fn ($query) => $query->where('pilars.id', $laporanPilar))
        ->when($laporanSearch !== '', function ($query) use ($laporanSearch) {
            $keyword = '%' . str_replace(' ', '%', $laporanSearch) . '%';
            $query->where('pilars.nama', 'like', $keyword);
        })
        ->select(
            'pilars.id',
            'pilars.nama',
            'pilars.rencana_biaya',
            \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
        )
        ->groupBy('pilars.id', 'pilars.nama', 'pilars.rencana_biaya')
        ->orderBy('pilars.nama')
        ->paginate(10, ['*'], 'laporan_page')
        ->withQueryString();

    $tahuns = \App\Models\Periode::query()
        ->select('tahun')
        ->distinct()
        ->orderByDesc('tahun')
        ->pluck('tahun');
@endphp

<div>
    <div class="mb-6  gap-4 md:flex-row md:items-center md:justify-between">
        <h3 class="text-xl font-extrabold tracking-tight text-gray-900">Tabel Laporan PPM</h3>
        <br>

        <form method="GET" action="{{ route('home') }}#laporan" class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 md:w-auto">
            <select name="laporan_tahun" class="input min-w-[150px]">
                <option value="">Semua Tahun</option>
                @foreach($tahuns as $tahun)
                    <option value="{{ $tahun }}" @selected((string) $laporanTahun === (string) $tahun)>{{ $tahun }}</option>
                @endforeach
            </select>

            <select name="laporan_pilar" class="input min-w-[190px]">
                <option value="">Semua Pilar</option>
                @foreach($pilars as $pilar)
                    <option value="{{ $pilar->id }}" @selected((string) $laporanPilar === (string) $pilar->id)>{{ $pilar->nama }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <input
                    name="laporan_search"
                    type="text"
                    value="{{ $laporanSearch }}"
                    class="input min-w-[220px]"
                    placeholder="Cari nama pilar"
                />
                <button type="submit" class="btn-primary whitespace-nowrap">Filter</button>
                <a href="{{ route('home') }}#laporan" class="btn-secondary whitespace-nowrap">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-[#1a1a1a] text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Pilar</th>
                    <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Anggaran</th>
                    <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Realisasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($laporans as $laporan)
                    <tr class="transition hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-700">
                            @php $pilarColor = $pilarColorMap[$laporan->nama ?? ''] ?? '#64748b'; @endphp
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background-color: {{ $pilarColor }}1A; color: {{ $pilarColor }};">
                                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background-color: {{ $pilarColor }};"></span>
                                {{ $laporan->nama ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">Rp {{ number_format((float) ($laporan->rencana_biaya ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">Rp {{ number_format((float) ($laporan->total_realisasi ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">Tidak ada data pilar ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-col items-start justify-between gap-3 text-sm text-gray-600 sm:flex-row sm:items-center">
        <div>
            Menampilkan {{ $laporans->firstItem() ?? 0 }} - {{ $laporans->lastItem() ?? 0 }} dari {{ $laporans->total() }} data
        </div>
        <div>
            {{ $laporans->fragment('laporan')->links() }}
        </div>
    </div>
</div>
