<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'AutoCare Pro' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <aside class="sidebar">
        <div class="brand">AUTO<span>CARE</span><small>PRO</small></div>
        <nav>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('jobs.board') }}">Live Job Board</a>
            <a href="{{ route('jobs.index') }}">Job Cards</a>
            <a href="{{ route('customers.index') }}">Customers</a>
            <a href="{{ route('vehicles.index') }}">Vehicles</a>
            <a href="{{ route('appointments.index') }}">Appointments</a>
            <a href="{{ route('inventory.index') }}">Inventory</a>
            <a href="{{ route('invoices.index') }}">Billing</a>
            <a href="{{ route('reports') }}">Reports</a>
        </nav>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button class="logout">Sign out</button>
        </form>
    </aside>
    <main class="main">
        <header>
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
</body>
</html>
