@extends('layouts.app')
@section('title', 'Burndown — ' . $sprint->nama)
@section('subtitle', $project->nama . ' · ' . $sprint->tanggal_mulai?->format('d M') . ' → ' . $sprint->tanggal_selesai?->format('d M Y'))
@section('header-action')
<a href="{{ route('sprint.index', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">← Sprint</a>
@endsection
@section('content')

<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-xs text-gray-400 mb-1">Total Story Points</div>
        <div class="text-2xl font-bold text-gray-900">{{ $totalPoint }}</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-xs text-gray-400 mb-1">Selesai</div>
        <div class="text-2xl font-bold text-green-600">{{ $sprint->done_point }}</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-xs text-gray-400 mb-1">Progress</div>
        <div class="text-2xl font-bold text-violet-600">{{ $sprint->progress }}%</div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-sm font-semibold text-gray-900 mb-5">📈 Burndown Chart</h2>

    @php
    $maxVal = max($totalPoint, 1);
    $height = 200;
    $width  = max(count($labels) * 60, 400);
    @endphp

    <div class="overflow-x-auto">
    <svg viewBox="0 0 {{ $width + 60 }} {{ $height + 60 }}" style="width:100%;min-width:400px" xmlns="http://www.w3.org/2000/svg">
        <!-- Grid -->
        @for($i = 0; $i <= 4; $i++)
        @php $y = 10 + ($height / 4) * $i; $val = round($maxVal - ($maxVal / 4) * $i); @endphp
        <line x1="50" y1="{{ $y }}" x2="{{ $width + 50 }}" y2="{{ $y }}" stroke="#F3F4F6" stroke-width="1"/>
        <text x="45" y="{{ $y + 4 }}" text-anchor="end" font-size="9" fill="#9CA3AF">{{ $val }}</text>
        @endfor

        <!-- Ideal Line -->
        @php
        $idealPoints = $idealLine->map(fn($v,$i) => [
            'x' => 50 + ($i / max(count($labels)-1,1)) * $width,
            'y' => 10 + (1 - $v/$maxVal) * $height
        ]);
        @endphp
        <polyline points="{{ $idealPoints->map(fn($p) => $p['x'].','.$p['y'])->join(' ') }}"
                  fill="none" stroke="#D1D5DB" stroke-width="1.5" stroke-dasharray="4,3"/>

        <!-- Actual Line -->
        @php
        $actualFiltered = $aktualLine->filter(fn($v) => $v !== null);
        $actualPoints = $actualFiltered->map(fn($v,$i) => [
            'x' => 50 + ($i / max(count($labels)-1,1)) * $width,
            'y' => 10 + (1 - $v/$maxVal) * $height
        ]);
        @endphp
        @if($actualPoints->count() > 1)
        <polyline points="{{ $actualPoints->map(fn($p) => $p['x'].','.$p['y'])->join(' ') }}"
                  fill="none" stroke="#8B5CF6" stroke-width="2"/>
        @foreach($actualPoints as $p)
        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3" fill="#8B5CF6"/>
        @endforeach
        @endif

        <!-- X Labels -->
        @foreach($labels as $i => $label)
        @php $x = 50 + ($i / max(count($labels)-1,1)) * $width; @endphp
        <text x="{{ $x }}" y="{{ $height + 30 }}" text-anchor="middle" font-size="8" fill="#9CA3AF">{{ $label }}</text>
        @endforeach
    </svg>
    </div>

    <div class="flex gap-6 mt-3 text-xs">
        <span class="flex items-center gap-2"><svg width="20" height="4"><line x1="0" y1="2" x2="20" y2="2" stroke="#D1D5DB" stroke-width="1.5" stroke-dasharray="4,3"/></svg> Ideal</span>
        <span class="flex items-center gap-2"><svg width="20" height="4"><line x1="0" y1="2" x2="20" y2="2" stroke="#8B5CF6" stroke-width="2"/></svg> Aktual</span>
    </div>
</div>

{{-- Task List --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm mt-4">
    <div class="px-5 py-3 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-900">Task Sprint</h3></div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400">Task</th>
            <th class="text-center px-5 py-2.5 text-xs font-semibold text-gray-400">Pts</th>
            <th class="text-center px-5 py-2.5 text-xs font-semibold text-gray-400">Prioritas</th>
            <th class="text-center px-5 py-2.5 text-xs font-semibold text-gray-400">Status</th>
            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400">Assignee</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($sprint->tasks->whereNull('parent_id') as $task)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5">
                    <a href="{{ route('task.show', [$project, $task]) }}" class="font-medium text-gray-900 hover:text-violet-600">{{ $task->judul }}</a>
                    <div class="text-xs font-mono text-violet-600">{{ $task->nomor }}</div>
                </td>
                <td class="px-5 py-2.5 text-center text-xs font-bold text-gray-700">{{ $task->story_point ?: '—' }}</td>
                <td class="px-5 py-2.5 text-center"><span class="text-xs {{ $task->prioritas_badge['class'] }} px-1.5 py-0.5 rounded">{{ $task->prioritas_badge['icon'] }} {{ $task->prioritas_badge['label'] }}</span></td>
                <td class="px-5 py-2.5 text-center"><span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $task->board->nama }}</span></td>
                <td class="px-5 py-2.5 text-xs text-gray-500">{{ $task->assignee?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
