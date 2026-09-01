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
        <h1>Services Report</h1>
        <p>Service usage statistics and revenue by service type.</p>
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
        <h1 style="margin:0 0 10px 0;">Services Report</h1>
        <p style="margin:0;color:#6b7280;">Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Service Name</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Count</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Revenue</th>
                <th style="padding:12px;text-align:right;border-bottom:2px solid #e5e7eb;">Average Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceStats as $serviceName => $stats)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:12px;">{{ $serviceName }}</td>
                <td style="padding:12px;text-align:right;">{{ $stats['count'] }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($stats['revenue'], 2) }}</td>
                <td style="padding:12px;text-align:right;">Rs. {{ number_format($stats['revenue'] / $stats['count'], 2) }}</td>
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
SERVICES REPORT
Date: {{ $startDate }} to {{ $endDate }}
================================
@foreach($serviceStats as $serviceName => $stats)
{{ $serviceName }} | {{ $stats['count'] }} | Rs.{{ number_format($stats['revenue'], 2) }}
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
