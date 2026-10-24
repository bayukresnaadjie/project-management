<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Workspace yang diakses user
        $workspaces = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->withCount('projects')
            ->with('owner')
            ->get();

        // Project yang diakses user
        $projects = Project::where('dibuat_oleh', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->where('status','aktif')
            ->with(['workspace','boards.tasks'])
            ->withCount('tasks')
            ->orderByDesc('updated_at')
            ->take(6)->get();

        // Task yang di-assign ke user
        $myTasks = Task::where('assignee_id', $user->id)
            ->orWhereHas('assignees', fn($q) => $q->where('users.id', $user->id))
            ->whereNull('parent_id')
            ->whereDoesntHave('board', fn($q) => $q->where('is_done', true))
            ->with(['project','board','labels'])
            ->orderBy('due_date')
            ->take(8)->get();

        // Task overdue
        $overdueTask = Task::where('assignee_id', $user->id)
            ->whereNull('parent_id')
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->whereDoesntHave('board', fn($q) => $q->where('is_done', true))
            ->count();

        // Statistik
        $totalTask      = Task::whereHas('project', fn($q) => $q->where('dibuat_oleh',$user->id)
            ->orWhereHas('members', fn($q2) => $q2->where('user_id',$user->id)))->whereNull('parent_id')->count();
        $taskSelesai    = Task::where('assignee_id', $user->id)->whereHas('board', fn($q) => $q->where('is_done',true))->count();
        $taskInProgress = Task::where('assignee_id', $user->id)->whereHas('board', fn($q) => $q->where('nama','In Progress'))->count();

        // Notifikasi belum dibaca
        $notifCount = Notification::where('user_id', $user->id)->where('dibaca', false)->count();

        // Aktivitas terbaru di semua project user
        $aktivitasTerbaru = \App\Models\TaskActivity::with(['user','task.project'])
            ->whereHas('task.project', fn($q) => $q->where('dibuat_oleh',$user->id)
                ->orWhereHas('members', fn($q2) => $q2->where('user_id',$user->id)))
            ->orderByDesc('created_at')
            ->take(8)->get();

        return view('dashboard', compact(
            'workspaces','projects','myTasks','overdueTask',
            'totalTask','taskSelesai','taskInProgress',
            'notifCount','aktivitasTerbaru'
        ));
    }
}
