<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceMember extends Model
{
    protected $table    = 'workspace_members';
    protected $fillable = ['workspace_id','user_id','role','joined_at'];
    protected $casts    = ['joined_at' => 'datetime'];

    public function workspace() { return $this->belongsTo(Workspace::class); }
    public function user()      { return $this->belongsTo(User::class); }

    public function getRoleBadgeAttribute(): array
    {
        return match($this->role) {
            'owner'  => ['label'=>'Owner',  'class'=>'bg-purple-100 text-purple-700'],
            'admin'  => ['label'=>'Admin',  'class'=>'bg-red-100 text-red-700'],
            'member' => ['label'=>'Member', 'class'=>'bg-blue-100 text-blue-700'],
            'viewer' => ['label'=>'Viewer', 'class'=>'bg-gray-100 text-gray-600'],
            default  => ['label'=>ucfirst($this->role), 'class'=>'bg-gray-100 text-gray-500'],
        };
    }
}
