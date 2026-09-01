@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Create Service</h1>
        <p>Add a new service type</p>
    </div>
    <a href="{{ route('services.index') }}" style="background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:500;transition:all 0.2s;">← Back to Services</a>
</div>

<div class="panel">
    <form method="post" action="{{ route('services.store') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="form-group">
                <label>Service Name *</label>
                <input type="text" name="name" required autofocus style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Category</label>
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <select name="service_category_id" id="categorySelect" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="openCategoryModal()" style="background:#10b981;color:white;padding:12px 16px;border-radius:8px;border:none;cursor:pointer;font-weight:500;white-space:nowrap;">+ Add Category</button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
            <div class="form-group">
                <label>Base Price (Rs.) *</label>
                <input type="number" name="base_price" step="0.01" min="0" required style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Labor Cost (Rs.)</label>
                <input type="number" name="labor_cost" step="0.01" min="0" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Tax Rate (%)</label>
                <input type="number" name="tax_rate" step="0.01" min="0" max="100" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="form-group">
                <label>Duration (minutes)</label>
                <input type="number" name="duration_minutes" min="1" style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Status</label>
                <div style="display:flex;align-items:center;gap:10px;padding:12px 0;">
                    <label style="position:relative;display:inline-block;width:44px;height:24px;">
                        <input type="checkbox" name="active" checked style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#10b981;transition:.4s;border-radius:24px;"></span>
                        <span style="position:absolute;content:'';height:18px;width:18px;left:3px;bottom:3px;background-color:white;transition:.4s;border-radius:50%;transform:translateX(20px);"></span>
                    </label>
                    <span style="font-size:14px;color:#6b7280;">Active</span>
                </div>
            </div>
        </div>
        <div style="margin-top:30px;">
            <button type="submit" class="primary" style="background:#3b82f6;padding:12px 24px;border-radius:8px;border:none;color:white;font-weight:500;cursor:pointer;transition:all 0.2s;">Create Service</button>
        </div>
    </form>
</div>

<div id="categoryModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;padding:30px;border-radius:12px;width:400px;max-width:90%;">
        <h3 style="margin:0 0 20px 0;">Add New Category</h3>
        <form id="categoryForm">
            @csrf
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" id="categoryName" name="name" required style="padding:12px;border:1px solid #e5e7eb;border-radius:8px;width:100%;">
            </div>
            <div class="form-group">
                <label>Status</label>
                <div style="display:flex;align-items:center;gap:10px;padding:12px 0;">
                    <label style="position:relative;display:inline-block;width:44px;height:24px;">
                        <input type="checkbox" id="categoryActive" name="active" checked style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#10b981;transition:.4s;border-radius:24px;"></span>
                        <span style="position:absolute;content:'';height:18px;width:18px;left:3px;bottom:3px;background-color:white;transition:.4s;border-radius:50%;transform:translateX(20px);"></span>
                    </label>
                    <span style="font-size:14px;color:#6b7280;">Active</span>
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeCategoryModal()" style="background:#6b7280;color:white;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#10b981;color:white;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;">Add Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCategoryModal() {
    const modal = document.getElementById('categoryModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    if (modal) {
        modal.style.display = 'none';
    }
    if (form) {
        form.reset();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const categoryForm = document.getElementById('categoryForm');
    if (categoryForm) {
        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const categoryName = document.getElementById('categoryName').value;
            const categoryActive = document.getElementById('categoryActive').checked;
            
            console.log('Submitting category:', categoryName, 'Active:', categoryActive);
            console.log('Route:', '{{ route('service-categories.store') }}');
            
            fetch('{{ route('service-categories.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: categoryName,
                    active: categoryActive
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    const select = document.getElementById('categorySelect');
                    const option = document.createElement('option');
                    option.value = data.category.id;
                    option.textContent = data.category.name;
                    option.selected = true;
                    select.appendChild(option);
                    closeCategoryModal();
                } else {
                    console.error('Error creating category:', data.error);
                    alert('Error creating category: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error creating category. Please try again.');
            });
        });
    } else {
        console.error('Category form not found');
    }
});
</script>
@endsection
