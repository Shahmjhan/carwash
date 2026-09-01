@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>{{ $job->job_number }}</h1>
        <p>{{ $job->vehicle->registration_number }} · {{ $job->customer->full_name }}</p>
    </div>
    <div><span class="badge large">{{ $job->status->getLabel() }}</span></div>
</div>
<div class="stepper">
    @foreach(['checked_in','inspection_completed','customer_approval_pending','approved','waiting_for_parts','in_service','quality_check','completed','ready_for_payment','paid','delivered'] as $s)
        <span class="{{ $job->status->value===$s?'current':'' }}">{{ str_replace('_',' ',ucfirst($s)) }}</span>
    @endforeach
</div>
<div class="grid2">
    <section class="panel">
        <h2>Vehicle & customer</h2>
        <div class="details">
            <span>Customer</span><b><a href="{{ route('customers.show',$job->customer) }}">{{ $job->customer->full_name }}</a></b>
            <span>Vehicle</span><b><a href="{{ route('vehicles.show',$job->vehicle) }}">{{ $job->vehicle->registration_number }}</a></b>
            <span>Complaint</span><b>{{ $job->customer_complaint ?: '—' }}</b>
            <span>Priority</span><b>{{ ucfirst($job->priority) }}</b>
        </div>
    </section>
    <section class="panel">
        <h2>Actions</h2>
        <div class="actions">
            <a href="{{ route('jobs.inspection.edit',$job) }}">Digital Inspection</a>
            <form method="post" action="{{ route('jobs.status',$job) }}">
                @csrf
                <label>Change Status
                    <select name="status" required>
                        <option value="" disabled selected>{{ $job->status->getLabel() }}</option>
                        @foreach(\App\Enums\JobStatus::cases() as $status)
                            @if($job->status->canTransitionTo($status))
                                <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                            @endif
                        @endforeach
                    </select>
                </label>
                <button>Update Status</button>
            </form>
            @if($job->invoice)
                <a href="{{ route('invoices.show',$job->invoice) }}">Open Invoice</a>
            @endif
        </div>
    </section>
</div>
<div class="grid2">
    <section class="panel">
        <h2>Services</h2>
        @forelse($job->services as $s)
            <div class="listrow">
                <b>{{ $s->name_snapshot }}</b>
                <span>Rs. {{ number_format($s->unit_price,2) }} · {{ $s->approval_status }}</span>
            </div>
        @empty
            <p class="empty">No services.</p>
        @endforelse
        <h3>Add additional work</h3>
        <form class="inline-form" method="post" action="{{ route('jobs.additional-work',$job) }}">
            @csrf
            <select name="service_id">
                @foreach($services as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} — Rs. {{ number_format($s->base_price,2) }}</option>
                @endforeach
            </select>
            <button>Request</button>
        </form>
    </section>
    <section class="panel">
        <h2>Parts used</h2>
        @forelse($job->parts as $p)
            <div class="listrow">
                <b>{{ $p->product->name }}</b>
                <span>{{ $p->quantity }} × Rs. {{ number_format($p->unit_price,2) }}</span>
            </div>
        @empty
            <p class="empty">No parts consumed.</p>
        @endforelse
        <h3>Consume inventory part</h3>
        <form class="inline-form" method="post" action="{{ route('jobs.consume-part',$job) }}">
            @csrf
            <select name="product_id" id="productSelect" onchange="updateSellingPrice(); updateStockInfo()">
                <option value="">Select Product</option>
                @foreach($products as $p)
                    @php
                        $inventory = \App\Models\Inventory::where('product_id', $p->id)->where('branch_id', $job->branch_id)->first();
                        $availableStock = $inventory ? $inventory->quantity : 0;
                    @endphp
                    <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-stock="{{ $availableStock }}">{{ $p->name }} — Rs. {{ number_format($p->selling_price,2) }} (Stock: {{ $availableStock }})</option>
                @endforeach
            </select>
            <input name="quantity" type="number" step=".001" value="1" min="0.001">
            <input name="unit_price" type="number" step=".01" placeholder="Selling price" id="unitPriceInput">
            <button>Consume</button>
        </form>
        <div id="stockWarning" style="margin-top:10px;display:none;padding:8px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:13px;"></div>
    </section>
</div>

<script>
function updateSellingPrice(){
    const select=document.getElementById('productSelect');
    const selectedOption=select.options[select.selectedIndex];
    const priceInput=document.getElementById('unitPriceInput');
    if(selectedOption.value){
        priceInput.value=selectedOption.getAttribute('data-price');
    }else{
        priceInput.value='';
    }
}

function updateStockInfo(){
    const select=document.getElementById('productSelect');
    const selectedOption=select.options[select.selectedIndex];
    const stockWarning=document.getElementById('stockWarning');
    const quantityInput=document.querySelector('input[name="quantity"]');
    
    if(selectedOption.value){
        const availableStock=parseFloat(selectedOption.getAttribute('data-stock'));
        const requestedQty=parseFloat(quantityInput.value)||1;
        
        if(availableStock===0){
            stockWarning.textContent='⚠️ This product is out of stock!';
            stockWarning.style.display='block';
        }else if(availableStock<requestedQty){
            stockWarning.textContent='⚠️ Insufficient stock. Available: '+availableStock+', Requested: '+requestedQty;
            stockWarning.style.display='block';
        }else{
            stockWarning.style.display='none';
        }
    }else{
        stockWarning.style.display='none';
    }
}

document.querySelector('input[name="quantity"]').addEventListener('input',updateStockInfo);
</script>
@endsection
