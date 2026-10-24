@extends('layouts.app')
@section('title', $workspace->nama)
@section('subtitle', $workspace->deskripsi ?? $workspace->members->count() . ' anggota · ' . $projects->count() . ' project')
@section('header-action')
<div class="flex gap-2">
    <a href="{{ route('project.create', ['workspace_id'=>$workspace->id]) }}" class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">+ Project</a>
    @if($workspace->owner_id === auth()->id())
    <a href="{{ route('workspace.edit', $workspace) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">⚙️ Setting</a>
    @endif
</div>
@endsection
@section('content')

{{-- Projects --}}
<div class="mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-3">🚀 Projects ({{ $projects->count() }})</h2>
    <div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($projects as $proj)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="h-1.5" style="background:{{ $proj->warna }}"></div>
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-gray-400">{{ $proj->kode }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded-full {{ $proj->status_badge['class'] }}">{{ $proj->status_badge['label'] }}</span>
                    </div>
                    <span class="text-xs text-gray-400 capitalize">{{ $proj->tipe }}</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ $proj->nama }}</h3>
                @if($proj->deskripsi)<p class="text-xs text-gray-400 line-clamp-1 mb-2">{{ $proj->deskripsi }}</p>@endif

                {{-- Progress --}}
                <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                    <span>Progress</span><span>{{ $proj->progress }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mb-3">
                    <div class="h-1.5 rounded-full" style="background:{{ $proj->warna }};width:{{ $proj->progress }}%"></div>
                </div>

                {{-- Members avatar --}}
                <div class="flex items-center justify-between">
                    <div class="flex -space-x-1.5">
                        @foreach($proj->members->take(4) as $m)
                        <div class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-white" style="background:{{ ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6'][$loop->index % 5] }}">
                            {{ substr($m->user->name,0,1) }}
                        </div>
                        @endforeach
                        @if($proj->members->count() > 4)<span class="text-xs text-gray-400 ml-1">+{{ $proj->members->count()-4 }}</span>@endif
                    </div>
                    <a href="{{ route('project.board', $proj) }}" class="text-xs bg-violet-50 hover:bg-violet-100 text-violet-700 px-3 py-1.5 rounded-lg transition font-medium">Buka Board</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-xl border border-gray-200 p-10 text-center text-sm text-gray-400">
            Belum ada project — <a href="{{ route('project.create', ['workspace_id'=>$workspace->id]) }}" class="text-violet-600 hover:underline">Buat sekarang</a>
        </div>
        @endforelse
    </div>
</div>

{{-- Members --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900">👥 Anggota ({{ $members->count() }})</h2>
    </div>

    @if(in_array($role, ['owner','admin']))
    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
        <form action="{{ route('workspace.invite', $workspace) }}" method="POST" class="flex gap-3">
            @csrf
            <input type="email" name="email" placeholder="Email user..." required class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
            <select name="role" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                <option value="member">Member</option>
                <option value="admin">Admin</option>
                <option value="viewer">Viewer</option>
            </select>
            <button type="submit" class="bg-violet-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-violet-700 transition">Undang</button>
        </form>
    </div>
    @endif

    <div class="divide-y divide-gray-50">
        @foreach($members as $m)
        <div class="px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-bold text-sm">{{ substr($m->user->name,0,1) }}</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $m->user->name }}</div>
                    <div class="text-xs text-gray-400">{{ $m->user->email }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full {{ $m->role_badge['class'] }}">{{ $m->role_badge['label'] }}</span>
                @if($workspace->owner_id === auth()->id() && $m->user_id !== auth()->id())
                <form action="{{ route('workspace.remove-member', [$workspace, $m->user]) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700 transition" onclick="return confirm('Hapus member ini?')">✕</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
