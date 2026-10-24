<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model {
    protected $table = 'notifications';
    protected $fillable = ['user_id','dari_user_id','task_id','project_id','judul','pesan','tipe','url','dibaca','dibaca_at'];
    protected $casts = ['dibaca'=>'boolean','dibaca_at'=>'datetime'];
    public function user()      { return $this->belongsTo(User::class); }
    public function dariUser()  { return $this->belongsTo(User::class,'dari_user_id'); }
    public function task()      { return $this->belongsTo(Task::class); }
    public function project()   { return $this->belongsTo(Project::class); }

    public static function kirim(int $userId, string $judul, string $pesan, string $tipe = 'info', ?string $url = null, ?int $taskId = null, ?int $projectId = null): void {
        if ($userId === auth()->id()) return;
        static::create([
            'user_id'      => $userId,
            'dari_user_id' => auth()->id(),
            'task_id'      => $taskId,
            'project_id'   => $projectId,
            'judul'        => $judul,
            'pesan'        => $pesan,
            'tipe'         => $tipe,
            'url'          => $url,
        ]);
    }
}
