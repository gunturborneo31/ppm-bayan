<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'divisi_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function divisi() { return $this->belongsTo(Divisi::class); }
    public function programs() { return $this->hasMany(Program::class); }
    public function kegiatans() { return $this->hasManyThrough(Kegiatan::class, Program::class); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function isSuperadmin(): bool { return $this->role === 'superadmin'; }
    public function isDivisi(): bool { return $this->role === 'divisi'; }
    public function isPimpinan(): bool { return $this->role === 'pimpinan'; }
    public function isCdo(): bool { return $this->role === 'cdo' || $this->isSuperadmin(); }
}
