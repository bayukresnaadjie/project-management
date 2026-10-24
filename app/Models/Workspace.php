<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use SoftDeletes;

    protected $table    = 'workspaces';
    protected $fillable = ['owner_id','nama','slug','deskripsi','avatar','warna','aktif'];
    protected $casts    = ['aktif' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nama) . '-' . Str::random(6);
            }
        });
    }

    public function owner()    { return $this->belongsTo(User::class, 'owner_id'); }
    public function members()  { return $this->hasMany(WorkspaceMember::class); }
    public function projects() { return $this->hasMany(Project::class); }

    public function users()
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role','joined_at')->withTimestamps();
    }

    public function getRoleUser(User $user): ?string
    {
        if ($this->owner_id === $user->id) return 'owner';
        return $this->members()->where('user_id', $user->id)->value('role');
    }

    public function userBisaAkses(User $user): bool
    {
        if ($this->owner_id === $user->id) return true;
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->nama) . '&background=' . ltrim($this->warna,'#') . '&color=fff&size=64';
    }
}
