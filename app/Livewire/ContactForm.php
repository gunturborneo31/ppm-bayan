<?php

namespace App\Livewire;

use App\Models\Pesan;
use Livewire\Component;

class ContactForm extends Component
{
    public $nama = '';
    public $email = '';
    public $pesan = '';
    public $sent = false;

    protected $rules = [
        'nama' => 'required|min:3',
        'email' => 'required|email',
        'pesan' => 'required|min:10',
    ];

    public function submit()
    {
        $this->validate();

        Pesan::create([
            'nama' => $this->nama,
            'email' => $this->email,
            'pesan' => $this->pesan,
        ]);

        $this->sent = true;
        $this->reset(['nama', 'email', 'pesan']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
