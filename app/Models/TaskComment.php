<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TaskComment extends Model {
    use SoftDeletes;
    protected $table = 'task_comments';
    protected $fillable = ['task_id','user_id','parent_id','isi'];
    public function task()    { return $this->belongsTo(Task::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function parent()  { return $this->belongsTo(TaskComment::class,'parent_id'); }
    public function replies() { return $this->hasMany(TaskComment::class,'parent_id')->with('user'); }
}
