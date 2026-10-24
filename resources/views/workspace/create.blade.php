@extends('layouts.app')
@section('title', isset($workspace) ? 'Edit Workspace' : 'Buat Workspace')
@section('content')
<div class="max-w-lg">
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
<form action="{{ isset($workspace) ? route('workspace.update', $workspace) : route('workspace.store') }}" method="POST" class="space-y-4">
    @csrf @if(isset($workspace)) @method('PUT') @endif

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Workspace <span class="text-red-500">*</span></label>
        <input type="text" name="nama" value="{{ old('nama', $workspace->nama ?? '') }}" required
               class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none">{{ old('deskripsi', $workspace->deskripsi ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Warna</label>
        <div class="flex gap-2 flex-wrap">
            @foreach(['#3B82F6','#8B5CF6','#10B981','#F59E0B','#EF4444','#EC4899','#06B6D4','#84CC16','#F97316','#6B7280'] as $c)
            <label class="cursor-pointer">
                <input type="radio" name="warna" value="{{ $c }}" {{ old('warna', $workspace->warna ?? '#8B5CF6') === $c ? 'checked':'' }} class="sr-only"/>
                <div class="w-7 h-7 rounded-lg border-2 border-transparent has-[:checked]:border-gray-900 transition" style="background:{{ $c }}"></div>
            </label>
            @endforeach
        </div>
    </div>

    <div class="flex gap-3 pt-2 border-t border-gray-100">
        <a href="{{ route('workspace.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-sm font-medium py-2.5 rounded-lg hover:bg-gray-200 transition">Batal</a>
        <button type="submit" class="flex-1 bg-violet-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-violet-700 transition">
            {{ isset($workspace) ? 'Simpan' : 'Buat Workspace' }}
        </button>
    </div>
</form>
</div>
</div>
@endsection
