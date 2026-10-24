<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Board;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Label;
use App\Models\TaskComment;
use App\Models\TaskAttachment;
use App\Models\TaskActivity;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        $validated = $request->validate([
            'judul'       => 'required|string|max:255',
            'board_id'    => 'required|exists:boards,id',
            'tipe'        => 'required|in:task,bug,feature,improvement,epic',
            'prioritas'   => 'required|in:critical,high,medium,low',
            'assignee_id' => 'nullable|exists:users,id',
            'sprint_id'   => 'nullable|exists:sprints,id',
            'due_date'    => 'nullable|date',
            'story_point' => 'nullable|integer|min:0',
            'deskripsi'   => 'nullable|string',
            'parent_id'   => 'nullable|exists:tasks,id',
            'labels'      => 'nullable|array',
            'labels.*'    => 'exists:labels,id',
        ]);

        $task = Task::create(array_merge($validated, [
            'project_id'  => $project->id,
            'dibuat_oleh' => $user->id,
        ]));

        if (!empty($validated['labels'])) {
            $task->labels()->attach($validated['labels']);
        }

        if ($validated['assignee_id']) {
            $task->assignees()->attach($validated['assignee_id']);
            // Notifikasi assignee
            Notification::kirim(
                $validated['assignee_id'],
                'Task Baru Ditugaskan',
                "{$user->name} menugaskan task '{$task->judul}' ke Anda",
                'assigned',
                route('task.show', [$project, $task]),
                $task->id, $project->id
            );
        }

        Task::catatAktivitas($task->id, 'created', null, null, null, 'Task dibuat');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'task' => $task->load(['assignees','labels','board'])]);
        }

        return redirect()->route('project.board', $project)->with('success', 'Task berhasil dibuat.');
    }

    public function show(Project $project, Task $task)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        $task->load([
            'board','sprint','assignees','labels',
            'dibuatOleh','subTasks.board','subTasks.assignees',
            'comments.user','comments.replies.user',
            'attachments.user','activities.user',
            'parent'
        ]);

        $boards   = $project->boards;
        $members  = $project->members()->with('user')->get();
        $labels   = $project->labels;
        $sprints  = $project->sprints;

        return view('task.show', compact('project','task','boards','members','labels','sprints'));
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $user = auth()->user();
        if (!$project->userBisaAkses($user)) abort(403);

        $validated = $request->validate([
            'judul'        => 'sometimes|required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'tipe'         => 'sometimes|in:task,bug,feature,improvement,epic',
            'prioritas'    => 'sometimes|in:critical,high,medium,low',
            'assignee_id'  => 'nullable|exists:users,id',
            'sprint_id'    => 'nullable|exists:sprints,id',
            'due_date'     => 'nullable|date',
            'story_point'  => 'nullable|integer|min:0',
            'estimasi_jam' => 'nullable|integer|min:0',
            'aktual_jam'   => 'nullable|integer|min:0',
            'progres'      => 'nullable|integer|min:0|max:100',
        ]);

        // Catat perubahan
        foreach (['judul','prioritas','tipe','progres','assignee_id'] as $field) {
            if (isset($validated[$field]) && $task->$field != $validated[$field]) {
                Task::catatAktivitas($task->id, 'updated', $field, $task->$field, $validated[$field]);
            }
        }

        // Notifikasi jika assignee berubah
        if (isset($validated['assignee_id']) && $validated['assignee_id'] && $task->assignee_id !== (int)$validated['assignee_id']) {
            Notification::kirim(
                $validated['assignee_id'],
                'Task Ditugaskan ke Anda',
                "{$user->name} menugaskan task '{$task->judul}' ke Anda",
                'assigned',
                route('task.show', [$project, $task]),
                $task->id, $project->id
            );
        }

        $task->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task berhasil diperbarui.');
    }

    public function moveBoard(Request $request, Project $project, Task $task)
    {
        $request->validate(['board_id' => 'required|exists:boards,id']);

        $boardLama = $task->board->nama;
        $boardBaru = Board::find($request->board_id);

        $task->update([
            'board_id'   => $request->board_id,
            'selesai_at' => $boardBaru->is_done ? now() : null,
            'progres'    => $boardBaru->is_done ? 100 : $task->progres,
        ]);

        Task::catatAktivitas($task->id, 'moved', 'board', $boardLama, $boardBaru->nama);

        // Notifikasi pembuat task jika task selesai
        if ($boardBaru->is_done && $task->dibuat_oleh !== auth()->id()) {
            Notification::kirim(
                $task->dibuat_oleh,
                'Task Selesai',
                auth()->user()->name . " menyelesaikan task '{$task->judul}'",
                'completed',
                route('task.show', [$project, $task]),
                $task->id, $project->id
            );
        }

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Project $project)
    {
        $request->validate(['tasks' => 'required|array', 'tasks.*.id' => 'required|exists:tasks,id', 'tasks.*.board_id' => 'required|exists:boards,id', 'tasks.*.urutan' => 'required|integer']);

        foreach ($request->tasks as $item) {
            Task::where('id', $item['id'])->update(['board_id' => $item['board_id'], 'urutan' => $item['urutan']]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Project $project, Task $task)
    {
        if (!in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);
        $task->delete();
        return back()->with('success', 'Task berhasil dihapus.');
    }

    // ── Komentar ──────────────────────────────────────────────────────────────
    public function storeComment(Request $request, Project $project, Task $task)
    {
        $request->validate(['isi' => 'required|string|max:2000', 'parent_id' => 'nullable|exists:task_comments,id']);

        TaskComment::create([
            'task_id'   => $task->id,
            'user_id'   => auth()->id(),
            'parent_id' => $request->parent_id,
            'isi'       => $request->isi,
        ]);

        Task::catatAktivitas($task->id, 'commented');

        // Notifikasi assignee & pembuat
        $notifTo = collect([$task->assignee_id, $task->dibuat_oleh])->unique()->filter();
        foreach ($notifTo as $uid) {
            Notification::kirim($uid, 'Komentar Baru', auth()->user()->name . " mengomentari '{$task->judul}'", 'commented', route('task.show',[$project,$task]), $task->id, $project->id);
        }

        return back()->with('success', 'Komentar ditambahkan.');
    }

    public function destroyComment(Project $project, Task $task, TaskComment $comment)
    {
        if ($comment->user_id !== auth()->id() && !in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);
        $comment->delete();
        return back()->with('success', 'Komentar dihapus.');
    }

    // ── Attachment ────────────────────────────────────────────────────────────
    public function storeAttachment(Request $request, Project $project, Task $task)
    {
        $request->validate(['file' => 'required|file|max:20480']);

        $file = $request->file('file');
        $path = $file->store('task-attachments/' . $task->id, 'public');

        TaskAttachment::create([
            'task_id'   => $task->id,
            'user_id'   => auth()->id(),
            'nama_file' => $file->getClientOriginalName(),
            'path_file' => $path,
            'mime_type' => $file->getMimeType(),
            'ukuran'    => $file->getSize(),
        ]);

        Task::catatAktivitas($task->id, 'attached', null, null, $file->getClientOriginalName());
        return back()->with('success', 'File berhasil dilampirkan.');
    }

    public function downloadAttachment(Project $project, Task $task, TaskAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->path_file)) abort(404);
        return Storage::disk('public')->download($attachment->path_file, $attachment->nama_file);
    }

    public function destroyAttachment(Project $project, Task $task, TaskAttachment $attachment)
    {
        if ($attachment->user_id !== auth()->id() && !in_array($project->getRoleUser(auth()->user()), ['manager'])) abort(403);
        Storage::disk('public')->delete($attachment->path_file);
        $attachment->delete();
        return back()->with('success', 'Lampiran dihapus.');
    }
}
