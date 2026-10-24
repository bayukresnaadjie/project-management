<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Board;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Label;
use App\Models\User;
use App\Models\TaskActivity;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $rina  = User::where('email','rina@pm.test')->first();
        $dika  = User::where('email','dika@pm.test')->first();
        $sari  = User::where('email','sari@pm.test')->first();
        $fajar = User::where('email','fajar@pm.test')->first();
        $tono  = User::where('email','tono@pm.test')->first();
        $maya  = User::where('email','maya@pm.test')->first();
        $admin = User::where('email','admin@pm.test')->first();

        // ── PROJECT 1: Website Redesign ──────────────────────────────
        $p1     = Project::where('kode','WEB-001')->first();
        $boards1 = $p1->boards->keyBy('nama');
        $todo1   = $boards1['To Do'];
        $prog1   = $boards1['In Progress'];
        $rev1    = $boards1['Review'];
        $done1   = $boards1['Done'];

        $lblFE = Label::where('project_id',$p1->id)->where('nama','Frontend')->first();
        $lblBE = Label::where('project_id',$p1->id)->where('nama','Backend')->first();
        $lblDS = Label::where('project_id',$p1->id)->where('nama','Design')->first();
        $lblUR = Label::where('project_id',$p1->id)->where('nama','Urgent')->first();

        // Done tasks
        $t1 = Task::create(['project_id'=>$p1->id,'board_id'=>$done1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$sari->id,'judul'=>'Riset kompetitor dan benchmark desain','tipe'=>'task','prioritas'=>'high','story_point'=>3,'progres'=>100,'selesai_at'=>now()->subDays(20),'nomor'=>'WEB-001']);
        $t1->labels()->attach([$lblDS->id]);

        $t2 = Task::create(['project_id'=>$p1->id,'board_id'=>$done1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$sari->id,'judul'=>'Buat wireframe halaman utama','tipe'=>'task','prioritas'=>'high','story_point'=>5,'progres'=>100,'selesai_at'=>now()->subDays(15),'nomor'=>'WEB-002']);
        $t2->labels()->attach([$lblDS->id]);

        $t3 = Task::create(['project_id'=>$p1->id,'board_id'=>$done1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$dika->id,'judul'=>'Setup Laravel project & deployment pipeline','tipe'=>'task','prioritas'=>'critical','story_point'=>5,'progres'=>100,'selesai_at'=>now()->subDays(10),'nomor'=>'WEB-003']);
        $t3->labels()->attach([$lblBE->id]);

        // In Progress tasks
        $t4 = Task::create(['project_id'=>$p1->id,'board_id'=>$prog1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$fajar->id,'judul'=>'Implementasi halaman beranda (Homepage)','tipe'=>'feature','prioritas'=>'high','story_point'=>8,'estimasi_jam'=>16,'aktual_jam'=>10,'progres'=>60,'due_date'=>now()->addDays(5),'nomor'=>'WEB-004']);
        $t4->labels()->attach([$lblFE->id]);
        $t4->assignees()->attach([$fajar->id,$sari->id]);

        $t5 = Task::create(['project_id'=>$p1->id,'board_id'=>$prog1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$dika->id,'judul'=>'API endpoint produk & kategori','tipe'=>'feature','prioritas'=>'high','story_point'=>5,'estimasi_jam'=>12,'aktual_jam'=>8,'progres'=>70,'due_date'=>now()->addDays(3),'nomor'=>'WEB-005']);
        $t5->labels()->attach([$lblBE->id]);

        $t6 = Task::create(['project_id'=>$p1->id,'board_id'=>$prog1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$sari->id,'judul'=>'Desain halaman About Us & Contact','tipe'=>'task','prioritas'=>'medium','story_point'=>3,'estimasi_jam'=>8,'aktual_jam'=>4,'progres'=>50,'due_date'=>now()->addDays(7),'nomor'=>'WEB-006']);
        $t6->labels()->attach([$lblDS->id,$lblFE->id]);

        // Sub-tasks untuk t4
        Task::create(['project_id'=>$p1->id,'board_id'=>$prog1->id,'parent_id'=>$t4->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$fajar->id,'judul'=>'Hero section & banner slider','tipe'=>'task','prioritas'=>'high','story_point'=>3,'progres'=>100,'nomor'=>'WEB-004-1']);
        Task::create(['project_id'=>$p1->id,'board_id'=>$prog1->id,'parent_id'=>$t4->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$fajar->id,'judul'=>'Featured products section','tipe'=>'task','prioritas'=>'medium','story_point'=>2,'progres'=>60,'nomor'=>'WEB-004-2']);
        Task::create(['project_id'=>$p1->id,'board_id'=>$todo1->id,'parent_id'=>$t4->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$fajar->id,'judul'=>'Footer & newsletter form','tipe'=>'task','prioritas'=>'low','story_point'=>2,'progres'=>0,'nomor'=>'WEB-004-3']);

        // Review tasks
        $t7 = Task::create(['project_id'=>$p1->id,'board_id'=>$rev1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$tono->id,'judul'=>'Testing halaman login & registrasi','tipe'=>'task','prioritas'=>'high','story_point'=>3,'progres'=>90,'nomor'=>'WEB-007']);
        $t7->labels()->attach([$lblUR->id]);

        $t8 = Task::create(['project_id'=>$p1->id,'board_id'=>$rev1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$tono->id,'judul'=>'Bug: Mobile responsive navbar','tipe'=>'bug','prioritas'=>'critical','story_point'=>2,'progres'=>80,'due_date'=>now()->subDays(1),'nomor'=>'WEB-008']);
        $t8->labels()->attach([$lblFE->id,$lblUR->id]);

        // To Do tasks
        Task::create(['project_id'=>$p1->id,'board_id'=>$todo1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$dika->id,'judul'=>'Integrasi payment gateway Midtrans','tipe'=>'feature','prioritas'=>'high','story_point'=>8,'due_date'=>now()->addDays(14),'nomor'=>'WEB-009']);
        Task::create(['project_id'=>$p1->id,'board_id'=>$todo1->id,'dibuat_oleh'=>$rina->id,'judul'=>'SEO optimization & meta tags','tipe'=>'improvement','prioritas'=>'medium','story_point'=>3,'nomor'=>'WEB-010']);
        Task::create(['project_id'=>$p1->id,'board_id'=>$todo1->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$tono->id,'judul'=>'Load testing & performance audit','tipe'=>'task','prioritas'=>'medium','story_point'=>5,'due_date'=>now()->addDays(20),'nomor'=>'WEB-011']);

        // ── PROJECT 2: Mobile App ─────────────────────────────────────
        $p2     = Project::where('kode','MOB-001')->first();
        $boards2 = $p2->boards->keyBy('nama');
        $todo2   = $boards2['To Do'];
        $prog2   = $boards2['In Progress'];
        $rev2    = $boards2['Review'];
        $done2   = $boards2['Done'];
        $sprint1 = Sprint::where('project_id',$p2->id)->where('nama','like','%Sprint 1%')->first();

        $lblAPI = Label::where('project_id',$p2->id)->where('nama','API')->first();
        $lblUI  = Label::where('project_id',$p2->id)->where('nama','UI')->first();
        $lblBug = Label::where('project_id',$p2->id)->where('nama','Bug')->first();

        // Done
        $m1 = Task::create(['project_id'=>$p2->id,'board_id'=>$done2->id,'sprint_id'=>$sprint1->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$dika->id,'judul'=>'Setup project React Native & arsitektur folder','tipe'=>'task','prioritas'=>'critical','story_point'=>5,'progres'=>100,'selesai_at'=>now()->subDays(12),'nomor'=>'MOB-001']);
        $m1->labels()->attach([$lblUI->id]);

        $m2 = Task::create(['project_id'=>$p2->id,'board_id'=>$done2->id,'sprint_id'=>$sprint1->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$dika->id,'judul'=>'Implementasi autentikasi JWT','tipe'=>'feature','prioritas'=>'critical','story_point'=>8,'progres'=>100,'selesai_at'=>now()->subDays(8),'nomor'=>'MOB-002']);
        $m2->labels()->attach([$lblAPI->id]);

        // In Progress
        $m3 = Task::create(['project_id'=>$p2->id,'board_id'=>$prog2->id,'sprint_id'=>$sprint1->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$fajar->id,'judul'=>'UI Screen: Home Dashboard','tipe'=>'feature','prioritas'=>'high','story_point'=>5,'estimasi_jam'=>12,'aktual_jam'=>7,'progres'=>55,'nomor'=>'MOB-003']);
        $m3->labels()->attach([$lblUI->id]);

        $m4 = Task::create(['project_id'=>$p2->id,'board_id'=>$prog2->id,'sprint_id'=>$sprint1->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$dika->id,'judul'=>'API: User profile & settings endpoint','tipe'=>'feature','prioritas'=>'high','story_point'=>5,'estimasi_jam'=>10,'aktual_jam'=>6,'progres'=>60,'nomor'=>'MOB-004']);
        $m4->labels()->attach([$lblAPI->id]);

        $m5 = Task::create(['project_id'=>$p2->id,'board_id'=>$prog2->id,'sprint_id'=>$sprint1->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$maya->id,'judul'=>'Setup CI/CD pipeline Github Actions','tipe'=>'task','prioritas'=>'medium','story_point'=>3,'estimasi_jam'=>8,'aktual_jam'=>5,'progres'=>70,'nomor'=>'MOB-005']);

        // Review
        $m6 = Task::create(['project_id'=>$p2->id,'board_id'=>$rev2->id,'sprint_id'=>$sprint1->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$tono->id,'judul'=>'QA testing: Login & Register flow','tipe'=>'task','prioritas'=>'high','story_point'=>3,'progres'=>85,'nomor'=>'MOB-006']);

        // To Do
        Task::create(['project_id'=>$p2->id,'board_id'=>$todo2->id,'sprint_id'=>$sprint1->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$fajar->id,'judul'=>'UI Screen: Product listing & detail','tipe'=>'feature','prioritas'=>'high','story_point'=>8,'nomor'=>'MOB-007']);
        Task::create(['project_id'=>$p2->id,'board_id'=>$todo2->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$dika->id,'judul'=>'Push notification integration FCM','tipe'=>'feature','prioritas'=>'medium','story_point'=>5,'due_date'=>now()->addDays(20),'nomor'=>'MOB-008']);
        Task::create(['project_id'=>$p2->id,'board_id'=>$todo2->id,'dibuat_oleh'=>$admin->id,'judul'=>'Bug: Crash saat load gambar besar','tipe'=>'bug','prioritas'=>'critical','story_point'=>2,'nomor'=>'MOB-009']);
        Task::create(['project_id'=>$p2->id,'board_id'=>$todo2->id,'dibuat_oleh'=>$admin->id,'assignee_id'=>$tono->id,'judul'=>'Performance testing & memory profiling','tipe'=>'task','prioritas'=>'medium','story_point'=>3,'nomor'=>'MOB-010']);

        // ── PROJECT 3: E-Commerce ─────────────────────────────────────
        $p3     = Project::where('kode','ECP-001')->first();
        $boards3 = $p3->boards->keyBy('nama');
        $todo3   = $boards3['To Do'];
        $prog3   = $boards3['In Progress'];
        $done3   = $boards3['Done'];

        Task::create(['project_id'=>$p3->id,'board_id'=>$done3->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$dika->id,'judul'=>'Analisa kebutuhan & ERD database','tipe'=>'task','prioritas'=>'critical','story_point'=>5,'progres'=>100,'selesai_at'=>now()->subDays(5),'nomor'=>'ECP-001']);
        Task::create(['project_id'=>$p3->id,'board_id'=>$prog3->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$sari->id,'judul'=>'Desain UI/UX marketplace','tipe'=>'feature','prioritas'=>'high','story_point'=>8,'progres'=>40,'nomor'=>'ECP-002']);
        Task::create(['project_id'=>$p3->id,'board_id'=>$prog3->id,'dibuat_oleh'=>$rina->id,'assignee_id'=>$dika->id,'judul'=>'Setup Laravel API & database migration','tipe'=>'task','prioritas'=>'high','story_point'=>5,'progres'=>30,'nomor'=>'ECP-003']);
        Task::create(['project_id'=>$p3->id,'board_id'=>$todo3->id,'dibuat_oleh'=>$rina->id,'judul'=>'Fitur: Multi-seller registration & onboarding','tipe'=>'feature','prioritas'=>'high','story_point'=>13,'nomor'=>'ECP-004']);
        Task::create(['project_id'=>$p3->id,'board_id'=>$todo3->id,'dibuat_oleh'=>$rina->id,'judul'=>'Integrasi payment & escrow system','tipe'=>'feature','prioritas'=>'critical','story_point'=>13,'nomor'=>'ECP-005']);

        // Tambah beberapa task activities
        TaskActivity::create(['task_id'=>$t4->id,'user_id'=>$fajar->id,'aksi'=>'moved','field'=>'board','nilai_lama'=>'To Do','nilai_baru'=>'In Progress','keterangan'=>'Mulai pengerjaan homepage']);
        TaskActivity::create(['task_id'=>$t8->id,'user_id'=>$tono->id,'aksi'=>'created','keterangan'=>'Bug ditemukan saat testing di iPhone 12']);
        TaskActivity::create(['task_id'=>$m3->id,'user_id'=>$fajar->id,'aksi'=>'updated','field'=>'progres','nilai_lama'=>'30','nilai_baru'=>'55']);
    }
}
