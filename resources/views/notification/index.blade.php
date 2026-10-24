@extends('layouts.app')
@section('title','Notifikasi')
@section('header-action')
<form action="{{ route('notification.hapus-semua') }}" method="POST">
    @csrf @method('DELETE')
    <button class="text-sm text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-lg transition">🗑️ Hapus Semua</button>
</form>
@endsection
@section('content')
<div class="max-w-2xl">
    @if($notifikasi->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-50">
        @foreach($notifikasi as $n)
        <div class="px-5 py-4 flex items-start gap-3 {{ !$n->dibaca ? 'bg-violet-50/30':'' }}">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0
                {{ $n->tipe==='assigned'?'bg-violet-500':($n->tipe==='commented'?'bg-blue-500':($n->tipe==='completed'?'bg-green-500':'bg-gray-500')) }}">
                {{ $n->tipe==='assigned'?'👤':($n->tipe==='commented'?'💬':($n->tipe==='completed'?'✓':'ℹ')) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-0.5">
                    <span class="text-sm font-semibold text-gray-900">{{ $n->judul }}</span>
                    @if(!$n->dibaca)<span class="w-2 h-2 bg-violet-500 rounded-full flex-shrink-0"></span>@endif
                </div>
                <p class="text-sm text-gray-600">{{ $n->pesan }}</p>
                <div class="flex items-center gap-2 mt-1.5 text-xs text-gray-400">
                    @if($n->dariUser)<span>Dari {{ $n->dariUser->name }}</span><span>·</span>@endif
                    <span>{{ $n->created_at->diffForHumans() }}</span>
                </div>
                @if($n->url)
                <a href="{{ $n->url }}" class="inline-block mt-2 text-xs text-violet-600 hover:underline">Lihat detail →</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @if($notifikasi->hasPages())
    <div class="mt-4">{{ $notifikasi->links() }}</div>
    @endif
    @else
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="text-5xl mb-3">🔔</div>
        <div class="text-gray-500 text-sm">Tidak ada notifikasi</div>
    </div>
    @endif
</div>
@endsection
