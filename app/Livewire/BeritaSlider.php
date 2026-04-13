<?php

namespace App\Livewire;

use Livewire\Component;

class BeritaSlider extends Component
{
    public $berita;
    public int $active = 0;

    public function mount($berita)
    {
        $items = [];
        if ($berita) {
            foreach ($berita as $b) {
                $items[] = [
                    'judul' => $b->judul,
                    'slug' => $b->slug,
                    'kategori' => $b->kategori->nama ?? 'Program CSR',
                    'tanggal' => $b->published_at ? $b->published_at->format('d M Y') : ($b->tanggal ?? $b->created_at->format('d M Y')),
                    'thumbnail' => $b->thumbnail ? asset('storage/' . $b->thumbnail) : null
                ];
            }
        }
        
        // Data Dummy Utama Jika Data Kurang
        $dummy = [
            [
                'judul' => 'Peresmian Fasilitas Air Bersih CSR untuk Desa Binaan PPN Bayan Group',
                'slug' => '#',
                'kategori' => 'Infrastruktur',
                'tanggal' => now()->format('d M Y'),
                'thumbnail' => null
            ],
            [
                'judul' => 'Program Beasiswa Pendidikan Terpadu Mahakam Ulu Angkatan 2026',
                'slug' => '#',
                'kategori' => 'Pendidikan',
                'tanggal' => now()->subDays(2)->format('d M Y'),
                'thumbnail' => null
            ],
            [
                'judul' => 'Pelatihan Wirausaha Berkelanjutan UMKM Kutai Kartanegara',
                'slug' => '#',
                'kategori' => 'Ekonomi Kerakyatan',
                'tanggal' => now()->subDays(5)->format('d M Y'),
                'thumbnail' => null
            ]
        ];

        // Fill with dummy until we have 3 for a nice slider experience
        $i = 0;
        while (count($items) < 3 && $i < count($dummy)) {
            $items[] = $dummy[$i];
            $i++;
        }

        $this->berita = $items;
    }

    public function next()
    {
        if (count($this->berita) > 0) {
            $this->active = ($this->active + 1) % count($this->berita);
        }
    }

    public function prev()
    {
        if (count($this->berita) > 0) {
            $this->active = ($this->active - 1 + count($this->berita)) % count($this->berita);
        }
    }

    public function goTo($index)
    {
        $this->active = $index;
    }

    public function render()
    {
        return view('livewire.berita-slider');
    }
}
