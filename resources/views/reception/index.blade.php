@extends('layouts.reception')

@section('content')
@php
    $business = auth()->user()->business;
    $settings = $business ? $business->getBillingSettings() : [];
    $bgType = $settings['background_type'] ?? 'image';
    $bgImage = $settings['reception_background_image'] ?? '';
    $bgColor = $settings['background_color'] ?? '';
    $customBgColor = $settings['custom_background_color'] ?? '';
    
    $bgStyle = '';
    if ($bgType === 'color' && $customBgColor) {
        $bgStyle = "background: {$customBgColor};";
    } elseif ($bgType === 'color' && $bgColor) {
        $bgStyle = "background: {$bgColor};";
    } elseif ($bgImage) {
        $bgStyle = "background-image: url('{{ asset($bgImage) }}');";
    } else {
        $bgStyle = "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";
    }
@endphp

<div class="reception-container" style="{{ $bgStyle }}">
    <div id="toastContainer"></div>
    
    <!-- Navigation Menu -->
    <div class="reception-nav">
        <button class="nav-toggle" onclick="toggleNav()">☰</button>
        <div class="nav-menu" id="navMenu">
            <a href="{{ route('dashboard') }}" @if(!auth()->user()->hasPermission('view_dashboard')) style="display:none" @endif>Dashboard</a>
            <a href="{{ route('jobs.index') }}" @if(!auth()->user()->hasPermission('view_jobs')) style="display:none" @endif>Job Cards</a>
            <a href="{{ route('customers.index') }}" @if(!auth()->user()->hasPermission('view_customers')) style="display:none" @endif>Customers</a>
            <a href="{{ route('vehicles.index') }}" @if(!auth()->user()->hasPermission('view_vehicles')) style="display:none" @endif>Vehicles</a>
            <a href="{{ route('appointments.index') }}" @if(!auth()->user()->hasPermission('view_appointments')) style="display:none" @endif>Appointments</a>
            <a href="{{ route('inventory.index') }}" @if(!auth()->user()->hasPermission('view_inventory')) style="display:none" @endif>Inventory</a>
            <a href="{{ route('invoices.index') }}" @if(!auth()->user()->hasPermission('view_invoices')) style="display:none" @endif>Billing</a>
            <a href="{{ route('reports') }}" @if(!auth()->user()->hasPermission('view_reports')) style="display:none" @endif>Reports</a>
            <a href="{{ route('users.index') }}" @if(!auth()->user()->hasPermission('view_users')) style="display:none" @endif>Users</a>
            <a href="{{ route('settings.billing') }}" @if(!auth()->user()->hasPermission('view_settings')) style="display:none" @endif>Settings</a>
            <a href="{{ route('logout') }}" class="logout-link">Sign Out</a>
        </div>
    </div>

    <div class="reception-header">
        <h1 style="color: #ffffff !important;">Vehicle Check-in</h1>
        <p style="color: #ffffff !important;">Enter vehicle registration number to begin</p>
    </div>

    <div class="search-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Vehicle registration number..." autocomplete="off">
        </div>
        <div id="searchResults" class="search-results"></div>
    </div>

    <!-- Vehicle Modal -->
    <div id="vehicleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Vehicle</h3>
                <button class="modal-close" onclick="closeVehicleModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Registration Number *</label>
                    <input type="text" id="modalVehicleRegistration" placeholder="Enter registration number">
                </div>
                <div class="form-group">
                    <label>Make</label>
                    <input type="text" id="modalVehicleMake" placeholder="e.g., Toyota">
                </div>
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" id="modalVehicleModel" placeholder="e.g., Corolla">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="modalVehicleCategory">
                        <option value="Small Car">Small Car</option>
                        <option value="Sedan">Sedan</option>
                        <option value="SUV">SUV</option>
                        <option value="Van">Van</option>
                        <option value="Truck">Truck</option>
                        <option value="Motorcycle">Motorcycle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Customer *</label>
                    <div class="customer-select-wrapper">
                        <select id="modalVehicleCustomer">
                            <option value="">Select existing customer...</option>
                        </select>
                        <button type="button" class="btn-add-customer" onclick="openCustomerModal()">+ New Customer</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="createVehicleFromModal()" class="btn-primary">Create Vehicle</button>
                <button onclick="closeVehicleModal()" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Customer</h3>
                <button class="modal-close" onclick="closeCustomerModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" id="modalCustomerName" placeholder="Enter customer name">
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="text" id="modalCustomerPhone" placeholder="Enter phone number">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="modalCustomerEmail" placeholder="Enter email (optional)">
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="createCustomerFromModal()" class="btn-primary">Create Customer</button>
                <button onclick="closeCustomerModal()" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <div id="jobForm" class="job-form hidden">
        <div class="form-header">
            <h2>Create Job Card</h2>
            <button id="cancelBtn" class="btn-secondary">Cancel</button>
        </div>

        <div class="vehicle-image-section">
            <div id="vehicleImageContainer" class="vehicle-image-container">
                <div class="vehicle-placeholder">
                    <span>Vehicle Image</span>
                </div>
            </div>
        </div>

        <div class="customer-info">
            <h3>Customer Information</h3>
            <div id="customerDetails"></div>
        </div>

        <div class="vehicle-info">
            <h3>Vehicle Information</h3>
            <div id="vehicleDetails"></div>
        </div>

        <div class="services-section">
            <h3>Select Services</h3>
            <div id="servicesList" class="services-grid"></div>
        </div>

        <div class="notes-section">
            <h3>Notes</h3>
            <textarea id="jobNotes" rows="3" placeholder="Additional notes..."></textarea>
        </div>

        <button id="createJobBtn" class="btn-primary">Create Job Card</button>
    </div>
</div>

<script>
let selectedCustomer = null;
let selectedVehicle = null;
let selectedServices = [];
let searchTimeout = null;

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

document.getElementById('searchInput').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    
    if (query.length >= 2) {
        searchTimeout = setTimeout(() => performSearch(query), 300);
    } else {
        document.getElementById('searchResults').innerHTML = '';
    }
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    document.getElementById('jobForm').classList.add('hidden');
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = '';
    selectedCustomer = null;
    selectedVehicle = null;
    selectedServices = [];
});

document.getElementById('createJobBtn').addEventListener('click', createJob);

async function performSearch(query) {
    const response = await fetch('/reception/search', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ q: query })
    });

    const data = await response.json();
    displaySearchResults(data);
}

function displaySearchResults(data) {
    const resultsDiv = document.getElementById('searchResults');
    
    if (!data.found || (data.vehicles && data.vehicles.length === 0)) {
        // Show option to add new vehicle when no results found
        const query = document.getElementById('searchInput').value.trim();
        resultsDiv.innerHTML = `
            <div class="result-card">
                <h4>No vehicle found</h4>
                <p>No vehicle found with registration "${query}"</p>
                <button onclick="openVehicleModal('${encodeURIComponent(query)}')" class="btn-primary">+ Add New Vehicle</button>
            </div>
        `;
        return;
    }

    let html = '';
    data.vehicles.forEach(vehicle => {
        html += `
            <div class="result-card">
                <h4>Vehicle Found</h4>
                <p><strong>Registration:</strong> ${vehicle.registration_number}</p>
                <p><strong>Make/Model:</strong> ${vehicle.make} ${vehicle.model}</p>
                <p><strong>Customer:</strong> ${vehicle.customer_name}</p>
                <button class="btn-primary" onclick='selectVehicleDirect(${JSON.stringify(vehicle)})'>Proceed to Job Creation</button>
            </div>
        `;
    });

    resultsDiv.innerHTML = html;
}

function openVehicleModal(registration = '') {
    const modal = document.getElementById('vehicleModal');
    modal.classList.add('active');
    document.getElementById('modalVehicleRegistration').value = decodeURIComponent(registration);
    loadCustomersForModal();
}

function closeVehicleModal() {
    document.getElementById('vehicleModal').classList.remove('active');
}

function openCustomerModal() {
    const modal = document.getElementById('customerModal');
    modal.classList.add('active');
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.remove('active');
}

async function loadCustomersForModal() {
    try {
        const response = await fetch('/customers/list');
        const customers = await response.json();
        const select = document.getElementById('modalVehicleCustomer');
        select.innerHTML = '<option value="">Select existing customer...</option>';
        
        if (Array.isArray(customers)) {
            customers.forEach(customer => {
                select.innerHTML += `<option value="${customer.id}">${customer.full_name} - ${customer.phone}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading customers:', error);
        showToast('Error loading customers', 'error');
    }
}

async function createVehicleFromModal() {
    const customerId = document.getElementById('modalVehicleCustomer').value;
    const registration = document.getElementById('modalVehicleRegistration').value.trim();
    const make = document.getElementById('modalVehicleMake').value.trim();
    const model = document.getElementById('modalVehicleModel').value.trim();
    const category = document.getElementById('modalVehicleCategory').value;

    if (!registration) {
        showToast('Registration number is required', 'error');
        return;
    }

    if (!customerId) {
        showToast('Please select a customer', 'error');
        return;
    }

    try {
        const response = await fetch('/vehicles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                customer_id: customerId,
                registration_number: registration,
                make: make,
                model: model,
                category: category
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            showToast('Vehicle created successfully!', 'success');
            closeVehicleModal();
            // Ensure vehicle_id is set correctly
            selectedVehicle = {
                vehicle_id: data.id,
                id: data.id,
                registration_number: data.registration_number,
                make: data.make,
                model: data.model,
                customer_id: data.customer_id
            };
            selectedCustomer = { id: customerId, name: '' };
            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('searchInput').value = registration;
            showJobForm();
        } else {
            const errorMsg = data.error || data.message || 'Error creating vehicle';
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

async function createCustomerFromModal() {
    const name = document.getElementById('modalCustomerName').value.trim();
    const phone = document.getElementById('modalCustomerPhone').value.trim();
    const email = document.getElementById('modalCustomerEmail').value.trim();

    if (!name || !phone) {
        showToast('Name and phone are required', 'error');
        return;
    }

    try {
        const response = await fetch('/customers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                full_name: name,
                phone: phone,
                email: email
            })
        });

        const data = await response.json();

        if (response.ok) {
            showToast('Customer created successfully!', 'success');
            closeCustomerModal();
            // Reload customer list and select the new customer
            loadCustomersForModal().then(() => {
                document.getElementById('modalVehicleCustomer').value = data.id;
            });
        } else {
            const errorMsg = data.error || data.message || 'Error creating customer';
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    }
}

function selectVehicleDirect(vehicle) {
    selectedCustomer = { id: vehicle.customer_id, name: vehicle.customer_name };
    selectedVehicle = {
        vehicle_id: vehicle.vehicle_id,
        id: vehicle.vehicle_id,
        registration_number: vehicle.registration_number,
        make: vehicle.make,
        model: vehicle.model,
        customer_id: vehicle.customer_id
    };
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = vehicle.registration_number;
    showJobForm();
}

function selectCustomerDirect(customer) {
    selectedCustomer = { id: customer.customer_id, name: customer.name };
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = customer.name;
    
    if (customer.vehicles && customer.vehicles.length > 0) {
        let vehiclesHtml = customer.vehicles.map(v => 
            `<div class="vehicle-option">
                <button class="vehicle-btn" onclick="selectVehicle(${JSON.stringify(v).replace(/"/g, '&quot;')})">
                    <strong>${v.registration_number}</strong>
                    <span>${v.make} ${v.model}</span>
                </button>
            </div>`
        ).join('');
        
        document.getElementById('searchResults').innerHTML = `
            <div class="result-card">
                <h4>Select Vehicle for ${customer.name}</h4>
                <p>Found ${customer.vehicles.length} vehicle(s)</p>
                <div class="vehicles-list">
                    ${vehiclesHtml}
                </div>
                <a href="/vehicles/create?customer_id=${customer.customer_id}" class="btn-secondary">+ Add New Vehicle</a>
            </div>
        `;
    } else {
        document.getElementById('searchResults').innerHTML = `
            <div class="result-card">
                <p>No vehicles found for this customer.</p>
                <a href="/vehicles/create?customer_id=${customer.customer_id}" class="btn-primary">+ Add New Vehicle</a>
            </div>
        `;
    }
}

function selectVehicle(vehicle) {
    selectedVehicle = vehicle;
    showJobForm();
}

function showJobForm() {
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('jobForm').classList.remove('hidden');
    
    document.getElementById('customerDetails').innerHTML = `
        <p><strong>Name:</strong> ${selectedCustomer.name}</p>
    `;
    
    // Display vehicle image if available
    const vehicleImageContainer = document.getElementById('vehicleImageContainer');
    if (selectedVehicle.image) {
        vehicleImageContainer.innerHTML = `<img src="${selectedVehicle.image}" alt="Vehicle Image">`;
    } else {
        vehicleImageContainer.innerHTML = `
            <div class="vehicle-placeholder">
                <span>Vehicle Image</span>
            </div>
        `;
    }
    
    document.getElementById('vehicleDetails').innerHTML = `
        <p><strong>Registration:</strong> ${selectedVehicle.registration_number}</p>
        <p><strong>Make/Model:</strong> ${selectedVehicle.make} ${selectedVehicle.model}</p>
        <p><strong>Category:</strong> ${selectedVehicle.category}</p>
    `;
    
    loadServices();
}

async function loadServices() {
    const response = await fetch('/reception/services');
    const services = await response.json();
    
    const servicesList = document.getElementById('servicesList');
    servicesList.innerHTML = services.map(service => `
        <label class="service-item">
            <input type="checkbox" value="${service.id}" data-price="${service.base_price}">
            <div class="service-info">
                <span class="service-name">${service.name}</span>
                <span class="service-price">LKR ${service.base_price.toLocaleString()}</span>
            </div>
        </label>
    `).join('');
    
    document.querySelectorAll('.service-item input').forEach(checkbox => {
        checkbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                selectedServices.push(e.target.value);
            } else {
                selectedServices = selectedServices.filter(id => id !== e.target.value);
            }
        });
    });
}

async function createJob() {
    if (selectedServices.length === 0) {
        showToast('Please select at least one service', 'error');
        return;
    }

    const createBtn = document.getElementById('createJobBtn');
    createBtn.disabled = true;
    createBtn.textContent = 'Creating Job...';

    try {
        const response = await fetch('/reception/job', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                customer_id: selectedCustomer.id,
                vehicle_id: selectedVehicle.vehicle_id,
                service_ids: selectedServices,
                notes: document.getElementById('jobNotes').value
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showToast(`Job ${data.job_number} created successfully!`, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showToast('Error: ' + (data.message || 'Unknown error creating job'), 'error');
            createBtn.disabled = false;
            createBtn.textContent = 'Create Job Card';
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        createBtn.disabled = false;
        createBtn.textContent = 'Create Job Card';
    }
}

function toggleNav() {
    const navMenu = document.getElementById('navMenu');
    navMenu.classList.toggle('active');
}

// Close nav menu when clicking outside
document.addEventListener('click', (e) => {
    const navMenu = document.getElementById('navMenu');
    const navToggle = document.querySelector('.nav-toggle');
    if (!navMenu.contains(e.target) && !navToggle.contains(e.target)) {
        navMenu.classList.remove('active');
    }
});
</script>

<style>
.reception-fullscreen {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    width: 100%;
    overflow-x: hidden;
}

.reception-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    min-height: 100vh;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    position: relative;
    width: 100%;
    overflow-x: hidden;
}

.reception-container::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(26, 26, 46, 0.7) 100%);
    z-index: 0;
    pointer-events: none;
}

.reception-container > * {
    position: relative;
    z-index: 1;
}

.reception-header {
    text-align: center;
    margin-bottom: 50px;
    padding: 40px 20px;
}

.reception-header h1 {
    color: #ffffff !important;
    margin-bottom: 15px;
    font-size: 56px;
    font-weight: 800;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    letter-spacing: -1px;
    line-height: 1.2;
    transition: all 0.3s ease;
    cursor: default;
    animation: blink 2s ease-in-out infinite;
}

@keyframes blink {
    0%, 100% {
        opacity: 1;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    50% {
        opacity: 0.7;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 30px rgba(74, 144, 226, 0.3);
    }
}

.reception-header h1:hover {
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 30px rgba(74, 144, 226, 0.3);
    transform: scale(1.02);
}

.reception-header p {
    color: #ffffff !important;
    font-size: 22px;
    margin: 0;
    text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: default;
    animation: blink 2s ease-in-out infinite;
    animation-delay: 0.5s;
}

.reception-header p:hover {
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4), 0 0 20px rgba(74, 144, 226, 0.2);
}

.reception-nav {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

.nav-toggle {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 24px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.nav-toggle:hover {
    background: rgba(255, 255, 255, 0.3);
}

.nav-menu {
    position: absolute;
    top: 50px;
    right: 0;
    background: rgba(26, 26, 46, 0.95);
    border-radius: 12px;
    padding: 15px;
    min-width: 200px;
    display: none;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.nav-menu.active {
    display: block;
}

.nav-menu a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}

.nav-menu a:hover {
    background: rgba(74, 144, 226, 0.3);
}

.nav-menu a.logout-link {
    color: #e74c3c;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 15px;
    margin-top: 10px;
}

.nav-menu a.logout-link:hover {
    background: rgba(231, 76, 60, 0.2);
}

.search-section {
    margin-bottom: 30px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    font-size: 18px;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    transition: all 0.3s ease;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.search-box input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.search-box input:focus {
    outline: none;
    border-color: rgba(74, 144, 226, 0.8);
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 32px rgba(74, 144, 226, 0.4);
}

.search-results {
    min-height: 100px;
}

.result-card {
    background: rgba(255, 255, 255, 0.95);
    padding: 25px;
    border-radius: 12px;
    border-left: 4px solid #4a90e2;
    margin-bottom: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
}

.result-card h4 {
    margin: 0 0 15px 0;
    color: #1a1a2e;
    font-size: 20px;
}

.result-card p {
    color: #1a1a2e;
    margin: 8px 0;
}

.result-card strong {
    color: #1a1a2e;
}

#toastContainer {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 10000;
    pointer-events: none;
}

.toast {
    pointer-events: auto;
    background: white;
    padding: 16px 24px;
    margin-bottom: 12px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    border-left: 5px solid;
    min-width: 320px;
    transition: all 0.3s ease;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-success {
    border-left-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.toast-error {
    border-left-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

.reception-header {
    text-align: center;
    margin-bottom: 30px;
}

.reception-header h1 {
    color: #1a1a2e;
    margin-bottom: 10px;
}

.reception-header p {
    color: #666;
}

.search-section {
    margin-bottom: 30px;
}

.search-box {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.search-box input {
    flex: 1;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
}

.search-results {
    min-height: 100px;
}

.search-section {
    margin-bottom: 30px;
}

.result-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #4a90e2;
}

.result-card h4 {
    margin-bottom: 15px;
    color: #1a1a2e;
}

.result-card p {
    margin: 8px 0;
    color: #333;
}

.vehicles-list {
    margin: 15px 0;
}

.vehicle-btn {
    display: block;
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    background: white;
    border: 1px solid #4a90e2;
    color: #4a90e2;
    border-radius: 4px;
    cursor: pointer;
}

.vehicle-btn:hover {
    background: #4a90e2;
    color: white;
}

.job-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.vehicle-image-section {
    margin-bottom: 25px;
    text-align: center;
}

.vehicle-image-container {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
    border: 2px solid rgba(74, 144, 226, 0.3);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.4s ease;
    animation: pulse-glow 3s ease-in-out infinite;
}

.vehicle-image-container:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 24px rgba(74, 144, 226, 0.3);
    border-color: rgba(74, 144, 226, 0.6);
}

.vehicle-image-container img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

.vehicle-placeholder {
    padding: 40px 20px;
    color: #4a90e2;
    font-size: 16px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1), 0 0 0 0 rgba(74, 144, 226, 0.4);
    }
    50% {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1), 0 0 20px 5px rgba(74, 144, 226, 0.2);
    }
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.form-header h2 {
    text-align: center;
    color: #4a90e2;
    font-weight: 800;
    font-size: 28px;
    flex: 1;
}

.customer-info, .vehicle-info, .services-section, .notes-section {
    margin-bottom: 25px;
}

.customer-info h3, .vehicle-info h3, .services-section h3, .notes-section h3 {
    color: #1a1a2e;
    margin-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 10px;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.service-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.service-item:hover {
    background: #e9ecef;
}

.service-item input {
    margin-right: 15px;
    width: 20px;
    height: 20px;
}

.service-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
}

.service-name {
    font-weight: 500;
}

.service-price {
    color: #4a90e2;
    font-weight: 600;
}

.notes-section textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    resize: vertical;
}

.btn-primary {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 144, 226, 0.6);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
}

.hidden {
    display: none;
}

.no-results {
    padding: 20px;
    text-align: center;
    color: #666;
}

.no-results a {
    color: #4a90e2;
    text-decoration: underline;
}

.no-results-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.no-results-actions a {
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 6px;
    display: inline-block;
}

.vehicles-list {
    margin: 15px 0;
}

.vehicle-option {
    margin-bottom: 10px;
}

.vehicle-btn {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 15px;
    background: white;
    border: 2px solid #4a90e2;
    color: #4a90e2;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.vehicle-btn:hover {
    background: #4a90e2;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
}

.vehicle-btn strong {
    font-size: 16px;
}

.vehicle-form,
.customer-form {
    margin-top: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #1a1a2e;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #4a90e2;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.form-actions button {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h3 {
    margin: 0;
    color: #1a1a2e;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    line-height: 1;
}

.modal-close:hover {
    color: #1a1a2e;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e0e0e0;
}

.customer-select-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.customer-select-wrapper select {
    flex: 1;
}

.btn-add-customer {
    background: #4a90e2;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    white-space: nowrap;
}

.btn-add-customer:hover {
    background: #357abd;
}

/* Responsive Design */
@media (max-width: 768px) {
    .reception-header h1 {
        font-size: 36px;
    }
    
    .reception-header p {
        font-size: 16px;
    }
    
    .search-box input {
        padding: 15px;
        font-size: 16px;
    }
    
    .result-card {
        padding: 20px;
    }
    
    .result-card h4 {
        font-size: 18px;
    }
    
    .result-card p {
        font-size: 15px;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 10px 20px;
        font-size: 14px;
    }
    
    .nav-toggle {
        padding: 10px 14px;
        font-size: 20px;
    }
    
    .nav-menu {
        min-width: 180px;
        padding: 12px;
    }
    
    .nav-menu a {
        padding: 8px 12px;
        font-size: 14px;
    }
    
    /* Job Form */
    .job-form {
        padding: 20px;
        overflow-x: hidden;
    }
    
    .form-header h2 {
        font-size: 24px;
    }
    
    .form-header h3 {
        font-size: 18px;
    }
    
    .customer-info p,
    .vehicle-info p {
        font-size: 14px;
    }
    
    .service-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .service-card {
        padding: 15px;
        overflow: hidden;
    }
    
    .service-card h4 {
        font-size: 14px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .service-card .price {
        font-size: 16px;
    }
    
    /* Modals */
    .modal-content {
        width: 95%;
        max-width: 450px;
        overflow-x: hidden;
    }
    
    .modal-header h3 {
        font-size: 20px;
    }
    
    .modal-body {
        padding: 15px;
        overflow-x: hidden;
    }
    
    .form-group label {
        font-size: 14px;
        word-wrap: break-word;
    }
    
    .form-group input,
    .form-group select {
        padding: 10px;
        font-size: 14px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .modal-footer {
        padding: 15px;
    }
    
    .modal-footer button {
        padding: 10px 16px;
        font-size: 14px;
        flex: 1;
    }
    
    .customer-select-wrapper {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .btn-add-customer {
        margin-top: 10px;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .reception-header h1 {
        font-size: 28px;
    }
    
    .reception-header p {
        font-size: 14px;
    }
    
    .search-box input {
        padding: 12px;
        font-size: 14px;
    }
    
    .result-card {
        padding: 15px;
    }
    
    .result-card h4 {
        font-size: 16px;
    }
    
    .result-card p {
        font-size: 13px;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .nav-toggle {
        padding: 6px 10px;
        font-size: 16px;
    }
    
    .nav-menu {
        min-width: 140px;
        padding: 8px;
    }
    
    .nav-menu a {
        padding: 5px 8px;
        font-size: 12px;
    }
    
    .vehicle-image-section {
        margin-bottom: 8px;
        text-align: center;
    }
    
    .vehicle-image-container {
        width: 100%;
        max-width: 200px;
        margin: 0 auto;
        border-radius: 8px;
        overflow: hidden;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
        border: 1px solid rgba(74, 144, 226, 0.3);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: all 0.4s ease;
        animation: pulse-glow 3s ease-in-out infinite;
    }
    
    .vehicle-image-container:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(74, 144, 226, 0.25);
        border-color: rgba(74, 144, 226, 0.5);
    }
    
    .vehicle-placeholder {
        padding: 20px 10px;
        color: #4a90e2;
        font-size: 10px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100px;
    }
    
    .form-header h2 {
        font-size: 14px;
        margin-bottom: 8px;
        text-align: center;
        color: #4a90e2;
        font-weight: 800;
    }
    
    .form-header h3 {
        font-size: 11px;
        margin-bottom: 6px;
    }
    
    .customer-info,
    .vehicle-info,
    .services-section,
    .notes-section {
        margin-bottom: 10px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
        padding: 12px;
        border-radius: 10px;
        border: 1px solid rgba(74, 144, 226, 0.25);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .customer-info:hover,
    .vehicle-info:hover,
    .services-section:hover,
    .notes-section:hover {
        box-shadow: 0 6px 16px rgba(74, 144, 226, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.9) inset;
        border-color: rgba(74, 144, 226, 0.4);
    }
    
    .customer-info h3,
    .vehicle-info h3,
    .services-section h3,
    .notes-section h3 {
        font-size: 12px;
        margin-bottom: 10px;
        padding-bottom: 8px;
        color: #1a1a2e;
        border-bottom: 2px solid #4a90e2;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        background: linear-gradient(90deg, #4a90e2, #6ab0f3);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .customer-info p,
    .vehicle-info p {
        font-size: 9px;
        word-wrap: break-word;
        margin: 2px 0;
        line-height: 1.1;
    }
    
    .customer-info strong,
    .vehicle-info strong {
        font-size: 9px;
        font-weight: 600;
    }
    
    .services-grid {
        grid-template-columns: 1fr;
        gap: 1px;
        justify-content: center;
        padding: 0 2px;
        max-width: 100%;
        overflow: hidden;
    }
    
    .service-item {
        padding: 2px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2px;
        text-align: center;
        max-width: 100%;
    }
    
    .service-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2px;
        flex: 1;
        overflow: hidden;
    }
    
    .service-name {
        font-size: 8px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        margin: 0;
        flex: 1;
        text-align: left;
        line-height: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 60%;
    }
    
    .service-price {
        font-size: 8px;
        margin: 0;
        white-space: nowrap;
        text-align: right;
        flex-shrink: 0;
    }
    
    .service-item input[type="checkbox"] {
        width: 10px;
        height: 10px;
        flex-shrink: 0;
        margin-right: 2px;
    }
    
    .notes-section textarea {
        padding: 5px;
        font-size: 10px;
        width: 100%;
        box-sizing: border-box;
        min-height: 40px;
    }
    
    .btn-primary {
        padding: 6px 10px;
        font-size: 10px;
    }
    
    /* Modals */
    .modal-content {
        width: 95%;
        max-width: 320px;
        max-height: 90vh;
        overflow-x: hidden;
    }
    
    .modal-header h3 {
        font-size: 16px;
    }
    
    .modal-body {
        padding: 10px;
        overflow-x: hidden;
    }
    
    .form-group {
        margin-bottom: 10px;
    }
    
    .form-group label {
        font-size: 12px;
        word-wrap: break-word;
        margin-bottom: 4px;
    }
    
    .form-group input,
    .form-group select {
        padding: 6px;
        font-size: 12px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .modal-footer {
        padding: 10px;
        flex-direction: column;
        gap: 6px;
    }
    
    .modal-footer button {
        padding: 6px 10px;
        font-size: 12px;
        width: 100%;
    }
    
    .customer-select-wrapper {
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
    }
    
    .btn-add-customer {
        margin-top: 8px;
        padding: 6px 10px;
        font-size: 12px;
        width: 100%;
    }
}
</style>
@endsection
