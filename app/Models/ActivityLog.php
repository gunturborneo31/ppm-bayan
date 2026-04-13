<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model {
    protected $fillable = ['user_id','module','action','subject_type','subject_id','field','old_value','new_value','description'];

    public function user() { return $this->belongsTo(User::class); }
}
