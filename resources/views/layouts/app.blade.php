<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>ProjectHub — @yield('title','Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-60 bg-gray-900 flex flex-col fixed inset-y-0 left-0 z-30">
        <div class="flex items-center gap-2.5 px-4 py-4 border-b border-gray-800">
            <div class="w-8 h-8 bg-violet-600 rounded-lg flex items-center justify-center text-white font-bold text-sm flex-shrink-0">P</div>
            <div>
                <div class="font-bold text-white text-sm">ProjectHub</div>
                <div class="text-xs text-gray-500">Project Management</div>
            </div>
        </div>

        <nav class="flex-1 px-2 py-3 overflow-y-auto space-y-0.5">
            @php
            $user = auth()->user();
            $menu = [
                ['route'=>'dashboard',         'label'=>'Dashboard',    'icon'=>'📊'],
                ['route'=>'workspace.index',   'label'=>'Workspace',    'icon'=>'🏢'],
                ['route'=>'notification.index','label'=>'Notifikasi',   'icon'=>'🔔'],
            ];
            @endphp

            @foreach($menu as $item)
            @php $active = request()->routeIs(explode('.', $item['route'])[0] . '.*') || request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition {{ $active ? 'bg-violet-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <span>{{ $item['icon'] }}</span>
                {{ $item['label'] }}
                @if($item['route']==='notification.index')
                @php $nc = \App\Models\Notification::where('user_id',auth()->id())->where('dibaca',false)->count(); @endphp
                @if($nc > 0)<span class="ml-auto bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $nc }}</span>@endif
                @endif
            </a>
            @endforeach

            {{-- Workspace & Projects --}}
            @php
            $workspaces = \App\Models\Workspace::where('owner_id', $user->id)
                ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
                ->with(['projects' => fn($q) => $q->where('status','aktif')->orderBy('nama')])
                ->get();
            @endphp

            @foreach($workspaces as $ws)
            <div class="pt-3">
                <div class="px-3 pb-1 flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">{{ $ws->nama }}</span>
                    <a href="{{ route('project.create', ['workspace_id'=>$ws->id]) }}" class="text-gray-600 hover:text-white text-xs">+</a>
                </div>
                @foreach($ws->projects as $proj)
                @php $projActive = request()->is('project/'.$proj->id.'*'); @endphp
                <a href="{{ route('project.board', $proj) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition {{ $projActive ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-800 hover:text-gray-300' }}">
                    <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background:{{ $proj->warna }}"></span>
                    <span class="truncate">{{ $proj->nama }}</span>
                </a>
                @endforeach
            </div>
            @endforeach
        </nav>

        {{-- User --}}
        <div class="px-3 py-3 border-t border-gray-800">
            <div class="flex items-center gap-2 px-2 mb-2">
                <div class="w-7 h-7 rounded-full bg-violet-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-white truncate">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ $user->jabatan ?? 'Member' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-500 hover:text-red-400 hover:bg-gray-800 rounded-lg transition">
                    🚪 Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 ml-60">
        <header class="bg-white border-b border-gray-200 px-8 py-3.5 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-gray-900">@yield('title')</h1>
                    @hasSection('subtitle')<p class="text-xs text-gray-400">@yield('subtitle')</p>@endif
                </div>
                <div class="flex items-center gap-3">@yield('header-action')</div>
            </div>
        </header>

        <div class="px-8 pt-4">
            @foreach(['success'=>'green','error'=>'red','info'=>'blue'] as $type => $color)
            @if(session($type))
            <div class="bg-{{ $color }}-50 border border-{{ $color }}-200 text-{{ $color }}-700 px-4 py-3 rounded-lg text-sm mb-4">{{ session($type) }}</div>
            @endif
            @endforeach
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
        </div>

        <div class="px-8 py-5 pb-10">@yield('content')</div>
    </main>
</div>
</body>
</html>
