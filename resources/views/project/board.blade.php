@extends('layouts.app')
@section('title', $project->nama)
@section('subtitle', $project->kode . ' · ' . ucfirst($project->tipe) . ' · ' . $project->workspace->nama)
@section('header-action')
<div class="flex gap-2 items-center">
    {{-- Member avatars --}}
    <div class="flex -space-x-1.5 mr-1">
        @foreach($members->take(5) as $m)
        <div class="w-7 h-7 rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-white" style="background:{{ ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444'][$loop->index % 5] }}" title="{{ $m->user->name }}">
            {{ substr($m->user->name,0,1) }}
        </div>
        @endforeach
    </div>
    @if($project->tipe === 'scrum')
    <a href="{{ route('sprint.index', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition">🏃 Sprint</a>
    @endif
    <a href="{{ route('laporan.index', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition">📊 Laporan</a>
    <a href="{{ route('project.settings', $project) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition">⚙️</a>
    <button onclick="document.getElementById('modal-task').classList.remove('hidden')"
            class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">+ Task</button>
</div>
@endsection
@section('content')

{{-- Filter Bar --}}
<div class="flex items-center gap-3 mb-4 bg-white rounded-xl border border-gray-200 px-4 py-2.5 shadow-sm">
    <span class="text-xs text-gray-400 font-medium">Filter:</span>
    <div class="flex gap-2 flex-wrap flex-1">
        @foreach($members as $m)
        <button onclick="filterAssignee({{ $m->user->id }})" id="filter-user-{{ $m->user->id }}"
                class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border border-gray-200 hover:border-violet-400 transition">
            <span class="w-4 h-4 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:#8B5CF6;font-size:8px">{{ substr($m->user->name,0,1) }}</span>
            {{ explode(' ', $m->user->name)[0] }}
        </button>
        @endforeach
        @foreach(['critical'=>'🔴','high'=>'🟠','medium'=>'🟡','low'=>'🟢'] as $p => $icon)
        <button onclick="filterPrioritas('{{ $p }}')" id="filter-pr-{{ $p }}"
                class="text-xs px-2.5 py-1 rounded-full border border-gray-200 hover:border-violet-400 transition">{{ $icon }} {{ ucfirst($p) }}</button>
        @endforeach
        <button onclick="clearFilter()" class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition">✕ Reset</button>
    </div>
    <div class="text-xs text-gray-400">Progress: <span class="font-bold text-violet-600">{{ $project->progress }}%</span></div>
</div>

{{-- Kanban Board --}}
<div class="flex gap-4 overflow-x-auto pb-4" id="board" style="min-height:calc(100vh - 220px)">
    @foreach($boards as $board)
    <div class="flex-shrink-0 w-72 flex flex-col" data-board-id="{{ $board->id }}">
        {{-- Column Header --}}
        <div class="flex items-center justify-between px-3 py-2.5 rounded-xl mb-2" style="background:{{ $board->warna }}18;border:1px solid {{ $board->warna }}30">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $board->warna }}"></div>
                <span class="text-sm font-semibold text-gray-800">{{ $board->nama }}</span>
                <span class="text-xs bg-white px-1.5 py-0.5 rounded-full text-gray-500 font-medium" id="count-{{ $board->id }}">{{ $board->tasks->count() }}</span>
            </div>
            <button onclick="quickAdd({{ $board->id }}, '{{ addslashes($board->nama) }}')" class="text-gray-400 hover:text-gray-600 text-sm w-6 h-6 rounded flex items-center justify-center hover:bg-white transition">+</button>
        </div>

        {{-- Task Cards --}}
        <div class="flex-1 space-y-2 kanban-col" id="col-{{ $board->id }}" data-board-id="{{ $board->id }}"
             ondragover="event.preventDefault();this.classList.add('bg-violet-50')"
             ondragleave="this.classList.remove('bg-violet-50')"
             ondrop="drop(event, {{ $board->id }})" style="min-height:100px">

            @foreach($board->tasks as $task)
            <div class="task-card bg-white rounded-xl border border-gray-200 p-3 cursor-pointer hover:shadow-md hover:border-violet-200 transition group"
                 id="task-{{ $task->id }}" draggable="true"
                 data-task-id="{{ $task->id }}" data-assignee="{{ $task->assignees->pluck('id')->join(',') }}" data-prioritas="{{ $task->prioritas }}"
                 ondragstart="dragStart(event)" onclick="openTask({{ $task->id }})">

                {{-- Labels --}}
                @if($task->labels->count() > 0)
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($task->labels as $label)
                    <span class="text-xs px-1.5 py-0.5 rounded font-medium" style="background:{{ $label->warna }}20;color:{{ $label->warna }}">{{ $label->nama }}</span>
                    @endforeach
                </div>
                @endif

                {{-- Task Title --}}
                <p class="text-sm font-medium text-gray-900 mb-2 leading-snug">{{ $task->judul }}</p>

                {{-- Meta --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-mono text-gray-400">{{ $task->nomor }}</span>
                        <span class="text-xs">{{ $task->tipe_badge['icon'] }}</span>
                        <span class="text-xs">{{ $task->prioritas_badge['icon'] }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @if($task->subTasks->count() > 0)
                        <span class="text-xs text-gray-400">☰ {{ $task->subTasks->where('selesai_at','!=',null)->count() }}/{{ $task->subTasks->count() }}</span>
                        @endif
                        @if($task->due_date)
                        <span class="text-xs {{ $task->is_overdue ? 'text-red-500 font-medium':'text-gray-400' }}">📅 {{ $task->due_date->format('d/m') }}</span>
                        @endif
                        @if($task->assignees->count() > 0)
                        <div class="flex -space-x-1">
                            @foreach($task->assignees->take(2) as $a)
                            <div class="w-5 h-5 rounded-full border border-white flex items-center justify-center text-white text-xs font-bold" style="background:#8B5CF6;font-size:7px">{{ substr($a->name,0,1) }}</div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                @if($task->story_point > 0)
                <div class="mt-1.5 text-xs text-gray-300">{{ $task->story_point }} pts</div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Quick Add Input --}}
        <div id="quick-add-{{ $board->id }}" class="hidden mt-2">
            <div class="bg-white rounded-xl border border-violet-300 p-2.5 shadow-sm">
                <input type="text" id="quick-input-{{ $board->id }}" placeholder="Judul task..." class="w-full text-sm focus:outline-none"/>
                <div class="flex gap-1.5 mt-2">
                    <button onclick="submitQuickAdd({{ $board->id }}, {{ $project->id }})" class="flex-1 bg-violet-600 text-white text-xs font-medium py-1.5 rounded-lg hover:bg-violet-700 transition">Tambah</button>
                    <button onclick="cancelQuickAdd({{ $board->id }})" class="text-xs text-gray-400 px-2 hover:text-gray-600">✕</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Modal Buat Task --}}
<div id="modal-task" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Buat Task Baru</h2>
            <button onclick="document.getElementById('modal-task').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form action="{{ route('task.store', $project) }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="judul" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kolom</label>
                    <select name="board_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        @foreach($boards as $b)
                        <option value="{{ $b->id }}" {{ $b->is_default?'selected':'' }}>{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                    <select name="tipe" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        @foreach(['task'=>'✅ Task','bug'=>'🐛 Bug','feature'=>'✨ Feature','improvement'=>'⚡ Improvement'] as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prioritas</label>
                    <select name="prioritas" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="medium">🟡 Medium</option>
                        <option value="high">🟠 High</option>
                        <option value="critical">🔴 Critical</option>
                        <option value="low">🟢 Low</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Assignee</label>
                    <select name="assignee_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="">-- Tidak ada --</option>
                        @foreach($members as $m)
                        <option value="{{ $m->user->id }}">{{ $m->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Due Date</label>
                    <input type="date" name="due_date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Story Point</label>
                    <input type="number" name="story_point" value="0" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
                </div>
            </div>
            {{-- Labels --}}
            @if($labels->count() > 0)
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Labels</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($labels as $label)
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="labels[]" value="{{ $label->id }}" class="w-3.5 h-3.5 rounded"/>
                        <span class="text-xs px-2 py-0.5 rounded" style="background:{{ $label->warna }}20;color:{{ $label->warna }}">{{ $label->nama }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-task').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 text-sm font-medium py-2.5 rounded-lg">Batal</button>
                <button type="submit" class="flex-1 bg-violet-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-violet-700 transition">Buat Task</button>
            </div>
        </form>
    </div>
</div>

<script>
const projectId = {{ $project->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Drag & Drop
let draggedId = null;
function dragStart(e) { draggedId = e.target.dataset.taskId; e.target.classList.add('opacity-50'); }
document.addEventListener('dragend', e => { if(e.target.classList) e.target.classList.remove('opacity-50'); document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('bg-violet-50')); });

function drop(e, boardId) {
    e.preventDefault();
    const col = document.getElementById('col-'+boardId);
    col.classList.remove('bg-violet-50');
    if (!draggedId) return;
    const card = document.getElementById('task-'+draggedId);
    if (card) col.appendChild(card);
    fetch(`/project/${projectId}/task/${draggedId}/move`, {
        method:'PATCH', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({board_id: boardId})
    }).then(r => r.json()).then(d => { if(!d.success) location.reload(); });
    updateCount();
}

function updateCount() {
    document.querySelectorAll('.kanban-col').forEach(col => {
        const boardId = col.dataset.boardId;
        const cnt = col.querySelectorAll('.task-card').length;
        const el = document.getElementById('count-'+boardId);
        if(el) el.textContent = cnt;
    });
}

// Quick Add
function quickAdd(boardId, nama) {
    document.querySelectorAll('[id^="quick-add-"]').forEach(el => el.classList.add('hidden'));
    document.getElementById('quick-add-'+boardId).classList.remove('hidden');
    document.getElementById('quick-input-'+boardId).focus();
}
function cancelQuickAdd(boardId) { document.getElementById('quick-add-'+boardId).classList.add('hidden'); }
function submitQuickAdd(boardId, projectId) {
    const judul = document.getElementById('quick-input-'+boardId).value.trim();
    if (!judul) return;
    const form = document.createElement('form');
    form.method = 'POST'; form.action = `/project/${projectId}/task`;
    form.innerHTML = `<input name="_token" value="${csrfToken}"><input name="judul" value="${judul}"><input name="board_id" value="${boardId}"><input name="tipe" value="task"><input name="prioritas" value="medium">`;
    document.body.appendChild(form); form.submit();
}

// Open task detail
function openTask(taskId) { window.location.href = `/project/${projectId}/task/${taskId}`; }

// Filter
let activeFilters = { assignee: null, prioritas: null };
function filterAssignee(uid) {
    activeFilters.assignee = activeFilters.assignee === uid ? null : uid;
    document.querySelectorAll('[id^="filter-user-"]').forEach(b => b.classList.remove('bg-violet-100','border-violet-400'));
    if(activeFilters.assignee) document.getElementById('filter-user-'+uid)?.classList.add('bg-violet-100','border-violet-400');
    applyFilter();
}
function filterPrioritas(p) {
    activeFilters.prioritas = activeFilters.prioritas === p ? null : p;
    document.querySelectorAll('[id^="filter-pr-"]').forEach(b => b.classList.remove('bg-violet-100','border-violet-400'));
    if(activeFilters.prioritas) document.getElementById('filter-pr-'+p)?.classList.add('bg-violet-100','border-violet-400');
    applyFilter();
}
function clearFilter() {
    activeFilters = {assignee:null, prioritas:null};
    document.querySelectorAll('[id^="filter-"]').forEach(b => b.classList.remove('bg-violet-100','border-violet-400'));
    applyFilter();
}
function applyFilter() {
    document.querySelectorAll('.task-card').forEach(card => {
        const assignees = card.dataset.assignee ? card.dataset.assignee.split(',').map(Number) : [];
        const prioritas = card.dataset.prioritas;
        const showA = !activeFilters.assignee || assignees.includes(activeFilters.assignee);
        const showP = !activeFilters.prioritas || prioritas === activeFilters.prioritas;
        card.style.display = (showA && showP) ? '' : 'none';
    });
}

// Keyboard shortcut
document.addEventListener('keydown', e => {
    if (e.key === 'n' && !e.ctrlKey && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) {
        document.getElementById('modal-task').classList.remove('hidden');
    }
    if (e.key === 'Escape') document.getElementById('modal-task').classList.add('hidden');
});
</script>
@endsection
