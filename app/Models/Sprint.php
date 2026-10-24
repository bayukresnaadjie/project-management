<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $table    = 'sprints';
    protected $fillable = ['project_id','nama','tujuan','tanggal_mulai','tanggal_selesai','status','kapasitas'];
    protected $casts    = ['tanggal_mulai'=>'date','tanggal_selesai'=>'date'];

    public function project() { return $this->belongsTo(Project::class); }
    public function tasks()   { return $this->hasMany(Task::class); }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'perencanaan' => ['label'=>'Perencanaan','class'=>'bg-gray-100 text-gray-600'],
            'aktif'       => ['label'=>'Aktif',      'class'=>'bg-green-100 text-green-700'],
            'selesai'     => ['label'=>'Selesai',    'class'=>'bg-blue-100 text-blue-700'],
            default       => ['label'=>ucfirst($this->status),'class'=>'bg-gray-100 text-gray-500'],
        };
    }

    public function getTotalPointAttribute(): int
    {
        return (int) $this->tasks()->sum('story_point');
    }

    public function getDonePointAttribute(): int
    {
        return (int) $this->tasks()
            ->whereHas('board', fn($q) => $q->where('is_done', true))
            ->sum('story_point');
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->whereNull('parent_id')->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->whereNull('parent_id')
            ->whereHas('board', fn($q) => $q->where('is_done', true))->count();
        return round(($done / $total) * 100);
    }
}
