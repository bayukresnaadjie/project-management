<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function index(Project $project)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        $sprints = $project->sprints()->with(['tasks.board','tasks.assignees'])->orderByDesc('created_at')->get();
        $role    = $project->getRoleUser($user);

        // Backlog — task tanpa sprint
        $backlog = $project->tasks()->whereNull('sprint_id')->whereNull('parent_id')
            ->with(['board','assignees','labels'])->orderBy('urutan')->get();

        return view('sprint.index', compact('project','sprints','backlog','role'));
    }

    public function store(Request $request, Project $project)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);

        $validated = $request->validate([
            'nama'            => 'required|string|max:100',
            'tujuan'          => 'nullable|string',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'kapasitas'       => 'nullable|integer|min:0',
        ]);

        Sprint::create(array_merge($validated, ['project_id' => $project->id]));
        return back()->with('success', 'Sprint berhasil dibuat.');
    }

    public function update(Request $request, Project $project, Sprint $sprint)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);

        $validated = $request->validate([
            'nama'            => 'required|string|max:100',
            'tujuan'          => 'nullable|string',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'kapasitas'       => 'nullable|integer|min:0',
        ]);

        $sprint->update($validated);
        return back()->with('success', 'Sprint berhasil diperbarui.');
    }

    public function mulai(Project $project, Sprint $sprint)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);

        // Pastikan tidak ada sprint aktif lain
        if ($project->sprints()->where('status','aktif')->exists()) {
            return back()->with('error', 'Sudah ada sprint yang sedang aktif.');
        }

        $sprint->update(['status' => 'aktif', 'tanggal_mulai' => $sprint->tanggal_mulai ?? today()]);
        return back()->with('success', "Sprint '{$sprint->nama}' dimulai.");
    }

    public function selesai(Project $project, Sprint $sprint)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);

        // Pindahkan task yang belum selesai ke backlog
        $taskBelumSelesai = $sprint->tasks()->whereHas('board', fn($q) => $q->where('is_done', false))->count();

        $sprint->update(['status' => 'selesai', 'tanggal_selesai' => today()]);

        // Lepaskan sprint dari task yang belum selesai
        $sprint->tasks()->whereHas('board', fn($q) => $q->where('is_done', false))
            ->update(['sprint_id' => null]);

        return back()->with('success', "Sprint selesai. {$taskBelumSelesai} task dikembalikan ke backlog.");
    }

    public function addTask(Request $request, Project $project, Sprint $sprint)
    {
        $request->validate(['task_id' => 'required|exists:tasks,id']);
        Task::where('id', $request->task_id)->update(['sprint_id' => $sprint->id]);
        return back()->with('success', 'Task berhasil ditambahkan ke sprint.');
    }

    public function removeTask(Project $project, Sprint $sprint, Task $task)
    {
        $task->update(['sprint_id' => null]);
        return back()->with('success', 'Task dipindahkan ke backlog.');
    }

    public function burndown(Project $project, Sprint $sprint)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        // Data burndown chart
        $totalPoint = $sprint->total_point;
        $mulai      = $sprint->tanggal_mulai ?? today();
        $selesai    = $sprint->tanggal_selesai ?? today()->addDays(14);
        $hari       = $mulai->diffInDays($selesai) + 1;

        // Ideal burndown
        $idealPerHari = $hari > 1 ? $totalPoint / ($hari - 1) : 0;
        $idealLine    = collect(range(0, $hari - 1))->map(fn($d) => round($totalPoint - ($idealPerHari * $d), 1));

        // Aktual burndown (berdasarkan selesai_at task)
        $aktualLine = collect(range(0, $hari - 1))->map(function($d) use ($sprint, $mulai, $totalPoint) {
            $tgl  = $mulai->copy()->addDays($d);
            if ($tgl->isFuture()) return null;
            $done = $sprint->tasks()->whereHas('board', fn($q) => $q->where('is_done', true))
                ->where('selesai_at', '<=', $tgl->endOfDay())->sum('story_point');
            return max(0, $totalPoint - $done);
        });

        $labels = collect(range(0, $hari - 1))->map(fn($d) => $mulai->copy()->addDays($d)->format('d M'));

        return view('sprint.burndown', compact('project','sprint','labels','idealLine','aktualLine','totalPoint'));
    }
}
