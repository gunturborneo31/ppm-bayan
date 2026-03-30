<?php

namespace App\Livewire;

use App\Models\Berita;
use App\Models\KategoriBerita;
use Livewire\Component;
use Livewire\WithPagination;

class BeritaList extends Component
{
    use WithPagination;

    public $search = '';
    public $kategori = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingKategori()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Berita::with('kategori')->latest('published_at');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->kategori) {
            $query->where('kategori_id', $this->kategori);
        }

        return view('livewire.berita-list', [
            'beritas' => $query->paginate(6),
            'kategoris' => KategoriBerita::all()
        ]);
    }
}
