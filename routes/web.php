<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LaporanController;

// ─── AUTH ─────────────────────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate(['email'=>'required|email','password'=>'required']);
    if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        auth()->user()->update(['last_seen' => now()]);
        return redirect()->intended(route('dashboard'));
    }
    return back()->withErrors(['email'=>'Email atau password salah.'])->onlyInput('email');
})->name('login.post');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// ─── AUTHENTICATED ────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Workspace
    Route::get('/workspace',                          [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('/workspace/create',                   [WorkspaceController::class, 'create'])->name('workspace.create');
    Route::post('/workspace',                         [WorkspaceController::class, 'store'])->name('workspace.store');
    Route::get('/workspace/{workspace}',              [WorkspaceController::class, 'show'])->name('workspace.show');
    Route::get('/workspace/{workspace}/edit',         [WorkspaceController::class, 'edit'])->name('workspace.edit');
    Route::put('/workspace/{workspace}',              [WorkspaceController::class, 'update'])->name('workspace.update');
    Route::post('/workspace/{workspace}/invite',      [WorkspaceController::class, 'invite'])->name('workspace.invite');
    Route::delete('/workspace/{workspace}/member/{user}', [WorkspaceController::class, 'removeMember'])->name('workspace.remove-member');

    // Project
    Route::get('/project/create',                     [ProjectController::class, 'create'])->name('project.create');
    Route::post('/project',                           [ProjectController::class, 'store'])->name('project.store');
    Route::get('/project/{project}',                  [ProjectController::class, 'show'])->name('project.show');
    Route::get('/project/{project}/board',            [ProjectController::class, 'board'])->name('project.board');
    Route::get('/project/{project}/settings',         [ProjectController::class, 'settings'])->name('project.settings');
    Route::put('/project/{project}',                  [ProjectController::class, 'update'])->name('project.update');
    Route::post('/project/{project}/member',          [ProjectController::class, 'addMember'])->name('project.add-member');
    Route::delete('/project/{project}/member/{user}', [ProjectController::class, 'removeMember'])->name('project.remove-member');

    // Task
    Route::post('/project/{project}/task',                    [TaskController::class, 'store'])->name('task.store');
    Route::get('/project/{project}/task/{task}',              [TaskController::class, 'show'])->name('task.show');
    Route::put('/project/{project}/task/{task}',              [TaskController::class, 'update'])->name('task.update');
    Route::patch('/project/{project}/task/{task}/move',       [TaskController::class, 'moveBoard'])->name('task.move');
    Route::post('/project/{project}/task/reorder',            [TaskController::class, 'reorder'])->name('task.reorder');
    Route::delete('/project/{project}/task/{task}',           [TaskController::class, 'destroy'])->name('task.destroy');

    // Komentar
    Route::post('/project/{project}/task/{task}/comment',     [TaskController::class, 'storeComment'])->name('task.comment.store');
    Route::delete('/project/{project}/task/{task}/comment/{comment}', [TaskController::class, 'destroyComment'])->name('task.comment.destroy');

    // Attachment
    Route::post('/project/{project}/task/{task}/attachment',          [TaskController::class, 'storeAttachment'])->name('task.attachment.store');
    Route::get('/project/{project}/task/{task}/attachment/{attachment}',[TaskController::class, 'downloadAttachment'])->name('task.attachment.download');
    Route::delete('/project/{project}/task/{task}/attachment/{attachment}', [TaskController::class, 'destroyAttachment'])->name('task.attachment.destroy');

    // Sprint
    Route::get('/project/{project}/sprint',           [SprintController::class, 'index'])->name('sprint.index');
    Route::post('/project/{project}/sprint',          [SprintController::class, 'store'])->name('sprint.store');
    Route::put('/project/{project}/sprint/{sprint}',  [SprintController::class, 'update'])->name('sprint.update');
    Route::patch('/project/{project}/sprint/{sprint}/mulai',  [SprintController::class, 'mulai'])->name('sprint.mulai');
    Route::patch('/project/{project}/sprint/{sprint}/selesai',[SprintController::class, 'selesai'])->name('sprint.selesai');
    Route::post('/project/{project}/sprint/{sprint}/task',    [SprintController::class, 'addTask'])->name('sprint.add-task');
    Route::delete('/project/{project}/sprint/{sprint}/task/{task}', [SprintController::class, 'removeTask'])->name('sprint.remove-task');
    Route::get('/project/{project}/sprint/{sprint}/burndown', [SprintController::class, 'burndown'])->name('sprint.burndown');

    // Laporan
    Route::get('/project/{project}/laporan',          [LaporanController::class, 'index'])->name('laporan.index');

    // Notifikasi
    Route::get('/notifikasi',                         [NotificationController::class, 'index'])->name('notification.index');
    Route::delete('/notifikasi/hapus-semua',          [NotificationController::class, 'hapusSemua'])->name('notification.hapus-semua');
    Route::get('/api/notifikasi/unread',              [NotificationController::class, 'getUnread'])->name('notification.unread');
});
