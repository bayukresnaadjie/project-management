<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\User;

class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email','admin@pm.test')->first();
        $rina  = User::where('email','rina@pm.test')->first();
        $dika  = User::where('email','dika@pm.test')->first();
        $sari  = User::where('email','sari@pm.test')->first();
        $fajar = User::where('email','fajar@pm.test')->first();
        $tono  = User::where('email','tono@pm.test')->first();
        $maya  = User::where('email','maya@pm.test')->first();
        $rudi  = User::where('email','rudi@pm.test')->first();

        // Workspace 1 — Tech Company
        $ws1 = Workspace::create([
            'owner_id'   => $admin->id,
            'nama'       => 'TechCorp Indonesia',
            'slug'       => 'techcorp-indonesia',
            'deskripsi'  => 'Workspace utama TechCorp untuk semua project digital',
            'warna'      => '#3B82F6',
        ]);

        // Members workspace 1
        $members1 = [
            [$rina->id,  'admin'],
            [$dika->id,  'member'],
            [$sari->id,  'member'],
            [$fajar->id, 'member'],
            [$tono->id,  'member'],
            [$maya->id,  'member'],
            [$rudi->id,  'viewer'],
        ];
        foreach ($members1 as [$userId, $role]) {
            WorkspaceMember::create([
                'workspace_id' => $ws1->id,
                'user_id'      => $userId,
                'role'         => $role,
                'joined_at'    => now()->subDays(rand(10,30)),
            ]);
        }

        // Workspace 2 — Startup
        $ws2 = Workspace::create([
            'owner_id'   => $rina->id,
            'nama'       => 'StartupKu',
            'slug'       => 'startupku',
            'deskripsi'  => 'Workspace untuk project startup rintisan',
            'warna'      => '#8B5CF6',
        ]);

        WorkspaceMember::create(['workspace_id'=>$ws2->id,'user_id'=>$admin->id,'role'=>'admin','joined_at'=>now()]);
        WorkspaceMember::create(['workspace_id'=>$ws2->id,'user_id'=>$dika->id,'role'=>'member','joined_at'=>now()]);
        WorkspaceMember::create(['workspace_id'=>$ws2->id,'user_id'=>$sari->id,'role'=>'member','joined_at'=>now()]);
    }
}
