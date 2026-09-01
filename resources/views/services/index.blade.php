@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Services</h1>
        <p>Manage service types and pricing</p>
    </div>
    <a class="primary" href="{{ route('services.create') }}">+ New Service</a>
</div>

<div class="panel">
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
            <tr>
                <td>
                    <strong>{{ $service->name }}</strong>
                </td>
                <td>{{ $service->category ? $service->category->name : '-' }}</td>
                <td>Rs. {{ number_format($service->base_price, 2) }}</td>
                <td>
                    <label style="position:relative;display:inline-block;width:44px;height:24px;">
                        <input type="checkbox" {{ $service->active ? 'checked' : '' }} onchange="toggleServiceStatus({{ $service->id }}, this)" style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:{{ $service->active ? '#10b981' : '#ccc' }};transition:.4s;border-radius:24px;"></span>
                        <span style="position:absolute;content:'';height:18px;width:18px;left:3px;bottom:3px;background-color:white;transition:.4s;border-radius:50%;{{ $service->active ? 'transform:translateX(20px);' : '' }}"></span>
                    </label>
                </td>
                <td>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <a href="{{ route('services.edit', $service) }}" style="background:#3b82f6;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:500;transition:all 0.2s;">Edit</a>
                        <form method="post" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Are you sure you want to delete this service?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:#ef4444;color:white;padding:6px 12px;border-radius:6px;border:none;font-size:12px;font-weight:500;cursor:pointer;transition:all 0.2s;">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function toggleServiceStatus(serviceId, checkbox) {
    fetch(`/services/${serviceId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            active: checkbox.checked
        })
    })
    .then(response => response.json())
    .then(data => {
        const span = checkbox.nextElementSibling.nextElementSibling;
        if (checkbox.checked) {
            span.style.transform = 'translateX(20px)';
            checkbox.nextElementSibling.style.backgroundColor = '#10b981';
        } else {
            span.style.transform = 'translateX(0)';
            checkbox.nextElementSibling.style.backgroundColor = '#ccc';
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endsection
