@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle','Selamat datang kembali, ' . auth()->user()->name)
@section('header-action')
<a href="{{ route('project.create') }}" class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">+ Buat Project</a>
@endsection
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3"><span class="text-xs text-gray-500">My Tasks</span><span class="text-2xl">✅</span></div>
        <div class="text-2xl font-bold text-gray-900">{{ $totalTask }}</div>
        <div class="text-xs text-gray-400 mt-1">total task</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3"><span class="text-xs text-gray-500">Selesai</span><span class="text-2xl">🎯</span></div>
        <div class="text-2xl font-bold text-green-600">{{ $taskSelesai }}</div>
        <div class="text-xs text-gray-400 mt-1">task selesai</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3"><span class="text-xs text-gray-500">In Progress</span><span class="text-2xl">⚡</span></div>
        <div class="text-2xl font-bold text-blue-600">{{ $taskInProgress }}</div>
        <div class="text-xs text-gray-400 mt-1">sedang dikerjakan</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3"><span class="text-xs text-gray-500">Overdue</span><span class="text-2xl">⚠️</span></div>
        <div class="text-2xl font-bold {{ $overdueTask > 0 ? 'text-red-600':'text-gray-300' }}">{{ $overdueTask }}</div>
        <div class="text-xs {{ $overdueTask > 0 ? 'text-red-400':'text-gray-400' }} mt-1">melewati deadline</div>
    </div>
</div>

<div class="grid grid-cols-3 gap-4 mb-4">
    {{-- My Tasks --}}
    <div class="col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">📋 Task Saya</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($myTasks as $task)
            <a href="{{ route('task.show', [$task->project, $task]) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition">
                <span class="text-base">{{ $task->tipe_badge['icon'] }}</span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate {{ $task->is_overdue ? 'text-red-700':'' }}">{{ $task->judul }}</div>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                        <span class="font-mono text-violet-600">{{ $task->nomor }}</span>
                        <span>{{ $task->project->nama }}</span>
                        @if($task->due_date)<span class="{{ $task->is_overdue ? 'text-red-500 font-medium':'' }}">📅 {{ $task->due_date->format('d M') }}</span>@endif
                    </div>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs {{ $task->prioritas_badge['class'] }} px-1.5 py-0.5 rounded">{{ $task->prioritas_badge['icon'] }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $task->board->nama }}</span>
                </div>
            </a>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada task yang ditugaskan 🎉</div>
            @endforelse
        </div>
    </div>

    {{-- Projects --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">🚀 Project Aktif</h2>
            <a href="{{ route('project.create') }}" class="text-xs text-violet-600 hover:underline">+ Baru</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($projects as $proj)
            <a href="{{ route('project.board', $proj) }}" class="block px-5 py-3 hover:bg-gray-50 transition">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background:{{ $proj->warna }}"></span>
                    <span class="text-sm font-medium text-gray-900 truncate">{{ $proj->nama }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                    <span>{{ $proj->tasks_count }} tasks</span>
                    <span>{{ $proj->progress }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-violet-500 h-1.5 rounded-full transition-all" style="width:{{ $proj->progress }}%"></div>
                </div>
            </a>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">Belum ada project</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Aktivitas Terbaru --}}
@if($aktivitasTerbaru->count() > 0)
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-3 border-b border-gray-100"><h2 class="text-sm font-semibold text-gray-900">🕐 Aktivitas Terbaru</h2></div>
    <div class="divide-y divide-gray-50">
        @foreach($aktivitasTerbaru as $a)
        <div class="px-5 py-2.5 flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center text-xs font-bold text-violet-700 flex-shrink-0">{{ substr($a->user?->name ?? 'S', 0, 1) }}</div>
            <div class="flex-1 min-w-0 text-xs">
                <span class="font-medium text-gray-900">{{ $a->user?->name ?? 'Sistem' }}</span>
                <span class="text-gray-500"> {{ $a->aksi_label }} </span>
                @if($a->task)<a href="{{ route('task.show', [$a->task->project, $a->task]) }}" class="text-violet-600 hover:underline font-mono">{{ $a->task->nomor }}</a>@endif
            </div>
            <span class="text-xs text-gray-300 flex-shrink-0">{{ $a->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
