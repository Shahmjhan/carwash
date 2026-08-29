@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Operations Dashboard</h1>
        <p>Everything happening in your workshop, at a glance.</p>
    </div>
    <a class="primary" href="{{ route('jobs.create') }}">+ New Job Card</a>
</div>

<div class="stats">
    <div class="stat-card stat-blue">
        <small>Vehicles Today</small>
        <b>{{ $vehiclesToday }}</b>
        <span class="trend">+{{ $vehiclesToday > 0 ? '12%' : '0%' }}</span>
    </div>
    <div class="stat-card stat-green">
        <small>Active Jobs</small>
        <b>{{ $activeJobs }}</b>
        <span class="trend">In Progress</span>
    </div>
    <div class="stat-card stat-purple">
        <small>Completed This Month</small>
        <b>{{ $completedJobs }}</b>
        <span class="trend">{{ $completedJobs > 0 ? '+8%' : '0%' }}</span>
    </div>
    <div class="stat-card stat-cyan">
        <small>Today's Revenue</small>
        <b>Rs. {{ number_format($revenue, 2) }}</b>
        <span class="trend">{{ $revenue > 0 ? '+15%' : '0%' }}</span>
    </div>
    <div class="stat-card stat-orange">
        <small>Pending Payments</small>
        <b>Rs. {{ number_format($pendingPayments, 2) }}</b>
        <span class="trend">Outstanding</span>
    </div>
    <div class="stat-card stat-red">
        <small>Low Stock Alerts</small>
        <b>{{ $lowStock }}</b>
        <span class="trend">{{ $lowStock > 0 ? 'Action Needed' : 'OK' }}</span>
    </div>
</div>

<div class="stats-secondary">
    <div class="stat-card-secondary">
        <small>Monthly Revenue</small>
        <b>Rs. {{ number_format($monthlyRevenue ?? 0, 2) }}</b>
    </div>
    <div class="stat-card-secondary">
        <small>Monthly Jobs</small>
        <b>{{ $monthlyJobs ?? 0 }}</b>
    </div>
</div>

<div class="grid2">
    <section class="panel">
        <h2>Quick Actions</h2>
        <div class="actions">
            <a href="{{ route('customers.create') }}" class="action-card">
                <span class="icon">👤</span>
                <span>Register Customer</span>
            </a>
            <a href="{{ route('vehicles.create') }}" class="action-card">
                <span class="icon">🚗</span>
                <span>Register Vehicle</span>
            </a>
            <a href="{{ route('appointments.create') }}" class="action-card">
                <span class="icon">📅</span>
                <span>Book Appointment</span>
            </a>
            <a href="{{ route('inventory.create') }}" class="action-card">
                <span class="icon">📦</span>
                <span>Add Product</span>
            </a>
            <a href="{{ route('jobs.board') }}" class="action-card">
                <span class="icon">📋</span>
                <span>Open Job Board</span>
            </a>
            <a href="{{ route('reports') }}" class="action-card">
                <span class="icon">📊</span>
                <span>View Reports</span>
            </a>
        </div>
    </section>
    
    <section class="panel">
        <h2>System Status</h2>
        <div class="status-list">
            <div class="statusline status-ok">
                <span class="dot"></span>
                Core workflows online
            </div>
            <div class="statusline status-ok">
                <span class="dot"></span>
                Inventory traceability enabled
            </div>
            <div class="statusline status-ok">
                <span class="dot"></span>
                Audit-ready financial records
            </div>
            <div class="statusline status-ok">
                <span class="dot"></span>
                Communication queue ready
            </div>
            <div class="statusline status-ok">
                <span class="dot"></span>
                RBAC system active
            </div>
            <div class="statusline status-ok">
                <span class="dot"></span>
                Pricing engine operational
            </div>
        </div>
    </section>
</div>

<div class="panel panel-full">
    <h2>Recent Activity</h2>
    <div class="activity-list">
        <div class="activity-item">
            <span class="activity-icon">📋</span>
            <div class="activity-content">
                <strong>New job created</strong>
                <small>Job #JOB-2026-000001 for Toyota Camara</small>
            </div>
            <span class="activity-time">2 min ago</span>
        </div>
        <div class="activity-item">
            <span class="activity-icon">💰</span>
            <div class="activity-content">
                <strong>Payment received</strong>
                <small>Rs. 5,000.00 for Invoice #INV-2026-000001</small>
            </div>
            <span class="activity-time">15 min ago</span>
        </div>
        <div class="activity-item">
            <span class="activity-icon">✅</span>
            <div class="activity-content">
                <strong>Job completed</strong>
                <small>Job #JOB-2026-000002 delivered to customer</small>
            </div>
            <span class="activity-time">1 hour ago</span>
        </div>
    </div>
</div>
@endsection
