@extends('layouts.app')
@section('title','Workspace')
@section('header-action')
<a href="{{ route('workspace.create') }}" class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">+ Buat Workspace</a>
@endsection
@section('content')
<div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($workspaces as $ws)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition">
        <div class="h-2" style="background:{{ $ws->warna }}"></div>
        <div class="p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0" style="background:{{ $ws->warna }}">
                        {{ substr($ws->nama, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $ws->nama }}</h3>
                        <div class="text-xs text-gray-400">{{ $ws->owner->name }}</div>
                    </div>
                </div>
                @if($ws->owner_id === auth()->id())
                <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">Owner</span>
                @else
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ $ws->getRoleUser(auth()->user()) }}</span>
                @endif
            </div>
            @if($ws->deskripsi)<p class="text-xs text-gray-400 mb-3 line-clamp-2">{{ $ws->deskripsi }}</p>@endif
            <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
                <span>{{ $ws->projects_count }} project</span>
                <span>{{ $ws->members->count() }} anggota</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('workspace.show', $ws) }}" class="flex-1 text-center bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-medium py-2 rounded-lg transition">Buka</a>
                <a href="{{ route('project.create', ['workspace_id'=>$ws->id]) }}" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-lg transition">+ Project</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="text-5xl mb-3">🏢</div>
        <div class="text-gray-500 text-sm mb-3">Belum ada workspace</div>
        <a href="{{ route('workspace.create') }}" class="bg-violet-600 text-white text-sm px-4 py-2 rounded-lg inline-block">Buat Workspace Pertama</a>
    </div>
    @endforelse
</div>
@endsection
