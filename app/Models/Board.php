<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $table    = 'boards';
    protected $fillable = ['project_id','nama','warna','urutan','is_done','is_default'];
    protected $casts    = ['is_done'=>'boolean','is_default'=>'boolean'];

    public function project() { return $this->belongsTo(Project::class); }
    public function tasks()   { return $this->hasMany(Task::class)->orderBy('urutan'); }

    public function getTaskCountAttribute(): int
    {
        return $this->tasks()->whereNull('parent_id')->count();
    }
}
