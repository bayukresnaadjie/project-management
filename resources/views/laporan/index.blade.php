@extends('layouts.app')
@section('title', 'Laporan — ' . $project->nama)
@section('subtitle', $project->kode . ' · Progress: ' . $stats['progress'] . '%')
@section('header-action')
<a href="{{ route('project.board', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">← Board</a>
@endsection
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-xs text-gray-400 mb-1">Total Task</div>
        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-xs text-gray-400 mb-1">Selesai</div>
        <div class="text-2xl font-bold text-green-600">{{ $stats['selesai'] }}</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-xs text-gray-400 mb-1">Overdue</div>
        <div class="text-2xl font-bold {{ $stats['overdue'] > 0 ? 'text-red-600':'text-gray-300' }}">{{ $stats['overdue'] }}</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-xs text-gray-400 mb-1">Tanpa Assignee</div>
        <div class="text-2xl font-bold {{ $stats['tanpa_assignee'] > 0 ? 'text-amber-600':'text-gray-300' }}">{{ $stats['tanpa_assignee'] }}</div>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    {{-- Per Board --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">📋 Task per Kolom</h3>
        @php $maxBoard = $perBoard->max('total') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($perBoard as $b)
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $b['warna'] }}"></span>
                        {{ $b['nama'] }}
                    </span>
                    <span class="font-bold text-gray-700">{{ $b['total'] }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full" style="background:{{ $b['warna'] }};width:{{ $maxBoard > 0 ? ($b['total']/$maxBoard)*100 : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Per Prioritas --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">🎯 Task per Prioritas</h3>
        @php
        $prColors = ['critical'=>'#EF4444','high'=>'#F97316','medium'=>'#F59E0B','low'=>'#22C55E'];
        $prIcons  = ['critical'=>'🔴','high'=>'🟠','medium'=>'🟡','low'=>'🟢'];
        $maxPr    = $perPrioritas->max('total') ?: 1;
        @endphp
        <div class="space-y-3">
            @foreach($perPrioritas as $p)
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span>{{ $prIcons[$p->prioritas] ?? '⚪' }} {{ ucfirst($p->prioritas) }}</span>
                    <span class="font-bold">{{ $p->total }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full" style="background:{{ $prColors[$p->prioritas] ?? '#6B7280' }};width:{{ ($p->total/$maxPr)*100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    {{-- Per Member --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">👥 Task per Anggota</h3>
        <div class="space-y-3">
            @foreach($perMember as $m)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 text-xs font-bold flex-shrink-0">{{ substr($m['nama'],0,1) }}</div>
                <div class="flex-1">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-gray-900">{{ $m['nama'] }}</span>
                        <span class="text-gray-500">{{ $m['selesai'] }}/{{ $m['total'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-violet-500" style="width:{{ $m['total'] > 0 ? ($m['selesai']/$m['total'])*100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Aktivitas 14 Hari --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">📈 Aktivitas 14 Hari</h3>
        @php $maxAktivitas = $aktivitas14Hari->max('total') ?: 1; @endphp
        <div class="flex items-end gap-1 h-24">
            @foreach($aktivitas14Hari as $a)
            <div class="flex-1 flex flex-col items-center gap-0.5">
                <div class="w-full bg-violet-400 rounded-t" style="height:{{ max(2,($a['total']/$maxAktivitas)*88) }}px" title="{{ $a['tanggal'] }}: {{ $a['total'] }} aktivitas"></div>
                <span class="text-gray-400" style="font-size:7px">{{ substr($a['tanggal'],0,2) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
