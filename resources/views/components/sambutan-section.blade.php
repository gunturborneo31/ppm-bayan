@props(['pimpinan', 'berita'])

<div class="w-full font-[var(--font-sans)] relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Grid Reorganized: Quote & Welcome di Kanan Atas, Pimpinan HANYA Foto di Kiri -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-stretch min-h-[500px] lg:min-h-[600px] py-10 relative">
            
            <!-- Kolom KIRI: HANYA Foto & Nama Pimpinan (Digeser Ekstrim) -->
            <div class="lg:col-span-5 flex flex-col items-center justify-end lg:items-end pt-8 mb-10 lg:mb-0 order-2 lg:order-1 relative lg:-mt-12 xl:-mt-20">
                <!-- Foto Sangat Besar (Bergeser ke Atas) -->
                <img src="{{ asset('pimpinan.svg') }}" 
                     alt="{{ $pimpinan->nama ?? 'Foto Pimpinan' }}" 
                     class="max-w-none w-80 sm:w-[26rem] md:w-[32rem] lg:w-[42rem] xl:w-[50rem] 2xl:w-[58rem] h-auto object-contain drop-shadow-[0_20px_40px_rgba(0,0,0,0.6)] z-10 pointer-events-none transition-transform duration-700 hover:scale-[1.03] lg:absolute lg:bottom-28 lg:-left-20 xl:-left-32 origin-bottom"
                     style="mask-image: linear-gradient(to bottom, black 75%, transparent 98%); -webkit-mask-image: linear-gradient(to bottom, black 75%, transparent 98%);">
                
                <!-- Nama & Jabatan (Ikut Naik Sejajar Foto) -->
                <div class="text-center lg:text-right mt-6 lg:mt-0 lg:absolute lg:bottom-[11rem] lg:-left-6 xl:-left-12 z-20 bg-black/60 backdrop-blur-md px-6 lg:px-8 py-4 lg:py-5 rounded-2xl border border-white/10 shadow-[0_20px_40px_rgba(0,0,0,0.7)]">
                    <h3 class="font-extrabold text-white text-2xl lg:text-3xl tracking-wide drop-shadow-xl leading-tight mb-1">{{ $pimpinan->nama ?? 'Nama Pimpinan' }}</h3>
                    <p class="text-[10px] sm:text-xs lg:text-sm text-white font-bold uppercase tracking-widest drop-shadow-md">{{ $pimpinan->jabatan ?? 'Jabatan Pimpinan' }}</p>
                </div>
            </div>

            <!-- Kolom KANAN: Ucapan Selamat Datang -->
            <div class="lg:col-span-7 flex flex-col h-full justify-center order-1 lg:order-2 lg:-mt-10 xl:-mt-14">
                
                <div class="mb-4 lg:mb-8">
                    <div class="flex items-center gap-4 mb-4 lg:mb-6">
                        <div class="w-1 h-8 bg-[var(--color-primary)]"></div>
                        <span class="text-[var(--color-primary)] font-bold tracking-widest text-xs md:text-sm uppercase drop-shadow-md italic"></span>
                    </div>

                    <h2 class="text-3xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-8 lg:mb-12 drop-shadow-lg tracking-tight">
                        Selamat datang di<br>PPM PT Bayan Group
                    </h2>
                    
                    <div class="flex items-start gap-3 lg:gap-5">
                        <span class="text-7xl sm:text-8xl lg:text-9xl font-serif text-white leading-[0.6] pt-1 select-none">"</span>
                        <div class="text-white/95 text-lg md:text-xl lg:text-xl leading-relaxed font-normal drop-shadow-xl pt-1">
                            Kami berkomitmen untuk terus memperluas cakupan Pengembangan dan Pemberdayaan Masyarakat (PPM), memberdayakan masyarakat secara inklusif dan berkelanjutan.
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</div>
