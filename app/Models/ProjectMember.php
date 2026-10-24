<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    protected $table    = 'project_members';
    protected $fillable = ['project_id','user_id','role'];

    public function project() { return $this->belongsTo(Project::class); }
    public function user()    { return $this->belongsTo(User::class); }

    public function getRoleBadgeAttribute(): array
    {
        return match($this->role) {
            'manager' => ['label'=>'Manager','class'=>'bg-purple-100 text-purple-700'],
            'member'  => ['label'=>'Member', 'class'=>'bg-blue-100 text-blue-700'],
            'viewer'  => ['label'=>'Viewer', 'class'=>'bg-gray-100 text-gray-600'],
            default   => ['label'=>ucfirst($this->role),'class'=>'bg-gray-100 text-gray-500'],
        };
    }
}
