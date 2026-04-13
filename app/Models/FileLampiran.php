<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileLampiran extends Model {
    use SoftDeletes;

    protected $table = 'files';
    protected $fillable = ['kegiatan_id','realisasi_id','file_path','file_name','file_type','file_size','kategori','uploaded_by'];

    public function kegiatan() { return $this->belongsTo(Kegiatan::class); }
    public function realisasi() { return $this->belongsTo(Realisasi::class); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
