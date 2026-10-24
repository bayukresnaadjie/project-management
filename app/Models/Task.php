<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table    = 'tasks';
    protected $fillable = [
        'project_id','board_id','sprint_id','parent_id',
        'dibuat_oleh','assignee_id','nomor','judul','deskripsi',
        'tipe','prioritas','story_point','estimasi_jam','aktual_jam',
        'progres','due_date','selesai_at','urutan',
    ];
    protected $casts = [
        'due_date'   => 'date',
        'selesai_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->nomor)) {
                $project = Project::find($model->project_id);
                $prefix  = explode('-', $project->kode)[0];
                $count   = static::where('project_id', $model->project_id)->withTrashed()->count() + 1;
                $model->nomor = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
            if (empty($model->urutan)) {
                $model->urutan = static::where('board_id', $model->board_id)->max('urutan') + 1;
            }
        });
    }

    public function project()    { return $this->belongsTo(Project::class); }
    public function board()      { return $this->belongsTo(Board::class); }
    public function sprint()     { return $this->belongsTo(Sprint::class); }
    public function parent()     { return $this->belongsTo(Task::class, 'parent_id'); }
    public function subTasks()   { return $this->hasMany(Task::class, 'parent_id')->orderBy('urutan'); }
    public function dibuatOleh() { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function assignee()   { return $this->belongsTo(User::class, 'assignee_id'); }
    public function assignees()  { return $this->belongsToMany(User::class, 'task_assignees'); }
    public function labels()     { return $this->belongsToMany(Label::class, 'task_labels'); }
    public function comments()   { return $this->hasMany(TaskComment::class)->whereNull('parent_id')->with('replies.user')->orderBy('created_at'); }
    public function attachments(){ return $this->hasMany(TaskAttachment::class); }
    public function activities() { return $this->hasMany(TaskActivity::class)->orderByDesc('created_at'); }

    public function getPrioritasBadgeAttribute(): array
    {
        return match($this->prioritas) {
            'critical' => ['label'=>'Critical','class'=>'bg-red-100 text-red-700',   'icon'=>'🔴'],
            'high'     => ['label'=>'High',    'class'=>'bg-orange-100 text-orange-700','icon'=>'🟠'],
            'medium'   => ['label'=>'Medium',  'class'=>'bg-yellow-100 text-yellow-700','icon'=>'🟡'],
            'low'      => ['label'=>'Low',     'class'=>'bg-green-100 text-green-700','icon'=>'🟢'],
            default    => ['label'=>ucfirst($this->prioritas),'class'=>'bg-gray-100 text-gray-500','icon'=>'⚪'],
        };
    }

    public function getTipeBadgeAttribute(): array
    {
        return match($this->tipe) {
            'bug'         => ['label'=>'Bug',        'class'=>'bg-red-100 text-red-700',    'icon'=>'🐛'],
            'feature'     => ['label'=>'Feature',    'class'=>'bg-blue-100 text-blue-700',  'icon'=>'✨'],
            'improvement' => ['label'=>'Improvement','class'=>'bg-purple-100 text-purple-700','icon'=>'⚡'],
            'epic'        => ['label'=>'Epic',       'class'=>'bg-pink-100 text-pink-700',  'icon'=>'🎯'],
            default       => ['label'=>'Task',       'class'=>'bg-gray-100 text-gray-600',  'icon'=>'✅'],
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->selesai_at;
    }

    public function getSubTaskProgressAttribute(): int
    {
        $total = $this->subTasks()->count();
        if ($total === 0) return $this->progres;
        $done = $this->subTasks()->whereHas('board', fn($q) => $q->where('is_done', true))->count();
        return round(($done / $total) * 100);
    }

    public static function catatAktivitas(int $taskId, string $aksi, ?string $field = null, $lama = null, $baru = null, ?string $ket = null): void
    {
        TaskActivity::create([
            'task_id'    => $taskId,
            'user_id'    => auth()->id(),
            'aksi'       => $aksi,
            'field'      => $field,
            'nilai_lama' => $lama,
            'nilai_baru' => $baru,
            'keterangan' => $ket,
        ]);
    }
}
