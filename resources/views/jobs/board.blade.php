@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Live Job Board</h1>
        <p>Operational state of every active vehicle.</p>
    </div>
    <a class="primary" href="{{ route('jobs.create') }}">+ New Job</a>
</div>
<div class="kanban">
    @foreach($kanbanColumns as $column)
    <section class="kanban-col" data-status="{{ $column['id'] }}">
        <h3 class="status-{{ $column['color'] }}">
            {{ $column['title'] }}
            <small>{{ $jobs->get($column['id'], collect())->count() }}</small>
        </h3>
        @foreach($jobs->get($column['id'], collect()) as $j)
        <a class="job-card" href="{{ route('jobs.show', $j) }}">
            <b>{{ $j->vehicle->registration_number }}</b>
            <span>{{ $j->customer->full_name }}</span>
            <small>{{ $j->job_number }} · {{ ucfirst($j->priority) }}</small>
            @if($j->technician)
            <small class="technician">👤 {{ $j->technician->name }}</small>
            @endif
            @if($j->branch && !auth()->user()->branch_id)
            <small class="branch">{{ $j->branch->name }}</small>
            @endif
        </a>
        @endforeach
    </section>
    @endforeach
</div>
@endsection
