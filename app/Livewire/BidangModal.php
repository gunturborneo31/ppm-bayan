<?php

namespace App\Livewire;

use App\Models\BidangCsr;
use Livewire\Component;

class BidangModal extends Component
{
    public $isOpen = false;
    public $bidang = null;
    public $programList = [];
    public $totalAnggaran = 0;

    protected $listeners = ['openBidangModal' => 'loadBidang'];

    public function loadBidang($id)
    {
        $this->bidang = BidangCsr::with('programCsrs.perusahaan')->find($id);
        if ($this->bidang) {
            $this->programList = $this->bidang->programCsrs;
            $this->totalAnggaran = $this->programList->sum('anggaran');
            $this->isOpen = true;
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->bidang = null;
    }

    public function render()
    {
        return view('livewire.bidang-modal');
    }
}
