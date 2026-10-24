<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Label;
use App\Models\Sprint;
use App\Models\Workspace;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $ws1   = Workspace::where('slug','techcorp-indonesia')->first();
        $ws2   = Workspace::where('slug','startupku')->first();
        $admin = User::where('email','admin@pm.test')->first();
        $rina  = User::where('email','rina@pm.test')->first();
        $dika  = User::where('email','dika@pm.test')->first();
        $sari  = User::where('email','sari@pm.test')->first();
        $fajar = User::where('email','fajar@pm.test')->first();
        $tono  = User::where('email','tono@pm.test')->first();
        $maya  = User::where('email','maya@pm.test')->first();
        $rudi  = User::where('email','rudi@pm.test')->first();

        // Project 1 — Website Redesign (Kanban)
        $p1 = Project::create([
            'workspace_id'    => $ws1->id,
            'dibuat_oleh'     => $rina->id,
            'nama'            => 'Website Redesign 2024',
            'kode'            => 'WEB-001',
            'deskripsi'       => 'Redesign total website perusahaan dengan tampilan modern',
            'warna'           => '#3B82F6',
            'status'          => 'aktif',
            'tipe'            => 'kanban',
            'tanggal_mulai'   => now()->subDays(30),
            'tanggal_selesai' => now()->addDays(60),
        ]);

        $members1 = [[$rina->id,'manager'],[$dika->id,'member'],[$sari->id,'member'],[$fajar->id,'member'],[$tono->id,'member']];
        foreach ($members1 as [$uid,$role]) ProjectMember::create(['project_id'=>$p1->id,'user_id'=>$uid,'role'=>$role]);

        // Labels project 1
        foreach ([['Frontend','#3B82F6'],['Backend','#10B981'],['Design','#8B5CF6'],['Testing','#F59E0B'],['Urgent','#EF4444'],['Nice to Have','#6B7280']] as [$nama,$warna]) {
            Label::create(['project_id'=>$p1->id,'nama'=>$nama,'warna'=>$warna]);
        }

        // Project 2 — Mobile App (Scrum)
        $p2 = Project::create([
            'workspace_id'    => $ws1->id,
            'dibuat_oleh'     => $admin->id,
            'nama'            => 'Mobile App Development',
            'kode'            => 'MOB-001',
            'deskripsi'       => 'Pengembangan aplikasi mobile iOS dan Android',
            'warna'           => '#10B981',
            'status'          => 'aktif',
            'tipe'            => 'scrum',
            'tanggal_mulai'   => now()->subDays(14),
            'tanggal_selesai' => now()->addDays(90),
        ]);

        $members2 = [[$admin->id,'manager'],[$dika->id,'member'],[$fajar->id,'member'],[$tono->id,'member'],[$maya->id,'member'],[$rudi->id,'viewer']];
        foreach ($members2 as [$uid,$role]) ProjectMember::create(['project_id'=>$p2->id,'user_id'=>$uid,'role'=>$role]);

        // Labels project 2
        foreach ([['iOS','#007AFF'],['Android','#3DDC84'],['API','#10B981'],['UI','#8B5CF6'],['Bug','#EF4444'],['Sprint 1','#F59E0B']] as [$nama,$warna]) {
            Label::create(['project_id'=>$p2->id,'nama'=>$nama,'warna'=>$warna]);
        }

        // Sprint untuk project 2
        Sprint::create([
            'project_id'      => $p2->id,
            'nama'            => 'Sprint 1 — Foundation',
            'tujuan'          => 'Setup arsitektur dan fitur autentikasi',
            'tanggal_mulai'   => now()->subDays(14),
            'tanggal_selesai' => now()->addDays(0),
            'status'          => 'aktif',
            'kapasitas'       => 40,
        ]);

        Sprint::create([
            'project_id'      => $p2->id,
            'nama'            => 'Sprint 2 — Core Features',
            'tujuan'          => 'Fitur utama aplikasi mobile',
            'tanggal_mulai'   => now()->addDays(1),
            'tanggal_selesai' => now()->addDays(14),
            'status'          => 'perencanaan',
            'kapasitas'       => 50,
        ]);

        // Project 3 — Startup (ws2)
        $p3 = Project::create([
            'workspace_id'    => $ws2->id,
            'dibuat_oleh'     => $rina->id,
            'nama'            => 'E-Commerce Platform',
            'kode'            => 'ECP-001',
            'deskripsi'       => 'Platform e-commerce untuk UMKM Indonesia',
            'warna'           => '#F59E0B',
            'status'          => 'aktif',
            'tipe'            => 'kanban',
            'tanggal_mulai'   => now()->subDays(7),
            'tanggal_selesai' => now()->addDays(120),
        ]);

        ProjectMember::create(['project_id'=>$p3->id,'user_id'=>$rina->id,'role'=>'manager']);
        ProjectMember::create(['project_id'=>$p3->id,'user_id'=>$dika->id,'role'=>'member']);
        ProjectMember::create(['project_id'=>$p3->id,'user_id'=>$sari->id,'role'=>'member']);

        foreach ([['Frontend','#3B82F6'],['Backend','#10B981'],['Database','#8B5CF6'],['DevOps','#EF4444']] as [$nama,$warna]) {
            Label::create(['project_id'=>$p3->id,'nama'=>$nama,'warna'=>$warna]);
        }
    }
}
