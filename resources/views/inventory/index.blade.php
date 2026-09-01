@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Item Master</h1>
        <p>Stock, valuation and traceable movements.</p>
    </div>
    <a class="primary" href="{{ route('inventory.create') }}">+ New Product</a>
</div>

@if($lowStockItems->count() > 0)
<div class="alert alert-danger" style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div>
            <strong style="color:#dc2626;">Low Stock Alert: {{ $lowStockItems->count() }} product(s) need attention</strong>
            <p style="margin:4px 0 0 0;color:#991b1b;font-size:14px;">
                @foreach($lowStockItems->take(3) as $item)
                    {{ $item->product->name }} ({{ $item->quantity }} / {{ $item->product->minimum_stock }}){{ !$loop->last ? ', ' : '' }}
                @endforeach
                @if($lowStockItems->count() > 3)
                    and {{ $lowStockItems->count() - 3 }} more...
                @endif
            </p>
        </div>
    </div>
</div>
@endif

<div class="panel">
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>SKU</th>
                <th>Product</th>
                <th>Brand</th>
                <th>Stock</th>
                <th>Min</th>
                <th>Sell Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i)
            <tr>
                <td>
                    @if($i->product->image)
                        <img src="{{ asset('storage/'.$i->product->image) }}" alt="{{ $i->product->name }}" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                    @else
                        <span style="color:#9ca3af;">No image</span>
                    @endif
                </td>
                <td>{{ $i->product->sku }}</td>
                <td><b>{{ $i->product->name }}</b></td>
                <td>{{ $i->product->brand }}</td>
                <td>
                    @if($i->quantity == 0)
                        <span style="color:#dc2626;font-weight:bold;">Out of Stock</span>
                    @elseif($i->quantity <= $i->product->minimum_stock)
                        <span style="color:#dc2626;font-weight:bold;">{{ $i->quantity }}</span>
                    @else
                        {{ $i->quantity }}
                    @endif
                </td>
                <td>{{ $i->product->minimum_stock }}</td>
                <td>Rs. {{ number_format($i->product->selling_price,2) }}</td>
                <td>
                    <form class="inline-form" method="post" action="{{ route('inventory.adjust',$i->product) }}">
                        @csrf
                        <input name="quantity" type="number" step=".001" placeholder="+/- qty">
                        <input name="reason" placeholder="Reason">
                        <button>Adjust</button>
                    </form>
                    <a href="{{ route('inventory.edit',$i->product) }}" class="secondary">Edit</a>
                    <button class="danger" style="padding:8px 16px;border-radius:6px;border:none;background:#ef4444;color:white;cursor:pointer;font-size:14px;" onclick="showDeleteModal('{{ $i->product->id }}')">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>

<div id="deleteModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:1000;">
    <div class="modal-content" style="background:white;border-radius:12px;width:90%;max-width:400px;padding:24px;">
        <div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;font-size:18px;">Confirm Delete</h3>
            <button class="modal-close" onclick="closeDeleteModal()" style="background:none;border:none;font-size:24px;cursor:pointer;">✕</button>
        </div>
        <div class="modal-body" style="margin-bottom:20px;">
            <p style="margin:0;color:#374151;">Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:12px;">
            <button type="button" onclick="closeDeleteModal()" style="padding:8px 16px;border-radius:6px;border:1px solid #d1d5db;background:white;color:#374151;cursor:pointer;font-size:14px;">No</button>
            <button type="button" onclick="confirmDelete()" style="padding:8px 16px;border-radius:6px;border:none;background:#ef4444;color:white;cursor:pointer;font-size:14px;">Yes</button>
        </div>
    </div>
</div>

<form id="deleteForm" method="post" action="" style="display:none">@method('DELETE')@csrf</form>

<script>
let deleteProductId=null;
function showDeleteModal(id){
    deleteProductId=id;
    document.getElementById('deleteModal').style.display='flex'
}
function closeDeleteModal(){
    deleteProductId=null;
    document.getElementById('deleteModal').style.display='none'
}
function confirmDelete(){
    if(deleteProductId){
        const form=document.getElementById('deleteForm');
        form.action='{{ route('inventory.destroy', ':id') }}'.replace(':id',deleteProductId);
        form.submit()
    }
    closeDeleteModal()
}
</script>
@endsection
