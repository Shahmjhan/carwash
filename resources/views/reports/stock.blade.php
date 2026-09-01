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
        <h1>Stock Report</h1>
        <p>Current inventory levels, costs, and retail values.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('reports') }}" style="background:#6b7280;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:500;">← Back</a>
        <button onclick="window.print()" style="background:#3b82f6;color:white;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-weight:500;">📄 Print PDF</button>
        <button onclick="printThermal()" style="background:#f59e0b;color:white;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-weight:500;">🖨️ Thermal Print</button>
    </div>
</div>

<div id="report-content" class="panel">
    <div style="text-align:center;margin-bottom:30px;">
        <h1 style="margin:0 0 10px 0;">Stock Report</h1>
        <p style="margin:0;color:#6b7280;">Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:30px;">
        <div style="background:#eff6ff;padding:20px;border-radius:8px;border:1px solid #bfdbfe;">
            <small style="color:#1e40af;">Total Stock Value (Cost)</small>
            <h2 style="margin:5px 0 0 0;color:#1e40af;">Rs. {{ number_format($totalStockValue, 2) }}</h2>
        </div>
        <div style="background:#ecfdf5;padding:20px;border-radius:8px;border:1px solid #a7f3d0;">
            <small style="color:#065f46;">Total Retail Value</small>
            <h2 style="margin:5px 0 0 0;color:#065f46;">Rs. {{ number_format($totalRetailValue, 2) }}</h2>
        </div>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">SKU</th>
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Product Name</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Quantity</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Cost Price</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Selling Price</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Total Cost</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stock as $item)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:12px;">{{ $item->sku ?? '-' }}</td>
                <td style="padding:12px;">{{ $item->name }}</td>
                <td style="padding:12px;text-align:right;">{{ $item->quantity }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($item->cost_price, 2) }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($item->selling_price, 2) }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($item->total_cost, 2) }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($item->total_value, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function printThermal() {
    const content = `
STOCK REPORT
================================
Total Stock Value: Rs. {{ number_format($totalStockValue, 2) }}
Total Retail Value: Rs. {{ number_format($totalRetailValue, 2) }}
================================
@foreach($stock as $item)
{{ $item->name }} | Qty: {{ $item->quantity }} | Rs.{{ number_format($item->total_cost, 2) }}
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
