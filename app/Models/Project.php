<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $table    = 'projects';
    protected $fillable = [
        'workspace_id','dibuat_oleh','nama','kode','deskripsi',
        'avatar','warna','status','tipe',
        'tanggal_mulai','tanggal_selesai','publik',
    ];
    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'publik'          => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->kode)) {
                $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/','',$model->nama), 0, 3));
                $count  = static::where('workspace_id', $model->workspace_id)->count() + 1;
                $model->kode = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });

        // Buat default board saat project dibuat
        static::created(function ($model) {
            $boards = [
                ['nama'=>'To Do',       'warna'=>'#6B7280', 'urutan'=>1, 'is_default'=>true],
                ['nama'=>'In Progress', 'warna'=>'#3B82F6', 'urutan'=>2],
                ['nama'=>'Review',      'warna'=>'#F59E0B', 'urutan'=>3],
                ['nama'=>'Done',        'warna'=>'#10B981', 'urutan'=>4, 'is_done'=>true],
            ];
            foreach ($boards as $b) {
                Board::create(array_merge($b, ['project_id' => $model->id]));
            }
        });
    }

    public function workspace()    { return $this->belongsTo(Workspace::class); }
    public function dibuatOleh()   { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function members()      { return $this->hasMany(ProjectMember::class); }
    public function boards()       { return $this->hasMany(Board::class)->orderBy('urutan'); }
    public function sprints()      { return $this->hasMany(Sprint::class); }
    public function tasks()        { return $this->hasMany(Task::class); }
    public function labels()       { return $this->hasMany(Label::class); }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')->withTimestamps();
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->kode) . '&background=' . ltrim($this->warna,'#') . '&color=fff&size=64';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'aktif'      => ['label'=>'Aktif',      'class'=>'bg-green-100 text-green-700'],
            'selesai'    => ['label'=>'Selesai',    'class'=>'bg-blue-100 text-blue-700'],
            'ditunda'    => ['label'=>'Ditunda',    'class'=>'bg-yellow-100 text-yellow-700'],
            'dibatalkan' => ['label'=>'Dibatalkan', 'class'=>'bg-red-100 text-red-700'],
            default      => ['label'=>ucfirst($this->status), 'class'=>'bg-gray-100 text-gray-500'],
        };
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->whereNull('parent_id')->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->whereNull('parent_id')
            ->whereHas('board', fn($q) => $q->where('is_done', true))->count();
        return round(($done / $total) * 100);
    }

    public function userBisaAkses(User $user): bool
    {
        if ($this->publik) return true;
        if ($this->dibuat_oleh === $user->id) return true;
        if ($this->workspace->owner_id === $user->id) return true;

        // Workspace admin juga bisa akses semua project
        $wsRole = $this->workspace->members()
            ->where('user_id', $user->id)
            ->value('role');
        if (in_array($wsRole, ['owner', 'admin'])) return true;

        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function getRoleUser(User $user): ?string
    {
        if ($this->dibuat_oleh === $user->id) return 'manager';

        // Cek member project langsung
        $role = $this->members()->where('user_id', $user->id)->value('role');
        if ($role) return $role;

        // Workspace owner/admin dapat role manager di semua project
        $wsRole = $this->workspace->members()
            ->where('user_id', $user->id)
            ->value('role');
        if (in_array($wsRole, ['owner', 'admin'])) return 'manager';

        return null;
    }

    public function getSprintAktifAttribute(): ?Sprint
    {
        return $this->sprints()->where('status','aktif')->first();
    }
}
