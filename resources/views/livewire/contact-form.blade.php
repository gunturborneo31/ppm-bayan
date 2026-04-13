<div>
    @if($sent)
        <div class="border border-green-200/30 rounded-lg p-6 text-center h-full flex flex-col items-center justify-center bg-white/5 backdrop-blur-md">
            <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mb-4 mx-auto border border-green-500/30">
                <span class="material-icons text-green-400 text-3xl">check_circle</span>
            </div>
            <h4 class="text-xl font-bold text-white mb-2">Pesan Terkirim!</h4>
            <p class="text-gray-300 text-sm mb-6">Terima kasih atas masukan dan saran Anda. Tim kami akan segera menindaklanjuti dan merespon secepatnya ke email Anda.</p>
            <button wire:click="$set('sent', false)" class="bg-white/10 hover:bg-white/20 text-white px-6 py-2.5 rounded-full transition text-sm font-medium border border-white/20 shadow-sm">
                Kirim Pesan Lain
            </button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-4">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <span class="material-icons text-[18px]">person</span>
                    </span>
                    <input wire:model="nama" type="text" id="nama" class="w-full border border-white/20 rounded-lg pl-10 pr-4 py-2.5 bg-white/5 text-white focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent placeholder-gray-500 transition shadow-inner" placeholder="John Doe">
                </div>
                @error('nama') <p class="text-xs text-red-400 mt-1.5 flex items-center font-medium"><span class="material-icons text-[14px] mr-1">error</span> {{ $message }}</p> @enderror
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <span class="material-icons text-[18px]">email</span>
                    </span>
                    <input wire:model="email" type="email" id="email" class="w-full border border-white/20 rounded-lg pl-10 pr-4 py-2.5 bg-white/5 text-white focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent placeholder-gray-500 transition shadow-inner" placeholder="john@example.com">
                </div>
                @error('email') <p class="text-xs text-red-400 mt-1.5 flex items-center font-medium"><span class="material-icons text-[14px] mr-1">error</span> {{ $message }}</p> @enderror
            </div>
            
            <div>
                <label for="pesan" class="block text-sm font-medium text-gray-300 mb-1">Masukan/Saran</label>
                <div class="relative">
                    <span class="absolute top-3 left-3 flex items-start text-gray-400">
                        <span class="material-icons text-[18px]">chat</span>
                    </span>
                    <textarea wire:model="pesan" id="pesan" rows="4" class="w-full border border-white/20 rounded-lg pl-10 pr-4 py-2.5 bg-white/5 text-white focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent placeholder-gray-500 transition shadow-inner" placeholder="Tulis masukan, saran, atau pertanyaan Anda di sini..."></textarea>
                </div>
                @error('pesan') <p class="text-xs text-red-400 mt-1.5 flex items-center font-medium"><span class="material-icons text-[14px] mr-1">error</span> {{ $message }}</p> @enderror
            </div>
            
            <button type="submit" class="w-full bg-accent text-white px-4 py-3 rounded-full font-bold hover:bg-orange-600 transition flex justify-center items-center shadow-lg transform hover:-translate-y-0.5 mt-2">
                <span wire:loading.remove wire:target="submit" class="flex items-center">
                    <span class="material-icons text-[18px] mr-2">send</span> Kirim Pesan Sekarang
                </span>
                <span wire:loading wire:target="submit" class="flex items-center">
                    <span class="material-icons text-[18px] mr-2 animate-spin">autorenew</span> Sedang Memproses...
                </span>
            </button>
        </form>
    @endif
</div>
