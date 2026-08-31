@extends('layouts.app') @section('content')<div class="page-head"><h1>New Appointment</h1></div><div class="panel form-panel"><form method="post" action="{{ route('appointments.store') }}">@csrf<div class="form-grid"><label>Customer<select name="customer_id" id="customerSelect" onchange="filterVehicles()"><option value="">Select Customer</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->full_name }} — {{ $c->phone }}</option>@endforeach</select></label><label>Vehicle<select name="vehicle_id" id="vehicleSelect"><option value="">Select Customer First</option></select></label><label>Date & time<input type="datetime-local" name="scheduled_at" required></label><label class="wide">Notes<textarea name="notes"></textarea></label></div><button class="primary">Create Appointment</button></form></div>

<script>
const vehiclesData = @json($vehicles);
function filterVehicles(){
const customerId=document.getElementById('customerSelect').value;
const vehicleSelect=document.getElementById('vehicleSelect');
vehicleSelect.innerHTML='<option value="">Select Vehicle</option>';
if(customerId){
const customerVehicles=vehiclesData.filter(v=>v.customer_id==customerId);
customerVehicles.forEach(v=>{
const option=document.createElement('option');
option.value=v.id;
option.textContent=v.registration_number+' — '+v.make+' '+v.model;
vehicleSelect.appendChild(option);
});
}
}
</script>@endsection
