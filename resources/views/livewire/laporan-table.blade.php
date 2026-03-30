<div>
    <div class="mb-6 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Tabel Laporan PPM</h3>

        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-wrap">

            <!-- Filter Tahun - Custom Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-sm font-semibold text-gray-700 hover:border-[var(--color-primary)]/50 hover:bg-orange-50/30 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/20 transition-all duration-200 min-w-[150px] justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-[16px] text-[var(--color-primary)]">calendar_today</span>
                        <span>{{ $tahun ?: 'Semua Tahun' }}</span>
                    </div>
                    <span class="material-icons text-[18px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="absolute left-0 mt-2 w-44 z-50 bg-white rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.12)] border border-gray-100 overflow-hidden"
                     style="display:none;">
                    <button type="button" wire:click="$set('tahun', '')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold transition-colors {{ !$tahun ? 'bg-[var(--color-primary)] text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-[var(--color-primary)]' }}">
                        <span class="material-icons text-[16px]">date_range</span>
                        Semua Tahun
                        @if(!$tahun)<span class="material-icons text-[16px] ml-auto">check</span>@endif
                    </button>
                    <div class="h-px bg-gray-100 mx-3"></div>
                    @foreach($tahuns as $th)
                    <button type="button" wire:click="$set('tahun', '{{ $th }}')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold transition-colors {{ $tahun == $th ? 'bg-[var(--color-primary)] text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-[var(--color-primary)]' }}">
                        <span class="material-icons text-[16px]">{{ $tahun == $th ? 'radio_button_checked' : 'radio_button_unchecked' }}</span>
                        {{ $th }}
                        @if($tahun == $th)<span class="material-icons text-[16px] ml-auto">check</span>@endif
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Filter Bidang - Custom Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-sm font-semibold text-gray-700 hover:border-[var(--color-primary)]/50 hover:bg-orange-50/30 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/20 transition-all duration-200 min-w-[180px] justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-[16px] text-[var(--color-primary)]">category</span>
                        <span class="truncate max-w-[120px]">{{ $bidang ? $bidangs->firstWhere('id', $bidang)?->nama : 'Semua Bidang' }}</span>
                    </div>
                    <span class="material-icons text-[18px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="absolute left-0 mt-2 w-64 z-50 bg-white rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.12)] border border-gray-100 overflow-hidden max-h-72 overflow-y-auto"
                     style="display:none;">
                    <button type="button" wire:click="$set('bidang', '')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold transition-colors {{ !$bidang ? 'bg-[var(--color-primary)] text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-[var(--color-primary)]' }}">
                        <span class="material-icons text-[16px]">apps</span>
                        Semua Bidang
                        @if(!$bidang)<span class="material-icons text-[16px] ml-auto">check</span>@endif
                    </button>
                    <div class="h-px bg-gray-100 mx-3"></div>
                    @foreach($bidangs as $b)
                    <button type="button" wire:click="$set('bidang', '{{ $b->id }}')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold transition-colors {{ $bidang == $b->id ? 'bg-[var(--color-primary)] text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-[var(--color-primary)]' }}">
                        <span class="material-icons text-[16px]">{{ $b->icon ?? 'category' }}</span>
                        <span class="truncate">{{ $b->nama }}</span>
                        @if($bidang == $b->id)<span class="material-icons text-[16px] ml-auto flex-shrink-0">check</span>@endif
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Search -->
            <div class="relative w-full md:w-64">
                <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 pointer-events-none">
                    <span class="material-icons text-sm">search</span>
                </span>
                <input wire:model.live="search" type="text" placeholder="Cari perusahaan / program..."
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] transition shadow-sm hover:border-gray-300">
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-3 flex items-center">
                    <svg class="animate-spin h-4 w-4 text-[var(--color-primary)]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </div>
            </div>

        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm relative">
        <!-- Menghapus overlay spinner dan ganti dengan skeleton <tr> -->
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-[#1a1a1a] text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Tahun</th>
                    <th class="px-4 py-3 text-left font-semibold">Bidang</th>
                    <th class="px-4 py-3 text-left font-semibold">Perusahaan</th>
                    <th class="px-4 py-3 text-left font-semibold">Program</th>
                    <th class="px-4 py-3 text-left font-semibold">Lokasi</th>
                    <th class="px-4 py-3 text-right font-semibold">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody wire:loading.remove wire:target="search, bidang, tahun" class="divide-y divide-gray-100 bg-white">
                @forelse($laporans as $laporan)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $laporan->tahun }}</td>
                        <td class="px-4 py-3 text-primary font-medium">{{ $laporan->bidang->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->perusahaan->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->program->nama ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $laporan->lokasi ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($laporan->nominal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data laporan ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            
            <tbody wire:loading wire:target="search, bidang, tahun" class="divide-y divide-gray-100 bg-white w-full">
                @for($i=0; $i<5; $i++)
                <tr>
                    <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse w-1/2"></div></td>
                    <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse w-3/4"></div></td>
                    <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse w-full"></div></td>
                    <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse w-5/6"></div></td>
                    <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse w-2/3"></div></td>
                    <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded animate-pulse w-full"></div></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="text-sm text-gray-600">
            Menampilkan {{ $laporans->firstItem() ?? 0 }} - {{ $laporans->lastItem() ?? 0 }} dari total {{ $laporans->total() }} data
        </div>
        <div class="w-full sm:w-auto">
            {{ $laporans->links(data: ['scrollTo' => false]) }}
        </div>
        <div class="flex gap-2">
            <!-- Peringatan: tombol export belum terimplementasi fungsional backend-nya (placeholder) -->
            <button class="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-full text-sm font-bold uppercase transition flex items-center shadow-sm">
                <span class="material-icons text-sm mr-2">download</span> Export PDF
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-full text-sm font-bold uppercase transition flex items-center shadow-sm">
                <span class="material-icons text-sm mr-2">table_view</span> Export Excel
            </button>
        </div>
    </div>
</div>
