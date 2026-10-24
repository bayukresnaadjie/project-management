@extends('layouts.app')
@section('title','Sprint — ' . $project->nama)
@section('subtitle', $project->kode . ' · ' . $sprints->count() . ' sprint')
@section('header-action')
<div class="flex gap-2">
    <a href="{{ route('project.board', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">← Board</a>
    @if(in_array($role, ['manager']))
    <button onclick="document.getElementById('modal-sprint').classList.remove('hidden')" class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">+ Sprint</button>
    @endif
</div>
@endsection
@section('content')

{{-- Sprint List --}}
<div class="space-y-4 mb-6">
    @forelse($sprints as $sprint)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $sprint->status_badge['class'] }}">{{ $sprint->status_badge['label'] }}</span>
                <h3 class="font-semibold text-gray-900">{{ $sprint->nama }}</h3>
                @if($sprint->tujuan)<span class="text-xs text-gray-400">— {{ $sprint->tujuan }}</span>@endif
            </div>
            <div class="flex items-center gap-2">
                @if($sprint->tanggal_mulai)
                <span class="text-xs text-gray-400">{{ $sprint->tanggal_mulai->format('d M') }} → {{ $sprint->tanggal_selesai?->format('d M Y') ?? '?' }}</span>
                @endif
                <div class="flex gap-1.5 text-xs font-bold">
                    <span class="bg-teal-50 text-teal-700 px-2 py-0.5 rounded">{{ $sprint->done_point }}/{{ $sprint->total_point }} pts</span>
                    <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $sprint->progress }}%</span>
                </div>
                @if(in_array($role, ['manager']))
                <div class="flex gap-1">
                    @if($sprint->status === 'perencanaan')
                    <form action="{{ route('sprint.mulai', [$project,$sprint]) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">▶ Mulai</button>
                    </form>
                    @elseif($sprint->status === 'aktif')
                    <a href="{{ route('sprint.burndown', [$project,$sprint]) }}" class="text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">📈 Burndown</a>
                    <form action="{{ route('sprint.selesai', [$project,$sprint]) }}" method="POST" onsubmit="return confirm('Selesaikan sprint ini?')">
                        @csrf @method('PATCH')
                        <button class="text-xs bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">⏹ Selesai</button>
                    </form>
                    @elseif($sprint->status === 'selesai')
                    <a href="{{ route('sprint.burndown', [$project,$sprint]) }}" class="text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">📈 Burndown</a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="h-1 bg-gray-100">
            <div class="h-1 bg-violet-500 transition-all" style="width:{{ $sprint->progress }}%"></div>
        </div>

        {{-- Tasks --}}
        <div class="divide-y divide-gray-50">
            @forelse($sprint->tasks->whereNull('parent_id') as $task)
            <a href="{{ route('task.show', [$project,$task]) }}" class="flex items-center gap-3 px-5 py-2.5 hover:bg-gray-50 transition">
                <span class="text-sm">{{ $task->tipe_badge['icon'] }}</span>
                <div class="flex-1 min-w-0">
                    <span class="text-sm font-medium {{ $task->board->is_done ? 'line-through text-gray-400':'text-gray-900' }}">{{ $task->judul }}</span>
                </div>
                <span class="font-mono text-xs text-violet-600 flex-shrink-0">{{ $task->nomor }}</span>
                <span class="text-xs {{ $task->prioritas_badge['class'] }} px-1.5 py-0.5 rounded flex-shrink-0">{{ $task->prioritas_badge['icon'] }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 flex-shrink-0">{{ $task->board->nama }}</span>
                @if($task->assignee)
                <div class="w-6 h-6 rounded-full bg-violet-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0" title="{{ $task->assignee->name }}">{{ substr($task->assignee->name,0,1) }}</div>
                @endif
                @if($task->story_point > 0)
                <span class="text-xs text-gray-400 flex-shrink-0">{{ $task->story_point }}pt</span>
                @endif
            </a>
            @empty
            <div class="px-5 py-4 text-xs text-gray-400 text-center">Belum ada task di sprint ini</div>
            @endforelse
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
        <div class="text-4xl mb-2">🏃</div>
        <div class="text-sm text-gray-400 mb-3">Belum ada sprint</div>
        @if(in_array($role,['manager']))
        <button onclick="document.getElementById('modal-sprint').classList.remove('hidden')" class="bg-violet-600 text-white text-sm px-4 py-2 rounded-lg">Buat Sprint Pertama</button>
        @endif
    </div>
    @endforelse
</div>

{{-- Backlog --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900">📋 Backlog ({{ $backlog->count() }})</h2>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($backlog as $task)
        <div class="flex items-center gap-3 px-5 py-2.5 hover:bg-gray-50">
            <span class="text-sm">{{ $task->tipe_badge['icon'] }}</span>
            <a href="{{ route('task.show', [$project,$task]) }}" class="flex-1 text-sm font-medium text-gray-900 hover:text-violet-600">{{ $task->judul }}</a>
            <span class="font-mono text-xs text-violet-600">{{ $task->nomor }}</span>
            <span class="text-xs {{ $task->prioritas_badge['class'] }} px-1.5 py-0.5 rounded">{{ $task->prioritas_badge['icon'] }}</span>
            @if($task->story_point > 0)<span class="text-xs text-gray-400">{{ $task->story_point }}pt</span>@endif
            {{-- Assign to sprint --}}
            @if($sprints->where('status','!=','selesai')->count() > 0 && in_array($role,['manager','member']))
            <form action="{{ route('sprint.add-task', [$project, $sprints->where('status','aktif')->first() ?? $sprints->first()]) }}" method="POST">
                @csrf
                <input type="hidden" name="task_id" value="{{ $task->id }}"/>
                <button class="text-xs text-violet-600 bg-violet-50 hover:bg-violet-100 px-2.5 py-1 rounded-lg transition">+ Sprint</button>
            </form>
            @endif
        </div>
        @empty
        <div class="px-5 py-6 text-xs text-gray-400 text-center">Backlog kosong 🎉</div>
        @endforelse
    </div>
</div>

{{-- Modal Buat Sprint --}}
<div id="modal-sprint" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Buat Sprint Baru</h2>
            <button onclick="document.getElementById('modal-sprint').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form action="{{ route('sprint.store', $project) }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Sprint <span class="text-red-500">*</span></label>
                <input type="text" name="nama" placeholder="Sprint 1 — Foundation" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tujuan Sprint</label>
                <textarea name="tujuan" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Mulai</label>
                    <input type="date" name="tanggal_mulai" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Selesai</label>
                    <input type="date" name="tanggal_selesai" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kapasitas (Story Points)</label>
                    <input type="number" name="kapasitas" value="40" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-sprint').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 text-sm font-medium py-2.5 rounded-lg">Batal</button>
                <button type="submit" class="flex-1 bg-violet-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-violet-700 transition">Buat Sprint</button>
            </div>
        </form>
    </div>
</div>
@endsection
