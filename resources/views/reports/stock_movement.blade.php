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
        <h1>Stock Movement Report</h1>
        <p>Inventory additions, adjustments, and consumption history.</p>
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
        <h1 style="margin:0 0 10px 0;">Stock Movement Report</h1>
        <p style="margin:0;color:#6b7280;">Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Date</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Product</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">SKU</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Type</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Quantity</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:12px;">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                <td style="padding:12px;">{{ $movement->name }}</td>
                <td style="padding:12px;">{{ $movement->sku ?? '-' }}</td>
                <td style="padding:12px;">
                    @if($movement->type == 'add')
                    <span style="color:#10b981;font-weight:500;">Addition</span>
                    @elseif($movement->type == 'consume')
                    <span style="color:#ef4444;font-weight:500;">Consumption</span>
                    @elseif($movement->type == 'adjust')
                    <span style="color:#f59e0b;font-weight:500;">Adjustment</span>
                    @else
                    {{ $movement->type }}
                    @endif
                </td>
                <td style="padding:12px;text-align:right;">{{ $movement->quantity }}</td>
                <td style="padding:12px;">{{ $movement->reference ?? '-' }}</td>
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
STOCK MOVEMENT REPORT
Date: {{ $startDate }} to {{ $endDate }}
================================
@foreach($movements as $movement)
{{ $movement->created_at->format('Y-m-d') }} | {{ $movement->name }} | {{ $movement->type }} | {{ $movement->quantity }}
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
