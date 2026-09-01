@extends('layouts.app')
@section('content')
<style>
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

    .kanban-column {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        min-height: 400px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .column-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
    }

    .column-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .column-count {
        background: #e5e7eb;
        color: #374151;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .job-card {
        display: block;
        background: white;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .job-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-color: #2563eb;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .vehicle-number {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .priority-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .priority-high {
        background: #fee2e2;
        color: #dc2626;
    }

    .priority-medium {
        background: #fef3c7;
        color: #d97706;
    }

    .priority-low {
        background: #dcfce7;
        color: #16a34a;
    }

    .customer-name {
        font-size: 14px;
        color: #374151;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        color: #667085;
        background: #f8fafc;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .technician-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #dbeafe;
        color: #2563eb;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .branch-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f3e8ff;
        color: #9333ea;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        margin-top: 12px;
    }

    .time-badge {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        color: #667085;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-pending { background: #f59e0b; }
    .status-in-progress { background: #3b82f6; }
    .status-completed { background: #10b981; }
    .status-delivered { background: #8b5cf6; }

    /* TV Mode Styles */
    body.tv-mode .sidebar,
    body.tv-mode .page-head,
    body.tv-mode header {
        display: none !important;
    }

    body.tv-mode .main {
        margin-left: 0 !important;
        width: 100% !important;
        padding: 15px;
        height: 100vh;
        overflow: hidden;
    }

    body.tv-mode .kanban-board {
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        height: calc(100vh - 30px);
    }

    body.tv-mode .kanban-column {
        min-height: auto;
        height: 100%;
        padding: 15px;
        overflow: hidden;
    }

    body.tv-mode .column-header {
        margin-bottom: 10px;
        padding-bottom: 8px;
    }

    body.tv-mode .column-title {
        font-size: 18px;
    }

    body.tv-mode .column-count {
        font-size: 14px;
        padding: 4px 12px;
    }

    body.tv-mode .job-card {
        padding: 10px;
        margin-bottom: 8px;
    }

    body.tv-mode .vehicle-number {
        font-size: 16px;
    }

    body.tv-mode .customer-name {
        font-size: 13px;
        margin-bottom: 6px;
    }

    body.tv-mode .priority-badge {
        font-size: 10px;
        padding: 3px 8px;
    }

    body.tv-mode .meta-item,
    body.tv-mode .technician-badge,
    body.tv-mode .branch-badge,
    body.tv-mode .time-badge {
        font-size: 11px;
        padding: 4px 8px;
    }

    body.tv-mode .card-footer {
        margin-top: 8px;
        padding-top: 8px;
    }

    body.tv-mode .card-meta {
        gap: 4px;
        margin-bottom: 6px;
    }

    body.tv-mode .card-header {
        margin-bottom: 8px;
    }

    body.tv-mode .technician-badge,
    body.tv-mode .branch-badge {
        margin-bottom: 6px;
    }

    .tv-toggle-btn {
        background: #2563eb;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: all 0.3s ease;
        margin-left: 10px;
    }

    .tv-toggle-btn:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }

    body.tv-mode .tv-toggle-btn {
        background: #dc2626;
    }

    body.tv-mode .tv-toggle-btn:hover {
        background: #b91c1c;
    }
</style>

<div class="page-head">
    <div>
        <h1>Live Job Board</h1>
        <p>Operational state of every active vehicle.</p>
    </div>
    <div>
        <a class="primary" href="{{ route('jobs.create') }}">+ New Job</a>
        <button class="tv-toggle-btn" id="tvToggle">📺 TV Mode</button>
    </div>
</div>

<div class="kanban-board" id="jobBoard">
    @foreach($kanbanColumns as $column)
    <div class="kanban-column" data-status="{{ $column['id'] }}">
        <div class="column-header">
            <h3 class="column-title status-{{ $column['color'] }}">
                {{ $column['title'] }}
            </h3>
            <span class="column-count">{{ $jobs->get($column['id'], collect())->count() }}</span>
        </div>
        @php
            $columnJobs = $jobs->get($column['id'], collect())->take(5);
        @endphp
        @foreach($columnJobs as $j)
        <a class="job-card" href="{{ route('jobs.show', $j) }}">
            <div class="card-header">
                <h4 class="vehicle-number">{{ $j->vehicle->registration_number }}</h4>
                <span class="priority-badge priority-{{ $j->priority }}">{{ ucfirst($j->priority) }}</span>
            </div>
            <p class="customer-name">{{ $j->customer->full_name }}</p>
            <div class="card-meta">
                <div class="meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    {{ $j->job_number }}
                </div>
                @if($j->vehicle->make)
                <div class="meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    {{ $j->vehicle->make }}
                </div>
                @endif
            </div>
            @if($j->technician)
            <div class="technician-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                {{ $j->technician->name }}
            </div>
            @endif
            @if($j->branch && !auth()->user()->branch_id)
            <div class="branch-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ $j->branch->name }}
            </div>
            @endif
            <div class="card-footer">
                <div class="time-badge">
                    <span class="status-dot status-{{ str_replace('_', '-', $column['id']) }}"></span>
                    {{ $j->created_at->diffForHumans() }}
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endforeach
</div>

<script>
let lastUpdate = {{ now()->timestamp }};

// TV Mode Toggle
const tvToggle = document.getElementById('tvToggle');
tvToggle.addEventListener('click', () => {
    document.body.classList.toggle('tv-mode');
    if (document.body.classList.contains('tv-mode')) {
        tvToggle.textContent = '❌ Exit TV Mode';
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        }
    } else {
        tvToggle.textContent = '📺 TV Mode';
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
});

function refreshJobBoard() {
    fetch('{{ route('jobs.board') }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newBoard = doc.getElementById('jobBoard');
        const currentBoard = document.getElementById('jobBoard');
        if (newBoard && currentBoard) {
            currentBoard.innerHTML = newBoard.innerHTML;
        }
    })
    .catch(error => console.error('Error refreshing job board:', error));
}

setInterval(refreshJobBoard, 2000);
</script>
@endsection
