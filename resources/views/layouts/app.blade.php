<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AutoCare Pro' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .sidebar-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 30px !important;
            height: 30px !important;
            background: transparent !important;
            border: none !important;
            cursor: pointer !important;
            padding: 0 !important;
            margin-right: 15px !important;
        }

        #sidebarToggle {
            transition: transform 0.3s ease !important;
        }

        #sidebarToggle.collapsed svg {
            transform: rotate(180deg) !important;
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">✕</button>
        @php
            $business = auth()->user()->business;
            $settings = $business ? $business->getBillingSettings() : [
                'company_name' => 'AutoCare Pro',
                'logo_path' => ''
            ];
        @endphp
        <div class="brand">
            @if($settings['logo_path'])
                <img src="{{ asset($settings['logo_path']) }}" alt="{{ $settings['company_name'] }}" class="brand-logo">
            @else
                <span class="brand-text">AUTO<span>CARE</span><small>PRO</small></span>
            @endif
        </div>
        <nav>
            @if(auth()->user()->hasPermission('view_reception'))
                <a href="{{ route('reception.index') }}" class="reception-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                    <span>Reception</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_dashboard'))
                <a href="{{ route('dashboard') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_live_job_board'))
                <a href="{{ route('jobs.board') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0,1 3-3h7z"/></svg>
                    <span>Live Job Board</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_job_cards'))
                <a href="{{ route('jobs.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Job Cards</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_customers'))
                <a href="{{ route('customers.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Customers</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_vehicles'))
                <a href="{{ route('vehicles.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Vehicles</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_appointments'))
                <a href="{{ route('appointments.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Appointments</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_item_master'))
                <a href="{{ route('inventory.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    <span>Item Master</span>
                </a>
                <a href="{{ route('categories.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <span>Categories</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_invoices'))
                <a href="{{ route('invoices.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span>Invoices</span>
                </a>
                <a href="{{ route('cashier.index') }}" class="cashier-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Cashier</span>
                    @php
                        $readyForPaymentCount = \App\Models\Job::where('status', \App\Enums\JobStatus::READY_FOR_PAYMENT->value)->count();
                    @endphp
                    @if($readyForPaymentCount > 0)
                        <span class="notification-badge">{{ $readyForPaymentCount }}</span>
                    @endif
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_reports'))
                <a href="{{ route('reports') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <span>Reports</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_users'))
                <a href="{{ route('users.index') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Users</span>
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_settings'))
                <a href="{{ route('settings.billing') }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Settings</span>
                </a>
            @endif
        </nav>
        <a href="{{ route('logout') }}" class="logout">Sign out</a>
    </aside>
    <main class="main">
        <div style="position:fixed;top:15px;left:15px;z-index:9999;">
            <button id="sidebarToggle" aria-label="Toggle menu" style="display:flex !important;align-items:center !important;justify-content:center !important;width:40px !important;height:40px !important;background:white !important;border:1px solid #e5e7eb !important;border-radius:8px !important;cursor:pointer !important;padding:0 !important;box-shadow:0 2px 4px rgba(0,0,0,0.1) !important;z-index:9999 !important;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1a1a2e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
        </div>
        <header style="padding-left:60px;">
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <span class="muted"> · {{ str_replace('_',' ',ucfirst(auth()->user()->role)) }}</span>
            </div>
            <div class="branch">{{ optional(auth()->user()->branch)->name ?? 'All branches' }}</div>
        </header>
        @if(session('success'))
            <div class="toast success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="toast error">{{ $errors->first() }}</div>
        @endif
        <div class="content">
            @yield('content')
        </div>
    </main>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebar = document.getElementById('sidebar');
        const main = document.querySelector('.main');
        
        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                // Mobile/tablet - use active class for slide-in
                sidebar.classList.toggle('active');
            } else {
                // Desktop - use collapsed class for hide/show
                sidebar.classList.toggle('collapsed');
                main.classList.toggle('expanded');
                sidebarToggle.classList.toggle('collapsed');
            }
        });
        
        sidebarClose.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
        
        // Close sidebar when clicking outside (mobile/tablet only)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>

    <style>
        .sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-right: 15px;
            transition: transform 0.3s ease;
        }

        .sidebar-toggle svg {
            stroke: #1a1a2e;
            transition: stroke 0.3s ease, transform 0.3s ease;
        }

        .sidebar-toggle:hover svg {
            stroke: #4a90e2;
        }

        .sidebar-toggle.collapsed svg {
            transform: rotate(180deg);
        }

        header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 15px 20px !important;
            background: white !important;
            border-bottom: 1px solid #e5e7eb !important;
        }

        header > div {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .sidebar-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 30px !important;
            height: 30px !important;
            background: transparent !important;
            border: none !important;
            cursor: pointer !important;
            padding: 0 !important;
            margin-right: 15px !important;
            transition: transform 0.3s ease !important;
        }

        .sidebar-close {
            display: none;
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 18px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s ease;
            align-items: center;
            justify-content: center;
        }

        .sidebar-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .brand-logo {
            max-height: 80px;
            max-width: 80px;
            object-fit: contain;
            border-radius: 50%;
            display: block;
            margin: 0 auto;
        }

        .brand-text {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .brand-text span {
            color: #4a90e2;
        }

        .brand-text small {
            font-size: 14px;
            color: #4a90e2;
        }

        .brand {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px 10px 20px 10px;
        }

        aside.sidebar nav {
            display: flex;
            flex-direction: column;
            padding: 15px;
            overflow-y: auto;
            max-height: calc(100vh - 150px);
        }

        aside.sidebar nav::-webkit-scrollbar {
            width: 6px;
        }

        aside.sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }

        aside.sidebar nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        aside.sidebar nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        aside.sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin-bottom: 3px;
            font-size: 14px;
        }

        aside.sidebar nav a svg {
            flex-shrink: 0;
            color: white;
            width: 18px;
            height: 18px;
        }

        aside.sidebar nav a span {
            color: rgba(255, 255, 255, 0.9);
        }

        aside.sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        aside.sidebar nav a:hover svg {
            color: white;
        }

        aside.sidebar nav a:hover span {
            color: white;
        }

        aside.sidebar nav a.reception-link {
            background: rgba(74, 144, 226, 0.2);
            color: white;
        }

        aside.sidebar nav a.reception-link svg {
            color: #4a90e2;
        }

        aside.sidebar nav a.reception-link:hover {
            background: rgba(74, 144, 226, 0.3);
        }

        aside.sidebar nav a.cashier-link {
            background: rgba(16, 185, 129, 0.2);
            color: white;
        }

        aside.sidebar nav a.cashier-link svg {
            color: #10b981;
        }

        aside.sidebar nav a.cashier-link:hover {
            background: rgba(16, 185, 129, 0.3);
        }

        @media (min-width: 1025px) {
            .sidebar-toggle {
                display: flex !important;
            }

            aside.sidebar {
                width: 300px !important;
                margin-left: 0 !important;
                transition: margin-left 0.3s ease !important;
            }

            aside.sidebar.collapsed {
                margin-left: -300px !important;
            }

            .main {
                margin-left: 300px !important;
                transition: margin-left 0.3s ease !important;
                width: calc(100% - 300px) !important;
            }

            .main.expanded {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar-toggle {
                display: flex;
            }

            .sidebar-close {
                display: block;
            }

            aside.sidebar {
                position: fixed !important;
                left: -260px !important;
                top: 0 !important;
                height: 100vh !important;
                width: 260px !important;
                z-index: 1000 !important;
                transition: left 0.3s ease !important;
                background: #0a1f33 !important;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
            }

            aside.sidebar.active {
                left: 0 !important;
            }

            aside.sidebar nav a {
                padding: 9px 14px !important;
                font-size: 14px !important;
                gap: 9px !important;
            }

            aside.sidebar nav a svg {
                width: 17px !important;
                height: 17px !important;
            }

            .main {
                margin-left: 0 !important;
            }

            header {
                padding: 15px;
            }
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: flex;
            }

            .sidebar-close {
                display: block;
            }

            aside.sidebar {
                position: fixed !important;
                left: -250px !important;
                top: 0 !important;
                height: 100vh !important;
                width: 250px !important;
                z-index: 1000 !important;
                transition: left 0.3s ease !important;
                background: #0a1f33 !important;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
            }

            aside.sidebar.active {
                left: 0 !important;
            }

            aside.sidebar nav a {
                padding: 9px 13px !important;
                font-size: 14px !important;
                gap: 9px !important;
            }

            aside.sidebar nav a svg {
                width: 17px !important;
                height: 17px !important;
            }

            .main {
                margin-left: 0 !important;
            }

            header {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            aside.sidebar {
                width: 240px !important;
                left: -240px !important;
            }

            aside.sidebar nav a {
                padding: 8px 12px !important;
                font-size: 13px !important;
                gap: 8px !important;
            }

            aside.sidebar nav a svg {
                width: 16px !important;
                height: 16px !important;
            }

            .sidebar-toggle {
                width: 24px;
                height: 24px;
            }
        }

        .cashier-link {
            position: relative !important;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
    </style>
</body>
</html>
