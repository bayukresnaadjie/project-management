<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskActivity;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Project $project)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        $project->load(['members.user','boards','sprints']);

        // Task per board
        $perBoard = $project->boards->map(fn($b) => [
            'nama'  => $b->nama,
            'warna' => $b->warna,
            'total' => $b->tasks()->whereNull('parent_id')->count(),
        ]);

        // Task per prioritas
        $perPrioritas = Task::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->selectRaw('prioritas, count(*) as total')
            ->groupBy('prioritas')
            ->get();

        // Task per tipe
        $perTipe = Task::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->selectRaw('tipe, count(*) as total')
            ->groupBy('tipe')
            ->get();

        // Task per anggota
        $perMember = $project->members()->with('user')->get()->map(fn($m) => [
            'nama'        => $m->user->name,
            'total'       => Task::where('project_id',$project->id)->where('assignee_id',$m->user->id)->whereNull('parent_id')->count(),
            'selesai'     => Task::where('project_id',$project->id)->where('assignee_id',$m->user->id)->whereNull('parent_id')->whereHas('board', fn($q) => $q->where('is_done',true))->count(),
            'in_progress' => Task::where('project_id',$project->id)->where('assignee_id',$m->user->id)->whereNull('parent_id')->whereHas('board', fn($q) => $q->where('nama','In Progress'))->count(),
        ]);

        // Aktivitas 14 hari
        $aktivitas14Hari = collect(range(13, 0))->map(fn($d) => [
            'tanggal' => today()->subDays($d)->format('d M'),
            'total'   => TaskActivity::whereHas('task', fn($q) => $q->where('project_id',$project->id))
                ->whereDate('created_at', today()->subDays($d))->count(),
        ]);

        // Statistik umum
        $stats = [
            'total'         => $project->tasks()->whereNull('parent_id')->count(),
            'selesai'       => $project->tasks()->whereNull('parent_id')->whereHas('board', fn($q) => $q->where('is_done',true))->count(),
            'overdue'       => $project->tasks()->whereNull('parent_id')->whereNotNull('due_date')->where('due_date','<',today())->whereHas('board', fn($q) => $q->where('is_done',false))->count(),
            'tanpa_assignee'=> $project->tasks()->whereNull('parent_id')->whereNull('assignee_id')->count(),
            'progress'      => $project->progress,
        ];

        return view('laporan.index', compact('project','perBoard','perPrioritas','perTipe','perMember','aktivitas14Hari','stats'));
    }
}
