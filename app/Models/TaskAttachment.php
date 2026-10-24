<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TaskAttachment extends Model {
    protected $table = 'task_attachments';
    protected $fillable = ['task_id','user_id','nama_file','path_file','mime_type','ukuran'];
    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function getUkuranFormatAttribute(): string {
        $b = $this->ukuran;
        if ($b >= 1048576) return number_format($b/1048576,2).' MB';
        if ($b >= 1024)    return number_format($b/1024,2).' KB';
        return $b.' B';
    }
    public function getIconAttribute(): string {
        $ext = strtolower(pathinfo($this->nama_file, PATHINFO_EXTENSION));
        return match($ext) {
            'pdf'       => '📄', 'docx','doc' => '📝',
            'xlsx','xls'=> '📊', 'pptx','ppt' => '📋',
            'jpg','jpeg','png','gif','webp' => '🖼️',
            'zip','rar' => '📦', default => '📁',
        };
    }
}
