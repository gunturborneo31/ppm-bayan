<section id="beranda" class="relative w-full min-h-screen xl:min-h-[900px] flex flex-col justify-center overflow-hidden bg-black">
    <!-- Background Video & Overlay -->
    <div class="absolute inset-0 w-full h-full z-0">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="{{ asset('vidio/home.mp4') }}" type="video/mp4">
            Browser Anda tidak mendukung tag video.
        </video>
        
        <!-- Dark Overlay (Ditambah 10% kegelapan sesuai permintaan agar kontras semakin pekat) -->
        <div class="absolute inset-0 bg-black/70"></div>
    </div>

    <!-- Konten di atas Video -->
    <div class="relative z-10 w-full pt-32 pb-24 md:pt-40 md:pb-32">
        {{ $slot }}
    </div>

    <!-- Wave / Curve mask di bagian bawah hero -->
    <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-[0] z-20">
        <svg class="relative block w-full h-[50px] md:h-[80px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0 C150,80 400,120 600,120 C800,120 1050,80 1200,0 L1200,120 L0,120 Z" fill="#ffffff"></path>
        </svg>
    </div>
</section>
