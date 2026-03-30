<div>
    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <h3 class="text-xl font-semibold text-white">Statistik Perusahaan</h3>
        <div class="relative w-full md:w-64">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                <span class="material-icons text-sm">search</span>
            </span>
            <input wire:model.live="search" type="text" placeholder="Cari perusahaan..." class="w-full pl-10 pr-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-300 focus:outline-none focus:border-accent">
        </div>
    </div>

    <!-- Data Chart (Hidden, for JS) -->
    <div id="chart-data-container" data-chart='@json($this->chartData)' class="hidden"></div>

    <div class="overflow-x-auto rounded-lg border border-white/20 glass-dark">
        <table class="min-w-full divide-y divide-white/10 text-sm text-white">
            <thead class="bg-white/5">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">No</th>
                    <th class="px-4 py-3 text-left font-medium">Nama Perusahaan</th>
                    <th class="px-4 py-3 text-center font-medium">Jumlah Kegiatan / Program</th>
                    <th class="px-4 py-3 text-right font-medium">Total Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($perusahaans as $index => $perusahaan)
                    <tr class="hover:bg-white/5 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-300">{{ $perusahaans->firstItem() + $index }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $perusahaan->nama }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-white/10 border border-white/10 px-2 py-1 rounded text-xs font-medium">{{ $perusahaan->laporan_csrs_count ?? 0 }} program</span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-accent">
                            {{ number_format($perusahaan->laporan_csrs_sum_nominal ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                            Data perusahaan tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4 pb-2">
        {{ $perusahaans->links() }}
    </div>
</div>
