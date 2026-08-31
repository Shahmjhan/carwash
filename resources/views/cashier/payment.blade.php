@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Payment Processing</h1>
        <p>{{ $job->vehicle->registration_number }} · {{ $job->customer->full_name }}</p>
    </div>
    <a class="secondary" href="{{ route('cashier.index') }}">Back to Dashboard</a>
</div>

<div class="grid2">
    <section class="panel">
        <h2>Job Details</h2>
        <div class="details">
            <span>Job Number</span><b>{{ $job->job_number }}</b>
            <span>Vehicle</span><b>{{ $job->vehicle->registration_number }} · {{ $job->vehicle->make }} {{ $job->vehicle->model }}</b>
            <span>Customer</span><b>{{ $job->customer->full_name }}</b>
            <span>Priority</span><b>{{ ucfirst($job->priority) }}</b>
        </div>
    </section>
    
    <section class="panel">
        <h2>Services</h2>
        @forelse($job->services as $service)
            <div class="listrow">
                <b>{{ $service->name_snapshot }}</b>
                <span>Rs. {{ number_format($service->unit_price, 2) }} × {{ $service->quantity }}</span>
            </div>
        @empty
            <p class="empty">No services.</p>
        @endforelse
    </section>
</div>

<div class="panel">
    <h2>Parts Used</h2>
    @forelse($job->parts as $part)
        <div class="listrow">
            <b>{{ $part->product->name }}</b>
            <span>{{ $part->quantity }} × Rs. {{ number_format($part->unit_price, 2) }}</span>
        </div>
    @empty
        <p class="empty">No parts used.</p>
    @endforelse
</div>

<div class="panel">
    <h2>Payment</h2>
    @if($job->invoice)
        <div class="details">
            <span>Subtotal</span><b>Rs. {{ number_format($job->invoice->subtotal, 2) }}</b>
            <span>Tax</span><b>Rs. {{ number_format($job->invoice->tax, 2) }}</b>
            <span style="font-size: 1.1em; font-weight: bold;">Total Due</span><b style="font-size: 1.4em; color: #4a90e2;">Rs. {{ number_format($job->invoice->total, 2) }}</b>
        </div>
        
        <form method="post" action="{{ route('cashier.process-payment', $job) }}" id="paymentForm">
            @csrf
            <div class="form-grid">
                <label>Payment Method*
                    <select name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="upi">UPI</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </label>
                <label>Coupon/Voucher Code
                    <input type="text" name="coupon_code" id="couponCode" placeholder="Enter coupon code" oninput="applyCoupon()">
                </label>
                <label>Discount Type
                    <select name="discount_type" id="discountType" onchange="calculateTotal()">
                        <option value="none">No Discount</option>
                        <option value="amount">Fixed Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </label>
                <label>Discount Value
                    <input type="number" step=".01" name="discount_value" id="discountValue" placeholder="0.00" oninput="calculateTotal()">
                </label>
                <label>Amount Received*
                    <input type="number" step=".01" name="amount_received" id="amountReceived" placeholder="Enter amount received" required oninput="calculateBalance()" onwheel="this.blur()">
                </label>
            </div>
            
            <div class="balance-display" id="balanceDisplay" style="display: none; margin-top: 20px; padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #4a90e2;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 1.1em;">Balance to Return:</span>
                    <span id="balanceAmount" style="font-size: 1.5em; font-weight: bold; color: #10b981;">Rs. 0.00</span>
                </div>
            </div>
            
            <div class="payment-summary" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Subtotal:</span>
                    <strong>Rs. {{ number_format($job->invoice->subtotal, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;" id="discountRow">
                    <span>Discount:</span>
                    <strong id="displayDiscount">Rs. 0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-weight: bold;">Total Due:</span>
                    <strong id="displayTotal" style="font-weight: bold; color: #4a90e2;">Rs. {{ number_format($job->invoice->total, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Amount Received:</span>
                    <strong id="displayReceived">Rs. 0.00</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 2px solid #ddd; padding-top: 10px;">
                    <span style="font-weight: bold;">Balance:</span>
                    <strong id="displayBalance" style="font-weight: bold; color: #ef4444;">Rs. {{ number_format($job->invoice->total, 2) }}</strong>
                </div>
            </div>
            
            <button class="primary" style="margin-top: 20px; width: 100%;">Process Payment</button>
        </form>
    @else
        <p class="empty">Invoice not yet generated.</p>
    @endif
</div>

<script>
const originalTotal = {{ $job->invoice ? $job->invoice->total : 0 }};
const subtotal = {{ $job->invoice ? $job->invoice->subtotal : 0 }};
let currentTotal = originalTotal;

function calculateTotal() {
    const discountType = document.getElementById('discountType').value;
    const discountValue = parseFloat(document.getElementById('discountValue').value) || 0;
    
    let discountAmount = 0;
    
    if (discountType === 'amount') {
        discountAmount = Math.min(discountValue, originalTotal);
    } else if (discountType === 'percentage') {
        discountAmount = (originalTotal * discountValue) / 100;
    }
    
    currentTotal = originalTotal - discountAmount;
    
    document.getElementById('displayDiscount').textContent = 'Rs. ' + discountAmount.toFixed(2);
    document.getElementById('displayTotal').textContent = 'Rs. ' + currentTotal.toFixed(2);
    
    // Recalculate balance with new total
    calculateBalance();
}

function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value;
    // This would typically make an AJAX call to validate the coupon
    // For now, we'll just log it
    console.log('Applying coupon:', couponCode);
}

function calculateBalance() {
    const amountReceived = parseFloat(document.getElementById('amountReceived').value) || 0;
    const balance = amountReceived - currentTotal;
    
    document.getElementById('displayReceived').textContent = 'Rs. ' + amountReceived.toFixed(2);
    document.getElementById('displayBalance').textContent = 'Rs. ' + Math.abs(balance).toFixed(2);
    
    const balanceDisplay = document.getElementById('balanceDisplay');
    const balanceAmount = document.getElementById('balanceAmount');
    
    if (amountReceived > 0) {
        balanceDisplay.style.display = 'block';
        if (balance >= 0) {
            balanceAmount.textContent = 'Rs. ' + balance.toFixed(2);
            balanceAmount.style.color = '#10b981';
            document.getElementById('displayBalance').style.color = '#10b981';
        } else {
            balanceAmount.textContent = 'Rs. ' + Math.abs(balance).toFixed(2);
            balanceAmount.style.color = '#ef4444';
            document.getElementById('displayBalance').style.color = '#ef4444';
        }
    } else {
        balanceDisplay.style.display = 'none';
    }
}
</script>
@endsection
