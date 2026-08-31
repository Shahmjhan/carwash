@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Cashier Dashboard</h1>
        <p>Process payments for completed vehicles</p>
    </div>
    <form class="search" method="get" action="{{ route('cashier.search') }}">
        <input name="q" placeholder="Search by registration, customer, or job number..." required>
        <button>Search</button>
    </form>
</div>

<div class="panel">
    <h2>Vehicles Ready for Payment</h2>
    @forelse($readyForPayment as $job)
        <div class="listrow" style="cursor: pointer;" onclick="window.location.href='{{ route('cashier.payment', $job) }}'">
            <div>
                <b>{{ $job->vehicle->registration_number }}</b>
                <span>{{ $job->customer->full_name }}</span>
                <small>{{ $job->job_number }}</small>
            </div>
            <div>
                @if($job->invoice)
                    <span class="badge large">Rs. {{ number_format($job->invoice->total, 2) }}</span>
                @else
                    <span class="badge large">Calculating...</span>
                @endif
                <small>{{ $job->updated_at->diffForHumans() }}</small>
            </div>
        </div>
    @empty
        <p class="empty">No vehicles ready for payment.</p>
    @endforelse
</div>

<script>
setInterval(function() {
    location.reload();
}, 5000);
</script>
@endsection
