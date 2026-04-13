<div>
    @if($isOpen && $bidang)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/60 backdrop-blur-sm p-4">
        <!-- Background overlay that closes modal -->
        <div wire:click="closeModal" class="absolute inset-0"></div>
        
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl flex flex-col max-h-[90vh] z-10 transform transition-all">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                <div class="flex items-center gap-3">
                    <span class="material-icons text-accent text-3xl">{{ $bidang->icon ?? 'category' }}</span>
                    <h3 class="text-xl font-bold text-primary-dark">Detail Bidang: {{ $bidang->nama }}</h3>
                </div>
                <button wire:click="closeModal" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 overflow-y-auto w-full">
                <!-- Summary Boxes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="bg-primary/5 p-4 rounded-lg border border-primary/10 flex items-center gap-4">
                        <div class="bg-primary/10 p-3 rounded-full">
                            <span class="material-icons text-primary">diversity_3</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-0.5">Total Kegiatan</div>
                            <div class="text-2xl font-bold text-primary leading-none">{{ $programList->count() }} <span class="text-base font-normal text-gray-500">Program</span></div>
                        </div>
                    </div>
                    <div class="bg-accent/10 p-4 rounded-lg border border-accent/20 flex items-center gap-4">
                        <div class="bg-accent/20 p-3 rounded-full">
                            <span class="material-icons text-accent">account_balance_wallet</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-0.5">Total Anggaran</div>
                            <div class="text-2xl font-bold text-accent leading-none">Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <h4 class="font-bold text-gray-800 mb-4 border-b pb-2 flex items-center">
                    <span class="material-icons text-[18px] mr-2 text-gray-400">list_alt</span> Daftar Program Terkait
                </h4>
                
                @if($programList->count() > 0)
                    <div class="space-y-4">
                        @foreach($programList as $program)
                        <div class="border border-gray-100 p-4 rounded-lg hover:border-gray-300 hover:shadow-sm transition bg-white">
                            <div class="flex justify-between items-start mb-2">
                                <h5 class="font-bold text-primary-dark text-lg leading-tight">{{ $program->nama }}</h5>
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-600 font-bold whitespace-nowrap">{{ $program->tahun }}</span>
                            </div>
                            <div class="text-sm text-gray-600 mb-3 flex items-center">
                                <span class="material-icons text-[16px] mr-1 text-gray-400">business</span> <span class="font-medium">{{ $program->perusahaan->nama ?? 'Semua Perusahaan' }}</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center text-xs text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full">
                                    <span class="material-icons text-[14px] mr-1 text-gray-500">location_on</span> {{ $program->lokasi ?? 'Berbagai Kawasan' }}
                                </span>
                                <span class="inline-flex items-center text-xs font-bold text-accent bg-accent/10 px-2.5 py-1 rounded-full border border-accent/20">
                                    <span class="material-icons text-[14px] mr-1">payments</span> Rp {{ number_format($program->anggaran, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                        <span class="material-icons text-4xl mb-2 text-gray-300">inbox</span>
                        <p class="font-medium">Belum ada rincian program untuk bidang ini.</p>
                    </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="flex items-center justify-end p-4 border-t border-gray-100 rounded-b-xl bg-gray-50">
                <button wire:click="closeModal" class="text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 transition shadow-sm">Tutup Modal</button>
            </div>
        </div>
    </div>
    @endif
</div>
