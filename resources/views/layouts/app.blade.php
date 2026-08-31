<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AutoCare Pro' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">✕</button>
        <div class="brand">AUTO<span>CARE</span><small>PRO</small></div>
        <nav>
            @if(auth()->user()->hasPermission('view_reception'))
                <a href="{{ route('reception.index') }}" class="reception-link">🚗 Reception</a>
            @endif
            @if(auth()->user()->hasPermission('view_dashboard'))
                <a href="{{ route('dashboard') }}">Dashboard</a>
            @endif
            @if(auth()->user()->hasPermission('view_live_job_board'))
                <a href="{{ route('jobs.board') }}">Live Job Board</a>
            @endif
            @if(auth()->user()->hasPermission('view_job_cards'))
                <a href="{{ route('jobs.index') }}">Job Cards</a>
            @endif
            @if(auth()->user()->hasPermission('view_customers'))
                <a href="{{ route('customers.index') }}">Customers</a>
            @endif
            @if(auth()->user()->hasPermission('view_vehicles'))
                <a href="{{ route('vehicles.index') }}">Vehicles</a>
            @endif
            @if(auth()->user()->hasPermission('view_appointments'))
                <a href="{{ route('appointments.index') }}">Appointments</a>
            @endif
            @if(auth()->user()->hasPermission('view_item_master'))
                <a href="{{ route('inventory.index') }}">Item Master</a>
                <a href="{{ route('categories.index') }}">Categories</a>
            @endif
            @if(auth()->user()->hasPermission('view_billing'))
                <a href="{{ route('invoices.index') }}">Billing</a>
                <a href="{{ route('cashier.index') }}" class="cashier-link">
                    💰 Cashier
                    @php
                        $readyForPaymentCount = \App\Models\Job::where('status', \App\Enums\JobStatus::READY_FOR_PAYMENT->value)->count();
                    @endphp
                    @if($readyForPaymentCount > 0)
                        <span class="notification-badge">{{ $readyForPaymentCount }}</span>
                    @endif
                </a>
            @endif
            @if(auth()->user()->hasPermission('view_reports'))
                <a href="{{ route('reports') }}">Reports</a>
            @endif
            @if(auth()->user()->hasPermission('view_users'))
                <a href="{{ route('users.index') }}">Users</a>
            @endif
            @if(auth()->user()->hasPermission('view_settings'))
                <a href="{{ route('settings.billing') }}">Settings</a>
            @endif
        </nav>
        <a href="{{ route('logout') }}" class="logout">Sign out</a>
    </aside>
    <main class="main">
        <header>
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
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
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
        
        sidebarClose.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>

    <style>
        .sidebar-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 24px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-right: 15px;
        }

        .sidebar-toggle span {
            width: 100%;
            height: 3px;
            background: #1a1a2e;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover span {
            background: #4a90e2;
        }

        .sidebar-close {
            display: none;
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s ease;
        }

        .sidebar-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
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
                left: -280px !important;
                top: 0 !important;
                height: 100vh !important;
                width: 280px !important;
                z-index: 1000 !important;
                transition: left 0.3s ease !important;
                background: #0a1f33 !important;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
            }

            aside.sidebar.active {
                left: 0 !important;
            }

            aside.sidebar nav {
                display: flex !important;
                flex-direction: column !important;
                padding: 15px !important;
            }

            aside.sidebar nav a {
                background: rgba(255, 255, 255, 0.05) !important;
                color: white !important;
                padding: 12px 15px !important;
                border-radius: 0 !important;
                text-align: left !important;
                font-size: 14px !important;
                transition: all 0.3s ease !important;
                margin-bottom: 2px !important;
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            aside.sidebar nav a:nth-child(odd) {
                background: rgba(255, 255, 255, 0.08) !important;
            }

            aside.sidebar nav a:nth-child(even) {
                background: rgba(255, 255, 255, 0.03) !important;
            }

            aside.sidebar nav a:hover {
                background: rgba(74, 144, 226, 0.3) !important;
                padding-left: 20px !important;
            }

            aside.sidebar .brand {
                color: white !important;
                text-align: center !important;
                padding: 25px 10px 20px 10px !important;
            }

            aside.sidebar .logout {
                background: rgba(255, 255, 255, 0.1) !important;
                color: white !important;
                text-align: left !important;
                margin: 15px !important;
                padding: 12px 15px !important;
                border-radius: 0 !important;
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
                width: 260px !important;
                left: -260px !important;
                background: #0a1f33 !important;
            }

            aside.sidebar nav a {
                padding: 10px 12px !important;
                font-size: 13px !important;
            }

            .sidebar-toggle {
                width: 24px;
                height: 20px;
            }

            .sidebar-toggle span {
                height: 2px;
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
