@extends('layouts.app')
@section('content')
<div class="panel">
    <h2>Available Reports</h2>
    <p>Select a report type to generate with date range filtering and export options.</p>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:25px;margin-top:30px;">
        <div style="background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);border-radius:16px;padding:30px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 6px rgba(59,130,246,0.2);" onclick="window.location.href='{{ route('reports.sales') }}'" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="background:rgba(255,255,255,0.2);border-radius:12px;padding:20px;margin-bottom:20px;display:flex;align-items:center;justify-content:center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3 style="margin:0 0 10px 0;color:white;font-size:20px;text-align:center;">Sales Report</h3>
            <p style="margin:0;color:rgba(255,255,255,0.9);font-size:14px;line-height:1.5;text-align:center;">Revenue, payments, and outstanding balances by date range.</p>
        </div>
        
        <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:16px;padding:30px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 6px rgba(16,185,129,0.2);" onclick="window.location.href='{{ route('reports.stock') }}'" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="background:rgba(255,255,255,0.2);border-radius:12px;padding:20px;margin-bottom:20px;display:flex;align-items:center;justify-content:center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <h3 style="margin:0 0 10px 0;color:white;font-size:20px;text-align:center;">Stock Report</h3>
            <p style="margin:0;color:rgba(255,255,255,0.9);font-size:14px;line-height:1.5;text-align:center;">Current inventory levels, costs, and retail values.</p>
        </div>
        
        <div style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);border-radius:16px;padding:30px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 6px rgba(245,158,11,0.2);" onclick="window.location.href='{{ route('reports.stock-movement') }}'" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="background:rgba(255,255,255,0.2);border-radius:12px;padding:20px;margin-bottom:20px;display:flex;align-items:center;justify-content:center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            </div>
            <h3 style="margin:0 0 10px 0;color:white;font-size:20px;text-align:center;">Stock Movement</h3>
            <p style="margin:0;color:rgba(255,255,255,0.9);font-size:14px;line-height:1.5;text-align:center;">Inventory additions, adjustments, and consumption history.</p>
        </div>
        
        <div style="background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);border-radius:16px;padding:30px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 6px rgba(139,92,246,0.2);" onclick="window.location.href='{{ route('reports.services') }}'" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="background:rgba(255,255,255,0.2);border-radius:12px;padding:20px;margin-bottom:20px;display:flex;align-items:center;justify-content:center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </div>
            <h3 style="margin:0 0 10px 0;color:white;font-size:20px;text-align:center;">Services Report</h3>
            <p style="margin:0;color:rgba(255,255,255,0.9);font-size:14px;line-height:1.5;text-align:center;">Service usage statistics and revenue by service type.</p>
        </div>
        
        <div style="background:linear-gradient(135deg,#ec4899 0%,#db2777 100%);border-radius:16px;padding:30px;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 6px rgba(236,72,153,0.2);" onclick="window.location.href='{{ route('reports.customers') }}'" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="background:rgba(255,255,255,0.2);border-radius:12px;padding:20px;margin-bottom:20px;display:flex;align-items:center;justify-content:center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3 style="margin:0 0 10px 0;color:white;font-size:20px;text-align:center;">Customer Report</h3>
            <p style="margin:0;color:rgba(255,255,255,0.9);font-size:14px;line-height:1.5;text-align:center;">Customer activity and job history by date range.</p>
        </div>
    </div>
</div>
@endsection
