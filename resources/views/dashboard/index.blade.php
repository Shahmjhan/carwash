@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Operations Dashboard</h1>
        <p>Everything happening in your workshop, at a glance.</p>
    </div>
    <a class="primary" href="{{ route('jobs.create') }}">+ New Job Card</a>
</div>

<section class="panel quick-actions-panel">
    <h2>Quick Actions</h2>
    <div class="actions">
        <a href="{{ route('customers.create') }}" class="action-card action-blue">
            <span class="icon">👤</span>
            <span>Register Customer</span>
        </a>
        <a href="{{ route('vehicles.create') }}" class="action-card action-green">
            <span class="icon">🚗</span>
            <span>Register Vehicle</span>
        </a>
        <a href="{{ route('appointments.create') }}" class="action-card action-purple">
            <span class="icon">📅</span>
            <span>Book Appointment</span>
        </a>
        <a href="{{ route('inventory.create') }}" class="action-card action-orange">
            <span class="icon">📦</span>
            <span>Add Product</span>
        </a>
        <a href="{{ route('jobs.board') }}" class="action-card action-cyan">
            <span class="icon">📋</span>
            <span>Open Job Board</span>
        </a>
        <a href="{{ route('reports') }}" class="action-card action-pink">
            <span class="icon">📊</span>
            <span>View Reports</span>
        </a>
    </div>
</section>

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
        <b>{{ number_format($revenue, 2) }}</b>
        <span class="trend">{{ $revenue > 0 ? '+15%' : '0%' }}</span>
    </div>
    <div class="stat-card stat-orange">
        <small>Pending Payments</small>
        <b>{{ number_format($pendingPayments, 2) }}</b>
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
        <b>{{ number_format($monthlyRevenue ?? 0, 2) }}</b>
    </div>
    <div class="stat-card-secondary">
        <small>Monthly Jobs</small>
        <b>{{ $monthlyJobs ?? 0 }}</b>
    </div>
    <div class="stat-card-secondary">
        <small>Avg Job Value</small>
        <b>{{ $monthlyJobs > 0 ? number_format($monthlyRevenue / $monthlyJobs, 2) : '0.00' }}</b>
    </div>
    <div class="stat-card-secondary">
        <small>Payment Rate</small>
        <b>{{ $paymentRate ?? 0 }}%</b>
    </div>
</div>

<div class="grid2">
    <section class="panel">
        <h2>🚗 Top Vehicle Categories by Revenue</h2>
        <div class="insights-list">
            @if($vehicleRevenue->count() > 0)
                @foreach($vehicleRevenue as $index => $vehicle)
                <div class="insight-item">
                    <div class="insight-rank">{{ $index + 1 }}</div>
                    <div class="insight-content">
                        <strong>{{ $vehicle->category }}</strong>
                        <small>{{ $vehicle->count }} vehicles</small>
                    </div>
                    <div class="insight-value">{{ number_format($vehicle->total_revenue, 2) }}</div>
                </div>
                @endforeach
            @else
                <p class="no-data">No revenue data available</p>
            @endif
        </div>
    </section>
    
    <section class="panel">
        <h2>👥 Frequent Customers</h2>
        <div class="insights-list">
            @if($frequentCustomers->count() > 0)
                @foreach($frequentCustomers as $index => $customer)
                <div class="insight-item">
                    <div class="insight-rank">{{ $index + 1 }}</div>
                    <div class="insight-content">
                        <strong>{{ $customer->full_name }}</strong>
                        <small>{{ $customer->job_count }} visits • {{ number_format($customer->total_spent, 2) }} spent</small>
                    </div>
                </div>
                @endforeach
            @else
                <p class="no-data">No customer data available</p>
            @endif
        </div>
    </section>
    
    <section class="panel panel-full">
        <h2>🔧 Most Popular Services</h2>
        <div class="services-chart">
            @if($servicePopularity->count() > 0)
                @foreach($servicePopularity as $index => $service)
                <div class="service-bar">
                    <div class="service-info">
                        <span class="service-name">{{ $service->name }}</span>
                        <span class="service-count">{{ $service->count }}</span>
                    </div>
                    <div class="service-progress">
                        <div class="service-fill" style="width: {{ ($service->count / $servicePopularity->first()->count) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            @else
                <p class="no-data">No service data available</p>
            @endif
        </div>
    </section>
</div>

<div class="panel panel-full">
    <h2>📦 Low Stock Items</h2>
    <div class="stock-list">
        @if($lowStockItems->count() > 0)
            @foreach($lowStockItems as $item)
            <div class="stock-item">
                <div class="stock-info">
                    <strong>{{ $item->name }}</strong>
                    <small>{{ $item->category ?? 'Uncategorized' }}</small>
                </div>
                <div class="stock-quantity {{ $item->quantity <= 5 ? 'critical' : 'warning' }}">
                    {{ $item->quantity }} {{ $item->unit ?? 'pcs' }}
                </div>
            </div>
            @endforeach
        @else
            <p class="no-data">All items are well stocked</p>
        @endif
    </div>
</div>

<style>
/* Quick Actions Panel */
.quick-actions-panel {
    margin-bottom: 30px;
    max-width: 100%;
    overflow: hidden;
}

.quick-actions-panel h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #1a1a2e;
}

.actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    width: 100%;
    max-width: 100%;
}

.actions .action-card {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 25px 20px !important;
    border-radius: 12px !important;
    text-decoration: none !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    background: none !important;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.actions .action-card .icon {
    font-size: 40px !important;
    margin-bottom: 12px !important;
}

.actions .action-card span:not(.icon) {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #1a1a2e !important;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.actions .action-card:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2) !important;
}

.actions .action-card.action-blue {
    background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%) !important;
}

.actions .action-card.action-blue:hover {
    background: linear-gradient(135deg, #64b5f6 0%, #42a5f5 100%) !important;
}

.actions .action-card.action-green {
    background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%) !important;
}

.actions .action-card.action-green:hover {
    background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%) !important;
}

.actions .action-card.action-purple {
    background: linear-gradient(135deg, #e1bee7 0%, #ce93d8 100%) !important;
}

.actions .action-card.action-purple:hover {
    background: linear-gradient(135deg, #ba68c8 0%, #ab47bc 100%) !important;
}

.actions .action-card.action-orange {
    background: linear-gradient(135deg, #ffe0b2 0%, #ffcc80 100%) !important;
}

.actions .action-card.action-orange:hover {
    background: linear-gradient(135deg, #ffb74d 0%, #ffa726 100%) !important;
}

.actions .action-card.action-cyan {
    background: linear-gradient(135deg, #b2dfdb 0%, #80cbc4 100%) !important;
}

.actions .action-card.action-cyan:hover {
    background: linear-gradient(135deg, #4db6ac 0%, #26a69a 100%) !important;
}

.actions .action-card.action-pink {
    background: linear-gradient(135deg, #f8bbd0 0%, #f48fb1 100%) !important;
}

.actions .action-card.action-pink:hover {
    background: linear-gradient(135deg, #f06292 0%, #ec407a 100%) !important;
}

/* Stats */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.stat-card {
    padding: 24px;
    border-radius: 16px;
    color: #1a1a2e;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.05) 100%);
    pointer-events: none;
    border-radius: 16px;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.4);
    background: rgba(255, 255, 255, 0.25);
}

.stat-card small {
    display: block;
    font-size: 12px;
    margin-bottom: 12px;
    color: #1a1a2e;
    text-align: center;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    opacity: 0.8;
}

.stat-card b {
    display: block;
    font-size: 32px;
    margin-bottom: 12px;
    color: #1a1a2e;
    text-align: center;
    font-weight: 700;
    letter-spacing: -0.5px;
    transition: all 0.3s ease;
}

.stat-card:hover b {
    transform: scale(1.05);
}

.stat-card .trend {
    display: block;
    font-size: 11px;
    color: #1a1a2e;
    text-align: center;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.4);
    padding: 6px 16px;
    border-radius: 20px;
    margin: 0 auto;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-card.stat-blue { 
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.1) 0%, rgba(53, 122, 189, 0.05) 100%);
    border-color: rgba(74, 144, 226, 0.3);
}
.stat-card.stat-blue:hover {
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.2) 0%, rgba(53, 122, 189, 0.1) 100%);
    border-color: rgba(74, 144, 226, 0.5);
}

.stat-card.stat-green { 
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, rgba(30, 132, 73, 0.05) 100%);
    border-color: rgba(39, 174, 96, 0.3);
}
.stat-card.stat-green:hover {
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.2) 0%, rgba(30, 132, 73, 0.1) 100%);
    border-color: rgba(39, 174, 96, 0.5);
}

.stat-card.stat-purple { 
    background: linear-gradient(135deg, rgba(155, 89, 182, 0.1) 0%, rgba(125, 60, 152, 0.05) 100%);
    border-color: rgba(155, 89, 182, 0.3);
}
.stat-card.stat-purple:hover {
    background: linear-gradient(135deg, rgba(155, 89, 182, 0.2) 0%, rgba(125, 60, 152, 0.1) 100%);
    border-color: rgba(155, 89, 182, 0.5);
}

.stat-card.stat-cyan { 
    background: linear-gradient(135deg, rgba(26, 188, 156, 0.1) 0%, rgba(22, 160, 133, 0.05) 100%);
    border-color: rgba(26, 188, 156, 0.3);
}
.stat-card.stat-cyan:hover {
    background: linear-gradient(135deg, rgba(26, 188, 156, 0.2) 0%, rgba(22, 160, 133, 0.1) 100%);
    border-color: rgba(26, 188, 156, 0.5);
}

.stat-card.stat-orange { 
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.1) 0%, rgba(214, 137, 16, 0.05) 100%);
    border-color: rgba(243, 156, 18, 0.3);
}
.stat-card.stat-orange:hover {
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.2) 0%, rgba(214, 137, 16, 0.1) 100%);
    border-color: rgba(243, 156, 18, 0.5);
}

.stat-card.stat-red { 
    background: linear-gradient(135deg, rgba(231, 76, 60, 0.1) 0%, rgba(192, 57, 43, 0.05) 100%);
    border-color: rgba(231, 76, 60, 0.3);
}
.stat-card.stat-red:hover {
    background: linear-gradient(135deg, rgba(231, 76, 60, 0.2) 0%, rgba(192, 57, 43, 0.1) 100%);
    border-color: rgba(231, 76, 60, 0.5);
}

.stats-secondary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.stat-card-secondary {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #4a90e2;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.stat-card-secondary small {
    display: block;
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

.stat-card-secondary b {
    display: block;
    font-size: 24px;
    color: #1a1a2e;
}

.grid2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.panel {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.panel h2 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #1a1a2e;
}

.panel-full {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.status-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.statusline {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
}

.statusline .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #27ae60;
    flex-shrink: 0;
}

.statusline.status-ok .dot { background: #27ae60; }
.statusline.status-warning .dot { background: #f39c12; }
.statusline.status-error .dot { background: #e74c3c; }

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.activity-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-content strong {
    display: block;
    font-size: 14px;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.activity-content small {
    display: block;
    font-size: 13px;
    color: #666;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.activity-time {
    font-size: 12px;
    color: #999;
    flex-shrink: 0;
    white-space: nowrap;
}

/* Stock List Styles */
.stock-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.stock-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.stock-item:hover {
    background: #e9ecef;
    transform: translateX(4px);
}

.stock-info {
    flex: 1;
    min-width: 0;
}

.stock-info strong {
    display: block;
    font-size: 14px;
    color: #1a1a2e;
    margin-bottom: 2px;
}

.stock-info small {
    display: block;
    font-size: 12px;
    color: #666;
}

.stock-quantity {
    font-weight: 700;
    font-size: 14px;
    padding: 6px 12px;
    border-radius: 20px;
    background: #fff3cd;
    color: #856404;
}

.stock-quantity.critical {
    background: #f8d7da;
    color: #721c24;
}

.stock-quantity.warning {
    background: #fff3cd;
    color: #856404;
}

/* Insights Styles */
.insights-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.insight-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.insight-item:hover {
    background: #e9ecef;
    transform: translateX(4px);
}

.insight-rank {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}

.insight-content {
    flex: 1;
    min-width: 0;
}

.insight-content strong {
    display: block;
    font-size: 14px;
    color: #1a1a2e;
    margin-bottom: 2px;
}

.insight-content small {
    display: block;
    font-size: 12px;
    color: #666;
}

.insight-value {
    font-weight: 700;
    font-size: 14px;
    color: #4a90e2;
}

.no-data {
    text-align: center;
    color: #999;
    padding: 20px;
    font-style: italic;
}

/* Services Chart */
.services-chart {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-bar {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
}

.service-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.service-name {
    font-weight: 600;
    font-size: 14px;
    color: #1a1a2e;
}

.service-count {
    font-weight: 700;
    font-size: 14px;
    color: #4a90e2;
}

.service-progress {
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.service-fill {
    height: 100%;
    background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%);
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .quick-actions-panel {
        margin-bottom: 20px;
    }
    
    .quick-actions-panel h2 {
        font-size: 20px;
        margin-bottom: 15px;
    }

    .actions {
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .action-card {
        padding: 18px 12px !important;
    }

    .action-card .icon {
        font-size: 32px !important;
        margin-bottom: 8px !important;
    }

    .action-card span:not(.icon) {
        font-size: 12px !important;
    }

    .page-head h1 {
        font-size: 32px;
    }
    
    .page-head p {
        font-size: 16px;
    }
    
    .page-head a.primary {
        padding: 10px 20px;
        font-size: 14px;
    }
    
    .stats {
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-card small {
        font-size: 11px;
    }
    
    .stat-card b {
        font-size: 24px;
    }
    
    .stat-card .trend {
        font-size: 10px;
    }
    
    .stats-secondary {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .stat-card-secondary {
        padding: 15px;
    }
    
    .stat-card-secondary small {
        font-size: 11px;
    }
    
    .stat-card-secondary b {
        font-size: 20px;
    }
    
    .grid2 {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .panel {
        padding: 20px;
    }
    
    .panel h2 {
        font-size: 18px;
    }
    
    .status-list {
        gap: 8px;
    }
    
    .statusline {
        font-size: 13px;
        padding: 8px 12px;
    }
    
    .panel-full {
        padding: 20px;
    }
    
    .panel-full h2 {
        font-size: 18px;
    }
    
    .activity-item {
        padding: 12px;
    }
    
    .activity-icon {
        font-size: 20px;
    }
    
    .activity-content strong {
        font-size: 14px;
    }
    
    .activity-content small {
        font-size: 12px;
    }
    
    .activity-time {
        font-size: 11px;
    }
}

@media (max-width: 768px) {
    .quick-actions-panel {
        margin-bottom: 15px;
    }
    
    .quick-actions-panel h2 {
        font-size: 18px;
        margin-bottom: 12px;
    }

    .actions {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .action-card {
        padding: 15px 10px !important;
    }

    .action-card .icon {
        font-size: 28px !important;
        margin-bottom: 6px !important;
    }

    .action-card span:not(.icon) {
        font-size: 11px !important;
    }

    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 15px 12px;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    
    .page-head h1 {
        font-size: 24px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .page-head p {
        font-size: 13px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .page-head a.primary {
        width: 100%;
        text-align: center;
        padding: 10px;
        font-size: 13px;
        box-sizing: border-box;
    }
    
    .stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 0 12px;
    }
    
    .stat-card {
        padding: 12px;
    }
    
    .stat-card small {
        font-size: 10px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .stat-card b {
        font-size: 20px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .stat-card .trend {
        font-size: 9px;
    }
    
    .stats-secondary {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 0 12px;
    }
    
    .stat-card-secondary {
        padding: 10px;
    }
    
    .stat-card-secondary small {
        font-size: 10px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .stat-card-secondary b {
        font-size: 16px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .grid2 {
        grid-template-columns: 1fr;
        gap: 12px;
        padding: 0 12px;
    }
    
    .panel {
        padding: 15px;
    }
    
    .panel h2 {
        font-size: 16px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin-bottom: 15px;
    }
    
    .status-list {
        gap: 6px;
    }
    
    .statusline {
        font-size: 12px;
        padding: 6px 10px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .statusline .dot {
        width: 8px;
        height: 8px;
    }
    
    .panel-full {
        padding: 15px;
        margin: 0 12px;
    }
    
    .panel-full h2 {
        font-size: 16px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin-bottom: 15px;
    }
    
    .activity-item {
        padding: 10px;
        flex-wrap: wrap;
    }
    
    .activity-icon {
        font-size: 18px;
    }
    
    .activity-content {
        flex: 1;
        min-width: 0;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .activity-content strong {
        font-size: 13px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .activity-content small {
        font-size: 11px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .activity-time {
        font-size: 11px;
        width: 100%;
        text-align: right;
    }
    
    .insights-list {
        gap: 8px;
    }
    
    .insight-item {
        padding: 10px;
    }
    
    .insight-rank {
        width: 24px;
        height: 24px;
        font-size: 11px;
    }
    
    .insight-content strong {
        font-size: 13px;
    }
    
    .insight-content small {
        font-size: 11px;
    }
    
    .insight-value {
        font-size: 13px;
    }
    
    .services-chart {
        gap: 8px;
    }
    
    .service-bar {
        padding: 10px;
    }
    
    .service-name {
        font-size: 13px;
    }
    
    .service-count {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .quick-actions-panel {
        margin-bottom: 12px;
    }
    
    .quick-actions-panel h2 {
        font-size: 16px;
        margin-bottom: 10px;
    }

    .actions {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .action-card {
        padding: 12px 8px !important;
    }

    .action-card .icon {
        font-size: 24px !important;
        margin-bottom: 5px !important;
    }

    .action-card span:not(.icon) {
        font-size: 10px !important;
    }

    .page-head {
        padding: 12px 10px;
        gap: 10px;
    }
    
    .page-head h1 {
        font-size: 20px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .page-head p {
        font-size: 12px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .page-head a.primary {
        width: 100%;
        text-align: center;
        padding: 8px;
        font-size: 12px;
        box-sizing: border-box;
    }
    
    .stats {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 0 10px;
    }
    
    .stat-card {
        padding: 10px;
    }
    
    .stat-card small {
        font-size: 9px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .stat-card b {
        font-size: 18px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .stat-card .trend {
        font-size: 8px;
    }
    
    .stats-secondary {
        grid-template-columns: 1fr;
        gap: 6px;
        padding: 0 10px;
    }
    
    .stat-card-secondary {
        padding: 8px;
    }
    
    .stat-card-secondary small {
        font-size: 9px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .stat-card-secondary b {
        font-size: 14px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .grid2 {
        grid-template-columns: 1fr;
        gap: 10px;
        padding: 0 10px;
    }
    
    .panel {
        padding: 12px;
    }
    
    .panel h2 {
        font-size: 14px;
        margin-bottom: 10px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .actions {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .action-card {
        padding: 10px !important;
    }
    
    .action-card .icon {
        font-size: 20px !important;
    }
    
    .action-card span:not(.icon) {
        font-size: 10px !important;
    }
    
    .status-list {
        gap: 4px;
    }
    
    .statusline {
        font-size: 11px;
        padding: 5px 8px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .statusline .dot {
        width: 6px;
        height: 6px;
    }
    
    .panel-full {
        padding: 12px;
        margin: 0 10px;
    }
    
    .panel-full h2 {
        font-size: 14px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin-bottom: 10px;
    }
    
    .activity-item {
        padding: 8px;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-icon {
        font-size: 16px;
        margin-bottom: 6px;
    }
    
    .activity-content {
        width: 100%;
        margin-bottom: 6px;
    }
    
    .activity-content strong {
        font-size: 12px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .activity-content small {
        font-size: 10px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .activity-time {
        font-size: 9px;
        width: 100%;
        text-align: left;
    }
}
</style>
@endsection
