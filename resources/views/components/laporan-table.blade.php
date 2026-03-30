@php
    $laporanTahun = request('laporan_tahun');
    $laporanBidang = request('laporan_bidang');
    $laporanSearch = trim((string) request('laporan_search', ''));

    $bidangs = \App\Models\BidangCsr::query()
        ->orderBy('nama')
        ->get(['id', 'nama']);

    $laporans = \App\Models\LaporanCsr::query()
        ->with([
            'bidang:id,nama',
            'perusahaan:id,nama',
            'program:id,nama',
        ])
        ->when($laporanTahun, fn ($query) => $query->where('tahun', $laporanTahun))
        ->when($laporanBidang, fn ($query) => $query->where('bidang_id', $laporanBidang))
        ->when($laporanSearch !== '', function ($query) use ($laporanSearch) {
            $keyword = '%' . str_replace(' ', '%', $laporanSearch) . '%';

            $query->where(function ($nested) use ($keyword) {
                $nested->where('lokasi', 'like', $keyword)
                    ->orWhereHas('program', fn ($programQuery) => $programQuery->where('nama', 'like', $keyword))
                    ->orWhereHas('perusahaan', fn ($companyQuery) => $companyQuery->where('nama', 'like', $keyword))
                    ->orWhereHas('bidang', fn ($bidangQuery) => $bidangQuery->where('nama', 'like', $keyword));
            });
        })
        ->orderByDesc('tahun')
        ->orderByDesc('id')
        ->paginate(10, ['*'], 'laporan_page')
        ->withQueryString();

    $tahuns = \App\Models\LaporanCsr::query()
        ->select('tahun')
        ->distinct()
        ->orderByDesc('tahun')
        ->pluck('tahun');
@endphp

<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <h3 class="text-xl font-extrabold tracking-tight text-gray-900">Tabel Laporan PPM</h3>

        <form method="GET" action="{{ route('home') }}#laporan" class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 md:w-auto">
            <select name="laporan_tahun" class="input min-w-[150px]">
                <option value="">Semua Tahun</option>
                @foreach($tahuns as $tahun)
                    <option value="{{ $tahun }}" @selected((string) $laporanTahun === (string) $tahun)>{{ $tahun }}</option>
                @endforeach
            </select>

            <select name="laporan_bidang" class="input min-w-[190px]">
                <option value="">Semua Bidang</option>
                @foreach($bidangs as $bidang)
                    <option value="{{ $bidang->id }}" @selected((string) $laporanBidang === (string) $bidang->id)>{{ $bidang->nama }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <input
                    name="laporan_search"
                    type="text"
                    value="{{ $laporanSearch }}"
                    class="input min-w-[220px]"
                    placeholder="Cari perusahaan/program"
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
                    <th class="px-4 py-3 text-left font-semibold">Tahun</th>
                    <th class="px-4 py-3 text-left font-semibold">Bidang</th>
                    <th class="px-4 py-3 text-left font-semibold">Perusahaan</th>
                    <th class="px-4 py-3 text-left font-semibold">Program</th>
                    <th class="px-4 py-3 text-left font-semibold">Lokasi</th>
                    <th class="px-4 py-3 text-right font-semibold">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($laporans as $laporan)
                    <tr class="transition hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3">{{ $laporan->tahun }}</td>
                        <td class="px-4 py-3 font-medium text-[var(--color-primary)]">{{ $laporan->bidang->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->perusahaan->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->program->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->lokasi ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format((float) $laporan->nominal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data laporan ditemukan.</td>
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
