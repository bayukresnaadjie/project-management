@extends('layouts.app')
@section('title', 'Settings — ' . $project->nama)
@section('subtitle', $project->kode)
@section('header-action')
<a href="{{ route('project.board', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">← Board</a>
@endsection
@section('content')
<div class="max-w-3xl space-y-5">

{{-- Info Project --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <h2 class="text-sm font-semibold text-gray-900 mb-4">⚙️ Informasi Project</h2>
    <form action="{{ route('project.update', $project) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Project</label>
                <input type="text" name="nama" value="{{ old('nama',$project->nama) }}" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    @foreach(['aktif','selesai','ditunda','dibatalkan'] as $s)
                    <option value="{{ $s }}" {{ $project->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Warna</label>
                <div class="flex gap-1.5 flex-wrap mt-1">
                    @foreach(['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899','#06B6D4','#F97316'] as $c)
                    <label class="cursor-pointer">
                        <input type="radio" name="warna" value="{{ $c }}" {{ $project->warna===$c?'checked':'' }} class="sr-only"/>
                        <div class="w-6 h-6 rounded border-2 border-transparent has-[:checked]:border-gray-900" style="background:{{ $c }}"></div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ $project->tanggal_mulai?->format('Y-m-d') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Target Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ $project->tanggal_selesai?->format('Y-m-d') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none">{{ $project->deskripsi }}</textarea>
            </div>
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="publik" value="1" {{ $project->publik?'checked':'' }} class="w-4 h-4 rounded text-violet-600"/>
            <span class="text-sm text-gray-700">Project publik</span>
        </label>
        <button type="submit" class="bg-violet-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-violet-700 transition">Simpan</button>
    </form>
</div>

{{-- Members --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="px-5 py-3 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">👥 Anggota Project</h2>
    </div>

    @if($workspaceMembers->count() > 0)
    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
        <form action="{{ route('project.add-member', $project) }}" method="POST" class="flex gap-3">
            @csrf
            <select name="user_id" required class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                <option value="">-- Pilih dari workspace --</option>
                @foreach($workspaceMembers as $wm)
                <option value="{{ $wm->id }}">{{ $wm->name }} ({{ $wm->jabatan ?? 'Member' }})</option>
                @endforeach
            </select>
            <select name="role" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                <option value="member">Member</option>
                <option value="viewer">Viewer</option>
                <option value="manager">Manager</option>
            </select>
            <button type="submit" class="bg-violet-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-violet-700 transition">Tambah</button>
        </form>
    </div>
    @endif

    <div class="divide-y divide-gray-50">
        @foreach($project->members as $m)
        <div class="px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-bold text-sm">{{ substr($m->user->name,0,1) }}</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $m->user->name }}</div>
                    <div class="text-xs text-gray-400">{{ $m->user->jabatan ?? $m->user->email }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full {{ $m->role_badge['class'] }}">{{ $m->role_badge['label'] }}</span>
                @if($m->user_id !== $project->dibuat_oleh)
                <form action="{{ route('project.remove-member', [$project, $m->user]) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700 transition" onclick="return confirm('Hapus member ini?')">✕</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
</div>
@endsection
