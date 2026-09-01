@extends('layouts.app')
@section('content')
@php
    $business = auth()->user()->business;
    $settings = $business ? $business->getBillingSettings() : [
        'a4_enabled' => true,
        'thermal_enabled' => true
    ];
@endphp
<div class="page-head">
    <div>
        <h1>{{ $invoice->invoice_number }}</h1>
        <p>{{ $invoice->customer->full_name }} · {{ $invoice->job->vehicle->registration_number }}</p>
    </div>
    <div>
        @if($settings['a4_enabled'])
            <a href="{{ route('invoices.print',$invoice) }}" target="_blank" class="secondary">Print A4</a>
        @endif
        @if($settings['thermal_enabled'])
            <a href="{{ route('invoices.print',['invoice'=>$invoice,'format'=>'thermal']) }}" target="_blank" class="secondary">Print Receipt</a>
        @endif
    </div>
</div>

<div style="max-width:900px;margin:0 auto;">
    <div class="panel" style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:40px;box-shadow:0 4px 6px rgba(0,0,0,0.05);">
        <!-- Invoice Header -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:40px;padding-bottom:30px;border-bottom:2px solid #e5e7eb;">
            <div>
                @if(!empty($settings['logo_path']))
                    @php
                        $logoPath = $settings['logo_path'];
                        if(!str_starts_with($logoPath, 'http')) {
                            if(str_starts_with($logoPath, '/storage/')) {
                                $logoPath = asset($logoPath);
                            } elseif(str_starts_with($logoPath, 'storage/')) {
                                $logoPath = asset('/' . $logoPath);
                            } else {
                                $logoPath = asset('storage/' . $logoPath);
                            }
                        }
                    @endphp
                    <img src="{{ $logoPath }}" alt="Logo" style="max-height:80px;margin-bottom:15px;" onerror="this.style.display='none';">
                @else
                    <h2 style="margin:0 0 10px 0;color:#1a1a2e;font-size:28px;font-weight:800;">{{ $settings['company_name'] ?? 'AutoCare Pro' }}</h2>
                @endif
                <p style="margin:5px 0;color:#667085;font-size:14px;">{{ $settings['address'] ?? '' }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;">{{ $settings['phone'] ?? '' }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;">{{ $settings['email'] ?? '' }}</p>
            </div>
            <div style="text-align:right;">
                <h3 style="margin:0 0 10px 0;color:#1a1a2e;font-size:24px;font-weight:700;">INVOICE</h3>
                <p style="margin:5px 0;color:#667085;font-size:14px;"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;"><strong>Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;"><strong>Due Date:</strong> {{ $invoice->created_at->addDays(7)->format('d M Y') }}</p>
                <div style="margin-top:15px;padding:8px 16px;background:#fee2e2;border-radius:6px;display:inline-block;">
                    <span style="color:#dc2626;font-weight:600;font-size:13px;">{{ $invoice->balance > 0 ? 'UNPAID' : 'PAID' }}</span>
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div style="display:flex;justify-content:space-between;margin-bottom:30px;">
            <div style="flex:1;">
                <h4 style="margin:0 0 15px 0;color:#1a1a2e;font-size:16px;font-weight:600;">Bill To:</h4>
                <p style="margin:5px 0;color:#374151;font-size:14px;font-weight:600;">{{ $invoice->customer->full_name }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;">{{ $invoice->customer->phone ?? '' }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;">{{ $invoice->customer->email ?? '' }}</p>
            </div>
            <div style="flex:1;text-align:right;">
                <h4 style="margin:0 0 15px 0;color:#1a1a2e;font-size:16px;font-weight:600;">Vehicle Details:</h4>
                <p style="margin:5px 0;color:#374151;font-size:14px;font-weight:600;">{{ $invoice->job->vehicle->registration_number }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;">{{ $invoice->job->vehicle->make }} {{ $invoice->job->vehicle->model }}</p>
                <p style="margin:5px 0;color:#667085;font-size:14px;">Job #{{ $invoice->job->job_number }}</p>
            </div>
        </div>

        <!-- Invoice Items -->
        <div style="margin-bottom:30px;">
            <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e5e7eb;">
                        <th style="padding:15px;text-align:left;color:#1a1a2e;font-weight:600;font-size:14px;border-bottom:2px solid #e5e7eb;">Description</th>
                        <th style="padding:15px;text-align:center;color:#1a1a2e;font-weight:600;font-size:14px;border-bottom:2px solid #e5e7eb;width:80px;">Qty</th>
                        <th style="padding:15px;text-align:right;color:#1a1a2e;font-weight:600;font-size:14px;border-bottom:2px solid #e5e7eb;width:120px;">Price</th>
                        <th style="padding:15px;text-align:right;color:#1a1a2e;font-weight:600;font-size:14px;border-bottom:2px solid #e5e7eb;width:120px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:15px;color:#374151;font-size:14px;">{{ $item->description }}</td>
                        <td style="padding:15px;text-align:center;color:#374151;font-size:14px;">{{ $item->quantity }}</td>
                        <td style="padding:15px;text-align:right;color:#374151;font-size:14px;">Rs. {{ number_format($item->unit_price,2) }}</td>
                        <td style="padding:15px;text-align:right;color:#374151;font-size:14px;font-weight:600;">Rs. {{ number_format($item->line_total,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div style="display:flex;justify-content:flex-end;margin-bottom:30px;">
            <div style="width:300px;">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#667085;font-size:14px;">Subtotal</span>
                    <span style="color:#374151;font-size:14px;font-weight:600;">Rs. {{ number_format($invoice->subtotal ?? $invoice->total,2) }}</span>
                </div>
                @if($invoice->tax ?? 0)
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#667085;font-size:14px;">Tax</span>
                    <span style="color:#374151;font-size:14px;font-weight:600;">Rs. {{ number_format($invoice->tax,2) }}</span>
                </div>
                @endif
                @if($invoice->discount ?? 0)
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="color:#667085;font-size:14px;">Discount</span>
                    <span style="color:#10b981;font-size:14px;font-weight:600;">-Rs. {{ number_format($invoice->discount,2) }}</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;padding:15px 0;background:#f8fafc;border-radius:8px;margin-top:10px;padding:15px;">
                    <span style="color:#1a1a2e;font-size:16px;font-weight:700;">Total</span>
                    <span style="color:#2563eb;font-size:18px;font-weight:800;">Rs. {{ number_format($invoice->total,2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;">
                    <span style="color:#667085;font-size:14px;">Paid</span>
                    <span style="color:#10b981;font-size:14px;font-weight:600;">Rs. {{ number_format($invoice->paid,2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-top:2px solid #e5e7eb;">
                    <span style="color:#1a1a2e;font-size:16px;font-weight:700;">Balance Due</span>
                    <span style="color:#dc2626;font-size:18px;font-weight:800;">Rs. {{ number_format($invoice->balance,2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div style="background:#f8fafc;border-radius:12px;padding:25px;margin-top:30px;">
            <h3 style="margin:0 0 20px 0;color:#1a1a2e;font-size:18px;font-weight:700;">Record Payment</h3>
            @if($invoice->balance>0)
                <form method="post" action="{{ route('invoices.pay',$invoice) }}" style="display:flex;gap:15px;align-items:flex-end;flex-wrap:wrap;">
                    @csrf
                    <div style="flex:1;min-width:200px;">
                        <label style="display:block;margin-bottom:8px;color:#374151;font-size:14px;font-weight:600;">Amount</label>
                        <input name="amount" type="number" step=".01" max="{{ $invoice->balance }}" value="{{ $invoice->balance }}" required style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;">
                    </div>
                    <div style="flex:1;min-width:200px;">
                        <label style="display:block;margin-bottom:8px;color:#374151;font-size:14px;font-weight:600;">Payment Method</label>
                        <select name="method" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="primary" style="padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;">Receive Payment</button>
                </form>
            @else
                <div style="display:flex;align-items:center;gap:10px;padding:15px;background:#dcfce7;border-radius:8px;border:1px solid #86efac;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span style="color:#16a34a;font-weight:600;font-size:14px;">Paid in full</span>
                </div>
            @endif
        </div>

        <!-- Payment History -->
        @if($invoice->payments->count() > 0)
        <div style="margin-top:30px;">
            <h3 style="margin:0 0 15px 0;color:#1a1a2e;font-size:18px;font-weight:700;">Payment History</h3>
            @foreach($invoice->payments as $p)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:15px;background:white;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:15px;">
                    <div style="width:40px;height:40px;background:#dbeafe;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                    <div>
                        <p style="margin:0;color:#374151;font-size:14px;font-weight:600;">{{ ucfirst(str_replace('_',' ',$p->method)) }}</p>
                        <p style="margin:5px 0 0 0;color:#667085;font-size:12px;">{{ $p->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                <span style="color:#10b981;font-size:16px;font-weight:700;">Rs. {{ number_format($p->amount,2) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
