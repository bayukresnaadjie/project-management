<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name'=>'Bayu Admin',      'email'=>'admin@pm.test',   'jabatan'=>'Project Director'],
            ['name'=>'Rina Manager',    'email'=>'rina@pm.test',    'jabatan'=>'Project Manager'],
            ['name'=>'Dika Developer',  'email'=>'dika@pm.test',    'jabatan'=>'Backend Developer'],
            ['name'=>'Sari Designer',   'email'=>'sari@pm.test',    'jabatan'=>'UI/UX Designer'],
            ['name'=>'Fajar Frontend',  'email'=>'fajar@pm.test',   'jabatan'=>'Frontend Developer'],
            ['name'=>'Tono QA',         'email'=>'tono@pm.test',    'jabatan'=>'QA Engineer'],
            ['name'=>'Maya DevOps',     'email'=>'maya@pm.test',    'jabatan'=>'DevOps Engineer'],
            ['name'=>'Rudi Analyst',    'email'=>'rudi@pm.test',    'jabatan'=>'Business Analyst'],
        ];

        foreach ($users as $u) {
            User::create([
                'name'              => $u['name'],
                'email'             => $u['email'],
                'jabatan'           => $u['jabatan'],
                'aktif'             => true,
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
    }
}
