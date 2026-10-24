<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TaskActivity extends Model {
    protected $table = 'task_activities';
    protected $fillable = ['task_id','user_id','aksi','field','nilai_lama','nilai_baru','keterangan'];
    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function getAksiLabelAttribute(): string {
        return match($this->aksi) {
            'created'    => 'membuat task',
            'moved'      => 'memindahkan ke',
            'assigned'   => 'menugaskan ke',
            'commented'  => 'menambah komentar',
            'updated'    => 'mengubah',
            'completed'  => 'menyelesaikan task',
            'attached'   => 'menambah lampiran',
            'prioritas'  => 'mengubah prioritas',
            default      => $this->aksi,
        };
    }
}
