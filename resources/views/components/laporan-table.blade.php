@php
    $laporanTahun = request('laporan_tahun');
    $laporanDivisi = request('laporan_divisi');
    $laporanSearch = trim((string) request('laporan_search', ''));

    $divisis = \App\Models\Divisi::query()
        ->orderBy('nama')
        ->get(['id', 'nama']);

    $laporans = \App\Models\Realisasi::query()
        ->with([
            'periode:id,tahun,triwulan',
            'kegiatan:id,nama,program_id,divisi_id,rencana_biaya',
            'kegiatan.program:id,nama',
            'kegiatan.divisi:id,nama',
        ])
        ->when($laporanTahun, fn ($query) => $query->whereHas('periode', fn ($periodeQuery) => $periodeQuery->where('tahun', $laporanTahun)))
        ->when($laporanDivisi, fn ($query) => $query->whereHas('kegiatan', fn ($kegiatanQuery) => $kegiatanQuery->where('divisi_id', $laporanDivisi)))
        ->when($laporanSearch !== '', function ($query) use ($laporanSearch) {
            $keyword = '%' . str_replace(' ', '%', $laporanSearch) . '%';

            $query->where(function ($nested) use ($keyword) {
                $nested->where('keterangan', 'like', $keyword)
                    ->orWhereHas('kegiatan', fn ($kegiatanQuery) => $kegiatanQuery->where('nama', 'like', $keyword))
                    ->orWhereHas('kegiatan.program', fn ($programQuery) => $programQuery->where('nama', 'like', $keyword))
                    ->orWhereHas('kegiatan.divisi', fn ($divisiQuery) => $divisiQuery->where('nama', 'like', $keyword));
            });
        })
        ->orderByDesc('periode_id')
        ->orderByDesc('id')
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

        <form method="GET" action="{{ route('home') }}#laporan" class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 md:w-auto">
            <select name="laporan_tahun" class="input min-w-[150px]">
                <option value="">Semua Tahun</option>
                @foreach($tahuns as $tahun)
                    <option value="{{ $tahun }}" @selected((string) $laporanTahun === (string) $tahun)>{{ $tahun }}</option>
                @endforeach
            </select>

            <select name="laporan_divisi" class="input min-w-[190px]">
                <option value="">Semua Divisi</option>
                @foreach($divisis as $divisi)
                    <option value="{{ $divisi->id }}" @selected((string) $laporanDivisi === (string) $divisi->id)>{{ $divisi->nama }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <input
                    name="laporan_search"
                    type="text"
                    value="{{ $laporanSearch }}"
                    class="input min-w-[220px]"
                    placeholder="Cari divisi/program/kegiatan"
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
                    {{-- <th class="px-4 py-3 text-left font-semibold">Triwulan</th> --}}
                    <th class="px-4 py-3 text-left font-semibold">Divisi</th>
                    <th class="px-4 py-3 text-left font-semibold">Program</th>
                    <th class="px-4 py-3 text-left font-semibold">Kegiatan</th>
                    <th class="px-4 py-3 text-right font-semibold">Pagu</th>
                    <th class="px-4 py-3 text-right font-semibold">Realisasi</th>
                    <th class="px-4 py-3 text-right font-semibold">Sisa Anggaran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($laporans as $laporan)
                    @php
                        $pagu = (float) ($laporan->kegiatan?->rencana_biaya ?? 0);
                        $realisasi = (float) ($laporan->realisasi_biaya ?? 0);
                        $sisaAnggaran = $pagu - $realisasi;
                    @endphp
                    <tr class="transition hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3">{{ $laporan->periode?->tahun ?? '-' }}</td>
                        {{-- <td class="whitespace-nowrap px-4 py-3">{{ $laporan->periode?->triwulan ?? '-' }}</td> --}}
                        <td class="px-4 py-3 font-medium text-[var(--color-primary)]">{{ $laporan->kegiatan?->divisi?->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->kegiatan?->program?->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->kegiatan?->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($pagu, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($realisasi, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $sisaAnggaran < 0 ? 'text-red-600' : 'text-emerald-600' }}">Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data realisasi ditemukan.</td>
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
