<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function create(Request $request)
    {
        $user       = auth()->user();
        $workspaces = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id)
                ->whereIn('role', ['owner','admin','member']))
            ->get();
        $workspaceId = $request->get('workspace_id');
        return view('project.create', compact('workspaces','workspaceId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'workspace_id'    => 'required|exists:workspaces,id',
            'nama'            => 'required|string|max:100',
            'deskripsi'       => 'nullable|string',
            'warna'           => 'nullable|string|max:20',
            'tipe'            => 'required|in:scrum,kanban,basic',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'publik'          => 'sometimes|boolean',
        ]);

        $project = Project::create(array_merge($validated, [
            'dibuat_oleh' => $user->id,
            'publik'      => $request->boolean('publik', false),
        ]));

        // Pembuat otomatis jadi manager
        ProjectMember::create(['project_id'=>$project->id,'user_id'=>$user->id,'role'=>'manager']);

        return redirect()->route('project.board', $project)->with('success', 'Project berhasil dibuat.');
    }

    public function show(Project $project)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        $project->load(['workspace','members.user','boards.tasks.assignee','sprints','labels']);
        $role = $project->getRoleUser($user);

        // Statistik
        $stats = [
            'total'       => $project->tasks()->whereNull('parent_id')->count(),
            'selesai'     => $project->tasks()->whereNull('parent_id')->whereHas('board', fn($q) => $q->where('is_done',true))->count(),
            'in_progress' => $project->tasks()->whereNull('parent_id')->whereHas('board', fn($q) => $q->where('nama','In Progress'))->count(),
            'overdue'     => $project->tasks()->whereNull('parent_id')->whereNotNull('due_date')->where('due_date','<',today())->whereHas('board', fn($q) => $q->where('is_done',false))->count(),
        ];

        return view('project.show', compact('project','role','stats'));
    }

    public function board(Project $project)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        $project->load(['workspace','labels','members.user']);
        $boards = $project->boards()->with(['tasks' => function($q) {
            $q->whereNull('parent_id')
              ->with(['assignees','labels','subTasks'])
              ->orderBy('urutan');
        }])->get();

        $role    = $project->getRoleUser($user);
        $members = $project->members()->with('user')->get();
        $labels  = $project->labels;

        return view('project.board', compact('project','boards','role','members','labels'));
    }

    public function settings(Project $project)
    {
        $user = auth()->user();
        if (!in_array($project->getRoleUser($user), ['manager'])) abort(403);

        $project->load(['members.user','workspace.members.user']);
        $workspaceMembers = $project->workspace->users()
            ->whereNotIn('users.id', $project->members()->pluck('user_id'))
            ->get();

        return view('project.settings', compact('project','workspaceMembers'));
    }

    public function update(Request $request, Project $project)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);

        $validated = $request->validate([
            'nama'            => 'required|string|max:100',
            'deskripsi'       => 'nullable|string',
            'warna'           => 'nullable|string|max:20',
            'status'          => 'required|in:aktif,selesai,ditunda,dibatalkan',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'publik'          => 'sometimes|boolean',
        ]);

        $project->update(array_merge($validated, ['publik' => $request->boolean('publik', false)]));
        return back()->with('success', 'Project berhasil diperbarui.');
    }

    public function addMember(Request $request, Project $project)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|in:manager,member,viewer',
        ]);

        if ($project->members()->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'User sudah menjadi anggota project.');
        }

        ProjectMember::create(['project_id'=>$project->id,'user_id'=>$request->user_id,'role'=>$request->role]);
        return back()->with('success', 'Member berhasil ditambahkan.');
    }

    public function removeMember(Project $project, User $user)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);
        if ($user->id === $project->dibuat_oleh) return back()->with('error', 'Tidak bisa menghapus Project Manager.');
        ProjectMember::where('project_id',$project->id)->where('user_id',$user->id)->delete();
        return back()->with('success', 'Member berhasil dihapus.');
    }
}
