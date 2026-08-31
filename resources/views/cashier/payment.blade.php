@extends('layouts.app')
@section('content')
<div class="payment-page">
    <div class="payment-header">
        <div class="header-content">
            <a href="{{ route('cashier.index') }}" class="back-button">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
            <h1>Payment Processing</h1>
            <p>{{ $job->vehicle->registration_number }} · {{ $job->customer->full_name }}</p>
        </div>
    </div>

    <div class="payment-content">
        <div class="left-column">
            <div class="glass-card job-details-card">
                <div class="card-header">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h2>Job Details</h2>
                </div>
                <div class="detail-row">
                    <span class="label">Job Number</span>
                    <span class="value">{{ $job->job_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Vehicle</span>
                    <span class="value">{{ $job->vehicle->registration_number }} · {{ $job->vehicle->make }} {{ $job->vehicle->model }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Customer</span>
                    <span class="value">{{ $job->customer->full_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Priority</span>
                    <span class="value priority-badge">{{ ucfirst($job->priority) }}</span>
                </div>
            </div>
            
            <div class="glass-card services-card">
                <div class="card-header">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h2>Services</h2>
                </div>
                @forelse($job->services as $service)
                    <div class="service-item">
                        <span class="service-name">{{ $service->name_snapshot }}</span>
                        <span class="service-price">Rs. {{ number_format($service->unit_price, 2) }} × {{ $service->quantity }}</span>
                    </div>
                @empty
                    <p class="empty-state">No services.</p>
                @endforelse
            </div>
        </div>

        <div class="right-column">
            <div class="glass-card parts-card">
                <div class="card-header">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <h2>Parts Used</h2>
                </div>
                @forelse($job->parts as $part)
                    <div class="part-item">
                        <span class="part-name">{{ $part->product->name }}</span>
                        <span class="part-price">{{ $part->quantity }} × Rs. {{ number_format($part->unit_price, 2) }}</span>
                    </div>
                @empty
                    <p class="empty-state">No parts used.</p>
                @endforelse
            </div>

            @if($job->invoice)
                <div class="glass-card payment-card">
                    <div class="card-header">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <h2>Payment Summary</h2>
                    </div>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rs. {{ number_format($job->invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax</span>
                        <span>Rs. {{ number_format($job->invoice->tax, 2) }}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Due</span>
                        <span class="total-amount">Rs. {{ number_format($job->invoice->total, 2) }}</span>
                    </div>
                    
                    <form method="post" action="{{ route('cashier.process-payment', $job) }}" id="paymentForm">
                        @csrf
                        <div class="form-section">
                            <label>Payment Method</label>
                            <div class="payment-methods">
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="cash" checked>
                                    <span class="method-icon">💵</span>
                                    <span class="method-label">Cash</span>
                                </label>
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="card">
                                    <span class="method-icon">💳</span>
                                    <span class="method-label">Card</span>
                                </label>
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="upi">
                                    <span class="method-icon">📱</span>
                                    <span class="method-label">UPI</span>
                                </label>
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <span class="method-icon">🏦</span>
                                    <span class="method-label">Bank Transfer</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <label>Coupon/Voucher Code</label>
                            <input type="text" name="coupon_code" id="couponCode" placeholder="Enter coupon code" oninput="applyCoupon()">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-section">
                                <label>Discount Type</label>
                                <select name="discount_type" id="discountType" onchange="calculateTotal()">
                                    <option value="none">No Discount</option>
                                    <option value="amount">Fixed Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </div>
                            <div class="form-section">
                                <label>Discount Value</label>
                                <input type="number" step=".01" name="discount_value" id="discountValue" placeholder="0.00" oninput="calculateTotal()">
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <label>Amount Received</label>
                            <input type="number" step=".01" name="amount_received" id="amountReceived" placeholder="Enter amount received" required oninput="calculateBalance()" onwheel="this.blur()">
                        </div>
                        
                        <div class="balance-card" id="balanceDisplay" style="display: none;">
                            <div class="balance-content">
                                <span>Balance to Return</span>
                                <span id="balanceAmount">Rs. 0.00</span>
                            </div>
                        </div>
                        
                        <div class="live-summary">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <strong>Rs. {{ number_format($job->invoice->subtotal, 2) }}</strong>
                            </div>
                            <div class="summary-row" id="discountRow">
                                <span>Discount</span>
                                <strong id="displayDiscount">Rs. 0.00</strong>
                            </div>
                            <div class="summary-row">
                                <span>Total Due</span>
                                <strong id="displayTotal">Rs. {{ number_format($job->invoice->total, 2) }}</strong>
                            </div>
                            <div class="summary-row">
                                <span>Amount Received</span>
                                <strong id="displayReceived">Rs. 0.00</strong>
                            </div>
                            <div class="summary-row final-row">
                                <span>Balance</span>
                                <strong id="displayBalance">Rs. {{ number_format($job->invoice->total, 2) }}</strong>
                            </div>
                        </div>
                        
                        <button type="submit" class="process-button">
                            <span>Process Payment</span>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </form>
                @else
                    <div class="glass-card">
                        <p class="empty-state">Invoice not yet generated.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.payment-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 0;
}

.payment-header {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 32px 40px;
}

.header-content h1 {
    color: white;
    font-size: 32px;
    font-weight: 700;
    margin: 8px 0 4px 0;
}

.header-content p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 16px;
    margin: 0;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 8px 16px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.back-button:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(-4px);
}

.payment-content {
    padding: 40px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    max-width: 1400px;
    margin: 0 auto;
}

.left-column, .right-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.glass-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f5f9;
}

.card-header svg {
    color: #667eea;
}

.card-header h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
}

.detail-row .value {
    color: #1e293b;
    font-size: 15px;
    font-weight: 600;
}

.priority-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.service-item, .part-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.service-item:hover, .part-item:hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    transform: translateX(4px);
}

.service-name, .part-name {
    color: #475569;
    font-size: 15px;
    font-weight: 500;
}

.service-price, .part-price {
    color: #667eea;
    font-size: 15px;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    color: #94a3b8;
    font-size: 15px;
    padding: 20px;
    margin: 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
}

.summary-row:last-child {
    border-bottom: none;
}

.total-row {
    padding: 18px 0;
    border-bottom: 2px solid #667eea;
}

.total-amount {
    font-size: 24px;
    font-weight: 700;
    color: #667eea;
}

.form-section {
    margin-bottom: 20px;
}

.form-section label {
    display: block;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-section input,
.form-section select {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    background: #f8fafc;
    transition: all 0.3s ease;
    color: #1e293b;
}

.form-section input:focus,
.form-section select:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.payment-method-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 12px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method-option:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.payment-method-option input[type="radio"] {
    display: none;
}

.payment-method-option input[type="radio"]:checked + .method-icon {
    transform: scale(1.2);
}

.payment-method-option:has(input:checked) {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.method-icon {
    font-size: 28px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.method-label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.balance-card {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 16px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
}

.balance-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.balance-content span:first-child {
    color: white;
    font-size: 16px;
    font-weight: 600;
}

.balance-content span:last-child {
    color: white;
    font-size: 28px;
    font-weight: 700;
}

.live-summary {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid #e2e8f0;
}

.live-summary .summary-row {
    padding: 12px 0;
}

.final-row {
    padding-top: 16px;
    border-top: 2px solid #e2e8f0;
    margin-top: 8px;
}

.process-button {
    width: 100%;
    padding: 18px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 24px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
}

.process-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.5);
}

.process-button svg {
    transition: all 0.3s ease;
}

.process-button:hover svg {
    transform: translateX(4px);
}

@media (max-width: 1024px) {
    .payment-content {
        grid-template-columns: 1fr;
        padding: 24px;
    }
    
    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .payment-header {
        padding: 24px;
    }
    
    .header-content h1 {
        font-size: 24px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

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
    
    calculateBalance();
}

function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value;
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
            balanceDisplay.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            document.getElementById('displayBalance').style.color = '#10b981';
        } else {
            balanceAmount.textContent = 'Rs. ' + Math.abs(balance).toFixed(2);
            balanceDisplay.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            document.getElementById('displayBalance').style.color = '#ef4444';
        }
    } else {
        balanceDisplay.style.display = 'none';
    }
}
</script>
@endsection
