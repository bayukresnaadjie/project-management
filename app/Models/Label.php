<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $table    = 'labels';
    protected $fillable = ['project_id','nama','warna'];

    public function project() { return $this->belongsTo(Project::class); }
    public function tasks()   { return $this->belongsToMany(Task::class, 'task_labels'); }
}
