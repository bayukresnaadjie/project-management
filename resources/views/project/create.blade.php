@extends('layouts.app')
@section('title','Buat Project Baru')
@section('content')
<div class="max-w-xl">
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
<form action="{{ route('project.store') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Workspace <span class="text-red-500">*</span></label>
        <select name="workspace_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            <option value="">-- Pilih Workspace --</option>
            @foreach($workspaces as $ws)
            <option value="{{ $ws->id }}" {{ (old('workspace_id', $workspaceId) == $ws->id) ? 'selected':'' }}>{{ $ws->nama }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Project <span class="text-red-500">*</span></label>
        <input type="text" name="nama" value="{{ old('nama') }}" required
               class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none">{{ old('deskripsi') }}</textarea>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Tipe Project <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-3 gap-3">
            @foreach(['kanban'=>['label'=>'Kanban','desc'=>'Board visual drag & drop','icon'=>'📋'],'scrum'=>['label'=>'Scrum','desc'=>'Sprint & backlog','icon'=>'🏃'],'basic'=>['label'=>'Basic','desc'=>'List sederhana','icon'=>'📝']] as $val => $opt)
            <label class="flex flex-col items-center border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-violet-300 has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 transition text-center">
                <input type="radio" name="tipe" value="{{ $val }}" {{ old('tipe','kanban') === $val ? 'checked':'' }} class="sr-only"/>
                <span class="text-2xl mb-1">{{ $opt['icon'] }}</span>
                <div class="text-xs font-semibold text-gray-900">{{ $opt['label'] }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $opt['desc'] }}</div>
            </label>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', today()->format('Y-m-d')) }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Target Selesai</label>
            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
        </div>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Warna</label>
        <div class="flex gap-2 flex-wrap">
            @foreach(['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899','#06B6D4','#F97316','#84CC16','#6B7280'] as $c)
            <label class="cursor-pointer">
                <input type="radio" name="warna" value="{{ $c }}" {{ old('warna','#8B5CF6') === $c ? 'checked':'' }} class="sr-only"/>
                <div class="w-7 h-7 rounded-lg border-2 border-transparent has-[:checked]:border-gray-900 transition" style="background:{{ $c }}"></div>
            </label>
            @endforeach
        </div>
    </div>

    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="publik" value="1" {{ old('publik') ? 'checked':'' }} class="w-4 h-4 text-violet-600 rounded"/>
        <span class="text-sm text-gray-700">Project publik (semua member workspace bisa akses)</span>
    </label>

    <div class="flex gap-3 pt-2 border-t border-gray-100">
        <a href="{{ route('workspace.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-sm font-medium py-2.5 rounded-lg hover:bg-gray-200 transition">Batal</a>
        <button type="submit" class="flex-1 bg-violet-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-violet-700 transition">🚀 Buat Project</button>
    </div>
</form>
</div>
</div>
@endsection
