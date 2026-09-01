@extends('layouts.app')
@section('content')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #report-content, #report-content * {
        visibility: visible;
    }
    #report-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="page-head no-print">
    <div>
        <h1>Sales Report</h1>
        <p>Revenue, payments, and outstanding balances.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('reports') }}" style="background:#6b7280;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:500;">← Back</a>
        <button onclick="window.print()" style="background:#3b82f6;color:white;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-weight:500;">📄 Print PDF</button>
        <button onclick="printThermal()" style="background:#f59e0b;color:white;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-weight:500;">🖨️ Thermal Print</button>
    </div>
</div>

<div id="report-content" class="panel">
    <div class="no-print">
        <form method="get" style="display:flex;gap:10px;align-items:center;margin-bottom:20px;">
            <div>
                <label style="display:block;margin-bottom:5px;">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" style="padding:10px;border:1px solid #e5e7eb;border-radius:8px;">
            </div>
            <div>
                <label style="display:block;margin-bottom:5px;">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" style="padding:10px;border:1px solid #e5e7eb;border-radius:8px;">
            </div>
            <div style="padding-top:25px;">
                <button type="submit" style="background:#3b82f6;color:white;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-weight:500;">Generate Report</button>
            </div>
        </form>
    </div>

    <div style="text-align:center;margin-bottom:30px;">
        <h1 style="margin:0 0 10px 0;">Sales Report</h1>
        <p style="margin:0;color:#6b7280;">Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:30px;">
        <div style="background:#eff6ff;padding:20px;border-radius:8px;border:1px solid #bfdbfe;">
            <small style="color:#1e40af;">Total Revenue</small>
            <h2 style="margin:5px 0 0 0;color:#1e40af;">Rs. {{ number_format($totalRevenue, 2) }}</h2>
        </div>
        <div style="background:#ecfdf5;padding:20px;border-radius:8px;border:1px solid #a7f3d0;">
            <small style="color:#065f46;">Total Paid</small>
            <h2 style="margin:5px 0 0 0;color:#065f46;">Rs. {{ number_format($totalPaid, 2) }}</h2>
        </div>
        <div style="background:#fef2f2;padding:20px;border-radius:8px;border:1px solid #fecaca;">
            <small style="color:#991b1b;">Outstanding</small>
            <h2 style="margin:5px 0 0 0;color:#991b1b;">Rs. {{ number_format($totalOutstanding, 2) }}</h2>
        </div>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Invoice #</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Date</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Customer</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Vehicle</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Total</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Paid</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:12px;">#{{ $sale->id }}</td>
                <td style="padding:12px;">{{ $sale->created_at->format('Y-m-d') }}</td>
                <td style="padding:12px;">{{ $sale->customer?->name ?? $sale->job?->customer?->name ?? '-' }}</td>
                <td style="padding:12px;">{{ $sale->job?->vehicle?->plate_number ?? '-' }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($sale->total, 2) }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($sale->paid, 2) }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($sale->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:30px;text-align:center;color:#6b7280;font-size:12px;">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</div>

<script>
function printThermal() {
    const content = `
SALES REPORT
Date: {{ $startDate }} to {{ $endDate }}
================================
Total Revenue: Rs. {{ number_format($totalRevenue, 2) }}
Total Paid: Rs. {{ number_format($totalPaid, 2) }}
Outstanding: Rs. {{ number_format($totalOutstanding, 2) }}
================================
@foreach($sales as $sale)
#{{ $sale->id }} | {{ $sale->job?->customer?->name ?? '-' }} | Rs.{{ number_format($sale->total, 2) }}
@endforeach
================================
Generated: {{ now()->format('Y-m-d H:i') }}
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<pre style="font-family: monospace; font-size: 12px;">' + content + '</pre>');
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection
