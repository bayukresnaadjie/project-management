<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $workspaces = Workspace::where('owner_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->with(['owner','members.user'])
            ->withCount('projects')
            ->get();

        return view('workspace.index', compact('workspaces'));
    }

    public function create()
    {
        return view('workspace.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'warna'     => 'nullable|string|max:20',
        ]);

        $workspace = Workspace::create(array_merge($validated, [
            'owner_id' => auth()->id(),
            'slug'     => Str::slug($validated['nama']) . '-' . Str::random(6),
        ]));

        // Owner otomatis jadi member
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id'      => auth()->id(),
            'role'         => 'owner',
            'joined_at'    => now(),
        ]);

        return redirect()->route('workspace.show', $workspace)->with('success', 'Workspace berhasil dibuat.');
    }

    public function show(Workspace $workspace)
    {
        $user = auth()->user();
        if (!$workspace->userBisaAkses($user)) abort(403);

        $workspace->load(['owner','members.user','projects.boards.tasks']);
        $projects = $workspace->projects()->with(['members.user','boards'])->orderByDesc('updated_at')->get();
        $members  = $workspace->members()->with('user')->get();
        $role     = $workspace->getRoleUser($user);

        return view('workspace.show', compact('workspace','projects','members','role'));
    }

    public function edit(Workspace $workspace)
    {
        if ($workspace->owner_id !== auth()->id()) abort(403);
        return view('workspace.edit', compact('workspace'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        if ($workspace->owner_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'warna'     => 'nullable|string|max:20',
        ]);

        $workspace->update($validated);
        return redirect()->route('workspace.show', $workspace)->with('success', 'Workspace berhasil diperbarui.');
    }

    public function invite(Request $request, Workspace $workspace)
    {
        if (!in_array($workspace->getRoleUser(auth()->user()), ['owner','admin'])) abort(403);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role'  => 'required|in:admin,member,viewer',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($workspace->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User sudah menjadi anggota workspace ini.');
        }

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $user->id,
            'role'         => $request->role,
            'joined_at'    => now(),
        ]);

        return back()->with('success', "{$user->name} berhasil diundang ke workspace.");
    }

    public function removeMember(Workspace $workspace, User $user)
    {
        if ($workspace->owner_id !== auth()->id()) abort(403);
        if ($user->id === $workspace->owner_id) return back()->with('error', 'Owner tidak bisa dihapus.');

        WorkspaceMember::where('workspace_id', $workspace->id)->where('user_id', $user->id)->delete();
        return back()->with('success', 'Member berhasil dihapus.');
    }
}
