@extends('layouts.app')
@section('title', $task->nomor . ' — ' . $task->judul)
@section('subtitle', $project->nama . ' · ' . $task->board->nama)
@section('header-action')
<div class="flex gap-2">
    <a href="{{ route('project.board', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">← Board</a>
    @if(in_array($project->getRoleUser(auth()->user()),['manager']))
    <form action="{{ route('task.destroy', [$project,$task]) }}" method="POST" onsubmit="return confirm('Hapus task ini?')">
        @csrf @method('DELETE')
        <button class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium px-4 py-2 rounded-lg transition">🗑️ Hapus</button>
    </form>
    @endif
</div>
@endsection
@section('content')
<div class="grid grid-cols-3 gap-5">

    {{-- Main Content --}}
    <div class="col-span-2 space-y-4">

        {{-- Task Header --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-start gap-3 mb-3">
                <span class="text-2xl mt-0.5">{{ $task->tipe_badge['icon'] }}</span>
                <div class="flex-1">
                    <h1 class="font-bold text-gray-900 text-xl leading-snug mb-2">{{ $task->judul }}</h1>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs text-violet-600 font-semibold">{{ $task->nomor }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $task->tipe_badge['class'] }}">{{ $task->tipe_badge['label'] }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $task->prioritas_badge['class'] }}">{{ $task->prioritas_badge['icon'] }} {{ $task->prioritas_badge['label'] }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $task->board->nama }}</span>
                        @if($task->is_overdue)<span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">⚠️ Overdue</span>@endif
                    </div>
                </div>
            </div>

            {{-- Labels --}}
            @if($task->labels->count() > 0)
            <div class="flex flex-wrap gap-1.5 mb-3">
                @foreach($task->labels as $label)
                <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:{{ $label->warna }}20;color:{{ $label->warna }};border:1px solid {{ $label->warna }}40">{{ $label->nama }}</span>
                @endforeach
            </div>
            @endif

            {{-- Deskripsi --}}
            <div class="prose prose-sm max-w-none text-gray-600 text-sm">
                @if($task->deskripsi)
                    {!! nl2br(e($task->deskripsi)) !!}
                @else
                    <span class="text-gray-300 italic text-xs">Belum ada deskripsi</span>
                @endif
            </div>

            {{-- Edit form (inline) --}}
            <div class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('task.update', [$project,$task]) }}" method="POST" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pindahkan ke</label>
                        <div class="flex gap-2">
                            <select name="board_id" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                                @foreach($boards as $b)
                                <option value="{{ $b->id }}" {{ $task->board_id==$b->id?'selected':'' }}>{{ $b->nama }}</option>
                                @endforeach
                            </select>
                            <select name="prioritas" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                                @foreach(['critical'=>'🔴 Critical','high'=>'🟠 High','medium'=>'🟡 Medium','low'=>'🟢 Low'] as $v=>$l)
                                <option value="{{ $v }}" {{ $task->prioritas===$v?'selected':'' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="progres" value="{{ $task->progres }}" min="0" max="100" class="w-20 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none" placeholder="%"/>
                            <button type="submit" class="bg-violet-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-violet-700 transition">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sub Tasks --}}
        @if($task->subTasks->count() > 0 || !$task->parent_id)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">☰ Sub Task ({{ $task->subTasks->count() }})</h3>
            <div class="space-y-2">
                @foreach($task->subTasks as $sub)
                <a href="{{ route('task.show', [$project, $sub]) }}" class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition">
                    <span class="{{ $sub->board->is_done ? 'text-green-500':'text-gray-300' }} text-lg">{{ $sub->board->is_done ? '✓':'○' }}</span>
                    <div class="flex-1">
                        <div class="text-sm {{ $sub->board->is_done ? 'line-through text-gray-400':'text-gray-900' }}">{{ $sub->judul }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $sub->nomor }}</div>
                    </div>
                    <span class="text-xs {{ $sub->prioritas_badge['class'] }} px-1.5 py-0.5 rounded">{{ $sub->prioritas_badge['icon'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Komentar --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">💬 Komentar ({{ $task->comments->count() }})</h3>

            <form action="{{ route('task.comment.store', [$project,$task]) }}" method="POST" class="mb-5">
                @csrf
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-violet-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ substr(auth()->user()->name,0,1) }}</div>
                    <div class="flex-1">
                        <textarea name="isi" rows="2" placeholder="Tulis komentar..." required
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none mb-2"></textarea>
                        <button type="submit" class="bg-violet-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-violet-700 transition">Kirim</button>
                    </div>
                </div>
            </form>

            <div class="space-y-4">
                @forelse($task->comments as $c)
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold flex-shrink-0">{{ substr($c->user->name,0,1) }}</div>
                    <div class="flex-1">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-semibold text-gray-900">{{ $c->user->name }}</span>
                                <span class="text-xs text-gray-400">{{ $c->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700">{{ $c->isi }}</p>
                        </div>
                        @foreach($c->replies as $r)
                        <div class="flex gap-2 mt-2 ml-4">
                            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-500">{{ substr($r->user->name,0,1) }}</div>
                            <div class="bg-gray-50 rounded-lg px-3 py-2 flex-1">
                                <span class="text-xs font-semibold text-gray-900">{{ $r->user->name }}</span>
                                <p class="text-xs text-gray-700 mt-0.5">{{ $r->isi }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada komentar</p>
                @endforelse
            </div>
        </div>

        {{-- Attachment --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">📎 Lampiran ({{ $task->attachments->count() }})</h3>
            <form action="{{ route('task.attachment.store', [$project,$task]) }}" method="POST" enctype="multipart/form-data" class="flex gap-3 mb-4">
                @csrf
                <input type="file" name="file" required class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm"/>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">Upload</button>
            </form>
            @if($task->attachments->count() > 0)
            <div class="space-y-2">
                @foreach($task->attachments as $att)
                <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg">
                    <span class="text-xl">{{ $att->icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 truncate">{{ $att->nama_file }}</div>
                        <div class="text-xs text-gray-400">{{ $att->ukuran_format }} · {{ $att->user->name }} · {{ $att->created_at->format('d M Y') }}</div>
                    </div>
                    <a href="{{ route('task.attachment.download', [$project,$task,$att]) }}" class="text-xs text-blue-600 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg">⬇</a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Detail --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Detail</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Assignee</span>
                    <div class="flex -space-x-1">
                        @forelse($task->assignees as $a)
                        <div class="w-6 h-6 rounded-full bg-violet-600 flex items-center justify-center text-white text-xs font-bold border-2 border-white" title="{{ $a->name }}">{{ substr($a->name,0,1) }}</div>
                        @empty<span class="text-gray-300 text-xs">—</span>@endforelse
                    </div>
                </div>
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Sprint</span><span class="text-xs text-gray-600">{{ $task->sprint?->nama ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Due Date</span>
                    <span class="text-xs {{ $task->is_overdue ? 'text-red-500 font-medium':'text-gray-600' }}">{{ $task->due_date?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Story Point</span><span class="text-xs font-bold text-gray-900">{{ $task->story_point ?: '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Estimasi</span><span class="text-xs text-gray-600">{{ $task->estimasi_jam ? $task->estimasi_jam . ' jam' : '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Aktual</span><span class="text-xs text-gray-600">{{ $task->aktual_jam ? $task->aktual_jam . ' jam' : '—' }}</span></div>
                <div>
                    <div class="flex justify-between text-xs mb-1"><span class="text-gray-400">Progress</span><span class="font-bold text-violet-600">{{ $task->progres }}%</span></div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-violet-500 h-2 rounded-full" style="width:{{ $task->progres }}%"></div>
                    </div>
                </div>
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Dibuat oleh</span><span class="text-xs text-gray-600">{{ $task->dibuatOleh->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400 text-xs">Dibuat</span><span class="text-xs text-gray-600">{{ $task->created_at->format('d M Y') }}</span></div>
                @if($task->selesai_at)<div class="flex justify-between"><span class="text-gray-400 text-xs">Selesai</span><span class="text-xs text-green-600 font-medium">{{ $task->selesai_at->format('d M Y') }}</span></div>@endif
            </div>
        </div>

        {{-- Aktivitas --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Aktivitas</h3>
            <div class="space-y-2.5 max-h-64 overflow-y-auto">
                @forelse($task->activities as $act)
                <div class="flex gap-2">
                    <div class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs flex-shrink-0">{{ substr($act->user?->name ?? 'S', 0, 1) }}</div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-600"><span class="font-medium text-gray-900">{{ $act->user?->name ?? 'Sistem' }}</span> {{ $act->aksi_label }}
                            @if($act->nilai_baru)<span class="font-medium text-gray-900"> {{ $act->nilai_baru }}</span>@endif
                        </p>
                        <span class="text-xs text-gray-400">{{ $act->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-2">Belum ada aktivitas</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
