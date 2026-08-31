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
    $hasCustomBg = false;
    if ($bgType === 'color' && $customBgColor) {
        $bgStyle = "background: {$customBgColor};";
        $hasCustomBg = true;
    } elseif ($bgType === 'color' && $bgColor) {
        $bgStyle = "background: {$bgColor};";
        $hasCustomBg = true;
    } elseif ($bgImage) {
        $bgStyle = "background-image: url('{{ asset($bgImage) }}');";
        $hasCustomBg = true;
    } else {
        $bgStyle = "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";
    }
@endphp

<div class="reception-container {{ $hasCustomBg ? 'custom-bg' : '' }}" style="{{ $bgStyle }}">
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
        <h1>Vehicle Check-in</h1>
        <p>Enter vehicle registration number to begin</p>
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

    <!-- Job Card Modal -->
    <div id="jobModal" class="modal">
        <div class="modal-content job-modal-content">
            <div class="modal-header">
                <h3>Create Job Card</h3>
                <button class="modal-close" onclick="closeJobModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="vehicle-image-section">
                    <div id="vehicleImageContainer" class="vehicle-image-container">
                        <div class="vehicle-placeholder">
                            <span>Vehicle Image</span>
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="customer-info">
                        <h3>Customer Information</h3>
                        <div id="customerDetails"></div>
                    </div>

                    <div class="vehicle-info">
                        <h3>Vehicle Information</h3>
                        <div id="vehicleDetails"></div>
                    </div>
                </div>

                <div class="services-section">
                    <h3>Select Services</h3>
                    <div id="servicesList" class="services-grid"></div>
                </div>

                <div class="notes-section">
                    <h3>Notes</h3>
                    <textarea id="jobNotes" rows="3" placeholder="Additional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button id="createJobBtn" class="btn-primary">Create Job Card</button>
                <button id="cancelBtn" class="btn-secondary">Cancel</button>
            </div>
        </div>
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

// Cancel button now just delegates to closeJobModal(), which owns
// all of the "reset the form" cleanup in one place.
document.getElementById('cancelBtn').addEventListener('click', closeJobModal);

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

// FIX: closing the job card (via the X button OR the Cancel button)
// now fully resets the search box and all selected state, so a
// leftover registration number can't linger for the next check-in.
function closeJobModal() {
    document.getElementById('jobModal').classList.remove('active');
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = '';
    selectedCustomer = null;
    selectedVehicle = null;
    selectedServices = [];
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
            // FIX: include category (fall back to what was picked in the
            // modal in case the server response doesn't echo it back),
            // otherwise it shows as "undefined" in the job form.
            selectedVehicle = {
                vehicle_id: data.id,
                id: data.id,
                registration_number: data.registration_number,
                make: data.make,
                model: data.model,
                category: data.category || category,
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
    selectedCustomer = {
        id: vehicle.customer_id,
        name: vehicle.customer_name
    };

    selectedVehicle = {
        vehicle_id: vehicle.vehicle_id ?? vehicle.id,
        id: vehicle.vehicle_id ?? vehicle.id,
        registration_number: vehicle.registration_number ?? '',
        make: vehicle.make ?? '',
        model: vehicle.model ?? '',
        category: vehicle.category ?? '',
        customer_id: vehicle.customer_id
    };

    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value =
        vehicle.registration_number ?? '';

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
    selectedVehicle = {
        vehicle_id: vehicle.vehicle_id ?? vehicle.id,
        id: vehicle.vehicle_id ?? vehicle.id,
        registration_number: vehicle.registration_number ?? '',
        make: vehicle.make ?? '',
        model: vehicle.model ?? '',
        category: vehicle.category ?? '',
        customer_id: vehicle.customer_id
    };

    showJobForm();
}

function showJobForm() {
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('jobModal').classList.add('active');

    // ---------------------------------------------------------
    // Customer information
    // ---------------------------------------------------------
    document.getElementById('customerDetails').innerHTML = `
        <p>
            <strong>Name:</strong>
            ${selectedCustomer?.name ?? ''}
        </p>
    `;

    // ---------------------------------------------------------
    // Vehicle image
    // ---------------------------------------------------------
    const vehicleImageContainer =
        document.getElementById('vehicleImageContainer');

    if (selectedVehicle?.image) {
        vehicleImageContainer.innerHTML = `
            <img
                src="${selectedVehicle.image}"
                alt="Vehicle Image"
            >
        `;
    } else {
        vehicleImageContainer.innerHTML = `
            <div class="vehicle-placeholder">
                <span>Vehicle Image</span>
            </div>
        `;
    }

    // ---------------------------------------------------------
    // Vehicle information
    // ---------------------------------------------------------
    const category = selectedVehicle?.category;

    document.getElementById('vehicleDetails').innerHTML = `
        <p>
            <strong>Registration:</strong>
            ${selectedVehicle?.registration_number ?? ''}
        </p>

        <p>
            <strong>Make/Model:</strong>
            ${selectedVehicle?.make ?? ''}
            ${selectedVehicle?.model ?? ''}
        </p>

        <p>
            <strong>Category:</strong>
            ${category || 'Not specified'}
        </p>
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
/* =========================================================
   TOKENS
   A workshop diagnostic-screen feel: deep steel-blue base,
   one cyan accent, glass surfaces used only where they help
   (search, cards, modal) — not stacked everywhere.
   ========================================================= */
:root {
    --ink: #060a14;
    --panel: rgba(255, 255, 255, 0.06);
    --panel-border: rgba(147, 197, 253, 0.22);
    --accent: #38bdf8;
    --accent-deep: #2563eb;
    --text: #f1f5f9;
    --text-dim: rgba(226, 232, 240, 0.75);
}

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
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.reception-container::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
        radial-gradient(circle at 15% 20%, rgba(59, 130, 246, 0.10), transparent 35%),
        radial-gradient(circle at 85% 75%, rgba(56, 189, 248, 0.08), transparent 35%),
        linear-gradient(135deg, rgba(3, 7, 18, 0.78) 0%, rgba(8, 15, 30, 0.82) 50%, rgba(4, 10, 22, 0.88) 100%);
    z-index: 0;
    pointer-events: none;
}

/* Remove dark overlay when custom background is set */
.reception-container.custom-bg::before {
    display: none;
}

.reception-container > * {
    position: relative;
    z-index: 1;
}

/* ---------------------------------------------------------
   Header — static, legible. No infinite blink: it's the
   first thing a receptionist reads dozens of times a shift.
   --------------------------------------------------------- */
.reception-header {
    text-align: center;
    margin-bottom: 44px;
    padding: 36px 20px 0;
}

.reception-header h1 {
    color: var(--text);
    margin-bottom: 10px;
    font-size: 44px;
    font-weight: 700;
    letter-spacing: -0.5px;
    line-height: 1.2;
    animation: blink 2s ease-in-out infinite;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.reception-header p {
    color: var(--text-dim);
    font-size: 18px;
    margin: 0;
    font-weight: 400;
    animation: blink 2s ease-in-out infinite;
    animation-delay: 0.5s;
    text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
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

.reception-header p:hover {
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4), 0 0 20px rgba(74, 144, 226, 0.2);
}

/* ---------------------------------------------------------
   Nav
   --------------------------------------------------------- */
.reception-nav {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

.nav-toggle {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid var(--panel-border);
    color: white;
    padding: 12px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 22px;
    backdrop-filter: blur(10px);
    transition: background 0.2s ease;
}

.nav-toggle:hover {
    background: rgba(255, 255, 255, 0.16);
}

.nav-menu {
    position: absolute;
    top: 52px;
    right: 0;
    background: rgba(10, 16, 32, 0.96);
    border: 1px solid var(--panel-border);
    border-radius: 12px;
    padding: 10px;
    min-width: 200px;
    display: none;
    backdrop-filter: blur(16px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

.nav-menu.active {
    display: block;
}

.nav-menu a {
    display: block;
    color: var(--text);
    text-decoration: none;
    padding: 10px 14px;
    border-radius: 7px;
    margin-bottom: 2px;
    font-size: 14px;
    transition: background 0.15s ease;
}

.nav-menu a:hover {
    background: rgba(56, 189, 248, 0.14);
}

.nav-menu a.logout-link {
    color: #f87171;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 12px;
    margin-top: 8px;
}

.nav-menu a.logout-link:hover {
    background: rgba(239, 68, 68, 0.14);
}

/* ---------------------------------------------------------
   Search
   --------------------------------------------------------- */
.search-section {
    margin-bottom: 30px;
    position: relative;
}

.search-box {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.search-box input {
    width: 100%;
    padding: 20px 22px;
    border: 1px solid var(--panel-border);
    border-radius: 16px;
    font-size: 18px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(125, 211, 252, 0.07));
    color: var(--text);
    backdrop-filter: blur(24px) saturate(140%);
    -webkit-backdrop-filter: blur(24px) saturate(140%);
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

.search-box input::placeholder {
    color: rgba(255, 255, 255, 0.55);
}

.search-box input:focus {
    outline: none;
    border-color: rgba(125, 211, 252, 0.75);
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.12), 0 12px 45px rgba(14, 165, 233, 0.22);
}

.search-results {
    min-height: 100px;
}

/* ---------------------------------------------------------
   Result cards — one entrance animation on render only.
   --------------------------------------------------------- */
.result-card {
    position: relative;
    padding: 24px;
    border-radius: 18px;
    border: 1px solid var(--panel-border);
    border-left: 3px solid var(--accent);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.11), rgba(96, 165, 250, 0.06));
    backdrop-filter: blur(22px) saturate(140%);
    -webkit-backdrop-filter: blur(22px) saturate(140%);
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.30), inset 0 1px 0 rgba(255, 255, 255, 0.10);
    color: var(--text);
    margin-bottom: 15px;
    animation: resultCardIn 0.3s ease both;
}

@keyframes resultCardIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.result-card h4 {
    margin: 0 0 15px;
    color: #e0f2fe;
    font-size: 17px;
    font-weight: 600;
}

.result-card p {
    margin: 8px 0;
    color: var(--text-dim);
}

.result-card strong {
    color: #bae6fd;
}

.no-results {
    padding: 20px;
    text-align: center;
    color: var(--text-dim);
}

.no-results a {
    color: var(--accent);
    text-decoration: underline;
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
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--accent);
    color: var(--accent);
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s ease, color 0.2s ease;
}

.vehicle-btn:hover {
    background: var(--accent);
    color: #06263a;
}

.vehicle-btn strong {
    font-size: 16px;
}

/* ---------------------------------------------------------
   Toasts
   --------------------------------------------------------- */
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
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    border-left: 5px solid;
    min-width: 320px;
    transition: opacity 0.3s ease;
    animation: slideIn 0.25s ease;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
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

/* ---------------------------------------------------------
   Job card content — lives inside the job modal now, so it
   reuses the same section styling as before but without its
   own outer panel (the modal-content supplies that).

   "Age-friendly" pass: bigger type, more generous line
   height and spacing, softer/rounder cards, higher-contrast
   labels — comfortable to read for anyone, not just people
   with sharp near vision.
   --------------------------------------------------------- */
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
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.12), rgba(59, 130, 246, 0.07));
    border: 1px solid var(--panel-border);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.vehicle-image-container:hover {
    box-shadow: 0 8px 24px rgba(56, 189, 248, 0.25);
    border-color: rgba(125, 211, 252, 0.55);
}

.vehicle-image-container img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

.vehicle-placeholder {
    padding: 40px 20px;
    color: var(--accent);
    font-size: 16px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

/* Customer + Vehicle information side by side */
.info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.customer-info, .vehicle-info, .services-section, .notes-section {
    margin-bottom: 25px;
}

.info-row .customer-info, .info-row .vehicle-info {
    margin-bottom: 0;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.16);
    border-radius: 16px;
    padding: 20px;
}

.customer-info h3, .vehicle-info h3, .services-section h3, .notes-section h3 {
    color: #e0f2fe;
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(186, 230, 253, 0.16);
    padding-bottom: 10px;
    font-size: 17px;
    font-weight: 700;
}

.customer-info p, .vehicle-info p {
    margin: 10px 0;
    color: var(--text-dim);
    font-size: 16px;
    line-height: 1.6;
}

.customer-info strong, .vehicle-info strong {
    color: #bae6fd;
    font-weight: 700;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.service-item {
    display: flex;
    align-items: center;
    padding: 16px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
    border: 1px solid rgba(186, 230, 253, 0.14);
    border-radius: 14px;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.service-item:hover {
    background: rgba(56, 189, 248, 0.14);
    border-color: rgba(125, 211, 252, 0.4);
}

.service-item input {
    margin-right: 15px;
    width: 22px;
    height: 22px;
    accent-color: var(--accent);
    flex-shrink: 0;
}

.service-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.service-name {
    font-weight: 500;
    color: var(--text);
    font-size: 15px;
}

.service-price {
    color: var(--accent);
    font-weight: 600;
    font-size: 15px;
}

.notes-section textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 14px 16px;
    border: 1px solid rgba(186, 230, 253, 0.25);
    border-radius: 14px;
    font-size: 15px;
    line-height: 1.6;
    color: var(--text);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.12), rgba(59, 130, 246, 0.07));
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    resize: vertical;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    font-family: inherit;
}

.notes-section textarea::placeholder {
    color: rgba(186, 230, 253, 0.55);
}

.notes-section textarea:focus {
    outline: none;
    border-color: rgba(125, 211, 252, 0.75);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12);
}

#createJobBtn {
    flex: 1;
}

/* ---------------------------------------------------------
   Buttons — one calm hover treatment each, no idle motion.
   --------------------------------------------------------- */
.btn-primary {
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(37, 99, 235, 0.5);
}

.result-card .btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.10);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 12px 24px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.2s ease, border-color 0.2s ease;
    backdrop-filter: blur(10px);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(255, 255, 255, 0.5);
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

.hidden {
    display: none;
}

/* ---------------------------------------------------------
   Modals
   --------------------------------------------------------- */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at center, rgba(14, 165, 233, 0.08), transparent 45%), rgba(2, 6, 23, 0.72);
    backdrop-filter: blur(14px) saturate(120%);
    -webkit-backdrop-filter: blur(14px) saturate(120%);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal.active {
    display: flex;
}

.modal-content {
    position: relative;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.16), rgba(30, 64, 175, 0.10));
    border: 1px solid var(--panel-border);
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    backdrop-filter: blur(30px) saturate(145%);
    -webkit-backdrop-filter: blur(30px) saturate(145%);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.55), 0 0 50px rgba(14, 165, 233, 0.10), inset 0 1px 0 rgba(255, 255, 255, 0.16);
    animation: modalGlassIn 0.3s cubic-bezier(.2,.8,.2,1);
}

/* Job card modal needs more room now that customer + vehicle
   info sit side by side, and reads better a bit wider overall.
   It keeps the same blue family as the rest of the app but a
   touch lighter/brighter than the base modal — this is the
   "taking care of the customer" moment, so it gets a slightly
   airier, higher-key version of the shared blue palette. */
.job-modal-content {
    max-width: 960px;
    --job-accent: #7dd3fc;        /* lighter sky blue */
    --job-accent-deep: #38bdf8;   /* brighter accent blue */
    --job-panel-border: rgba(186, 230, 253, 0.30);
    --job-text-warm: #e6f6ff;
}

.job-modal-content .modal-header {
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.20), rgba(56, 189, 248, 0.12));
    border-bottom: 1px solid var(--job-panel-border);
}

.job-modal-content .modal-header h3 {
    color: var(--job-text-warm);
}

.job-modal-content .modal-footer {
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.16), rgba(56, 189, 248, 0.09));
    border-top: 1px solid var(--job-panel-border);
}

@keyframes modalGlassIn {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 24px;
    border-bottom: 1px solid rgba(186, 230, 253, 0.16);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.10), rgba(59, 130, 246, 0.05));
}

.modal-header h3 {
    margin: 0;
    color: #e0f2fe;
    font-size: 20px;
    font-weight: 700;
}

.modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(186, 230, 253, 0.18);
    border-radius: 9px;
    font-size: 22px;
    cursor: pointer;
    color: rgba(224, 242, 254, 0.75);
    line-height: 1;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.modal-close:hover {
    color: #ffffff;
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(248, 113, 113, 0.40);
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    gap: 10px;
    padding: 20px 24px;
    border-top: 1px solid rgba(186, 230, 253, 0.14);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.07), rgba(59, 130, 246, 0.04));
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

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #bae6fd;
    font-size: 13px;
    letter-spacing: 0.2px;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 13px 14px;
    border: 1px solid rgba(186, 230, 253, 0.25);
    border-radius: 12px;
    font-size: 14px;
    color: var(--text);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.12), rgba(59, 130, 246, 0.07));
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input::placeholder {
    color: rgba(186, 230, 253, 0.55);
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: rgba(125, 211, 252, 0.75);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12);
}

/* Native <select> dropdown panels render outside our
   glassmorphism (the browser draws them), so the option list
   was showing default white-on-black-ish system colors.
   Force a dark background + light text on the options
   themselves so the open dropdown matches the theme. */
.form-group select option,
.customer-select-wrapper select option {
    background-color: #0b1224;
    color: var(--text);
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
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.75), rgba(37, 99, 235, 0.75));
    color: #ffffff;
    border: 1px solid rgba(186, 230, 253, 0.35);
    padding: 11px 16px;
    border-radius: 11px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    backdrop-filter: blur(10px);
    transition: background 0.2s ease, transform 0.2s ease;
}

.btn-add-customer:hover {
    transform: translateY(-1px);
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.9), rgba(37, 99, 235, 0.9));
}

/* ---------------------------------------------------------
   Job Card Modal — lighter blue palette applied to its inner
   sections (image box, customer/vehicle panels, service
   cards, notes, and the primary action button). Scoped
   entirely under .job-modal-content so nothing else in
   the app is affected.
   --------------------------------------------------------- */
.job-modal-content .info-row .customer-info,
.job-modal-content .info-row .vehicle-info {
    background: linear-gradient(135deg, rgba(186, 230, 253, 0.18), rgba(125, 211, 252, 0.08));
    border: 1px solid var(--job-panel-border);
}

.job-modal-content .customer-info h3,
.job-modal-content .vehicle-info h3,
.job-modal-content .services-section h3,
.job-modal-content .notes-section h3 {
    color: var(--job-text-warm);
    border-bottom: 1px solid var(--job-panel-border);
}

.job-modal-content .customer-info strong,
.job-modal-content .vehicle-info strong {
    color: var(--job-accent);
}

.job-modal-content .vehicle-image-container {
    background: linear-gradient(135deg, rgba(186, 230, 253, 0.20), rgba(125, 211, 252, 0.10));
    border: 1px solid var(--job-panel-border);
}

.job-modal-content .vehicle-image-container:hover {
    box-shadow: 0 8px 24px rgba(56, 189, 248, 0.28);
    border-color: rgba(186, 230, 253, 0.6);
}

.job-modal-content .vehicle-placeholder {
    color: var(--job-accent);
}

.job-modal-content .service-item {
    background: linear-gradient(135deg, rgba(186, 230, 253, 0.18), rgba(125, 211, 252, 0.08));
    border: 1px solid rgba(186, 230, 253, 0.20);
}

.job-modal-content .service-item:hover {
    background: rgba(125, 211, 252, 0.20);
    border-color: rgba(186, 230, 253, 0.5);
}

.job-modal-content .service-item input {
    accent-color: var(--job-accent);
}

.job-modal-content .service-price {
    color: var(--job-accent-deep);
}

.job-modal-content .notes-section textarea {
    border: 1px solid rgba(186, 230, 253, 0.30);
    background: linear-gradient(135deg, rgba(186, 230, 253, 0.16), rgba(125, 211, 252, 0.08));
}

.job-modal-content .notes-section textarea:focus {
    border-color: rgba(125, 211, 252, 0.8);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
}

.job-modal-content #createJobBtn {
    background: linear-gradient(135deg, var(--job-accent) 0%, var(--job-accent-deep) 100%);
    box-shadow: 0 4px 15px rgba(56, 189, 248, 0.35);
}

.job-modal-content #createJobBtn:hover {
    box-shadow: 0 8px 22px rgba(56, 189, 248, 0.5);
}

/* ---------------------------------------------------------
   Responsive
   --------------------------------------------------------- */
@media (max-width: 768px) {
    .reception-header h1 { font-size: 32px; }
    .reception-header p { font-size: 15px; }
    .search-box input { padding: 15px; font-size: 16px; }
    .result-card { padding: 20px; }
    .result-card h4 { font-size: 16px; }
    .result-card p { font-size: 14px; }
    .btn-primary, .btn-secondary { padding: 10px 20px; font-size: 14px; }
    .nav-toggle { padding: 10px 14px; font-size: 20px; }
    .nav-menu { min-width: 180px; padding: 10px; }
    .nav-menu a { padding: 8px 12px; font-size: 14px; }

    .form-header h2 { font-size: 22px; }
    .customer-info p, .vehicle-info p { font-size: 14px; }

    /* Stack customer/vehicle info back to one column on
       narrower screens so nothing gets cramped. */
    .info-row { grid-template-columns: 1fr; gap: 15px; }

    .modal-content { width: 95%; max-width: 450px; overflow-x: hidden; }
    .job-modal-content { max-width: 680px; }
    .modal-header h3 { font-size: 18px; }
    .modal-body { padding: 15px; overflow-x: hidden; }
    .form-group label { font-size: 14px; }
    .form-group input, .form-group select { padding: 10px; font-size: 14px; }
    .modal-footer { padding: 15px; }
    .modal-footer button { padding: 10px 16px; font-size: 14px; flex: 1; }
    .customer-select-wrapper { flex-direction: column; align-items: stretch; gap: 10px; }
    .btn-add-customer { margin-top: 10px; width: 100%; }
}

@media (max-width: 480px) {
    .reception-header h1 { font-size: 26px; }
    .reception-header p { font-size: 13px; }
    .search-box input { padding: 12px; font-size: 14px; }
    .result-card { padding: 16px; }
    .result-card h4 { font-size: 15px; }
    .result-card p { font-size: 13px; }
    .btn-primary, .btn-secondary { padding: 9px 16px; font-size: 13px; }
    .nav-toggle { padding: 8px 12px; font-size: 18px; }
    .nav-menu { min-width: 160px; padding: 8px; }
    .nav-menu a { padding: 7px 10px; font-size: 13px; }

    .vehicle-image-container { max-width: 220px; margin: 0 auto; border-radius: 10px; }
    .vehicle-placeholder { padding: 24px 10px; font-size: 12px; min-height: 110px; }

    .info-row { grid-template-columns: 1fr; gap: 10px; }

    .customer-info, .vehicle-info, .services-section, .notes-section {
        margin-bottom: 14px;
    }

    .info-row .customer-info, .info-row .vehicle-info {
        background: linear-gradient(135deg, rgba(125, 211, 252, 0.09), rgba(59, 130, 246, 0.05));
        padding: 14px;
        border-radius: 12px;
        border: 1px solid rgba(186, 230, 253, 0.16);
    }

    .services-section, .notes-section {
        background: linear-gradient(135deg, rgba(125, 211, 252, 0.09), rgba(59, 130, 246, 0.05));
        padding: 14px;
        border-radius: 12px;
        border: 1px solid rgba(186, 230, 253, 0.16);
    }

    .customer-info h3, .vehicle-info h3, .services-section h3, .notes-section h3 {
        font-size: 13px;
        margin-bottom: 10px;
        padding-bottom: 8px;
        color: #e0f2fe;
        border-bottom: 1px solid rgba(56, 189, 248, 0.4);
        font-weight: 700;
    }

    .customer-info p, .vehicle-info p { font-size: 13px; margin: 5px 0; }
    .customer-info strong, .vehicle-info strong { font-size: 13px; font-weight: 700; }

    .services-grid { grid-template-columns: 1fr; gap: 8px; }
    .service-item { padding: 10px; }
    .service-name { font-size: 13px; }
    .service-price { font-size: 13px; }

    .notes-section textarea { padding: 8px; font-size: 13px; min-height: 60px; }
    .btn-primary { padding: 10px 16px; font-size: 13px; }

    .modal-content { width: 95%; max-width: 340px; overflow-x: hidden; }
    .job-modal-content { max-width: 400px; }
    .modal-header h3 { font-size: 16px; }
    .modal-body { padding: 12px; overflow-x: hidden; }
    .form-group { margin-bottom: 10px; }
    .form-group label { font-size: 12px; }
    .form-group input, .form-group select { padding: 8px; font-size: 12px; }
    .modal-footer { padding: 12px; flex-direction: column; gap: 8px; }
    .modal-footer button { padding: 10px; font-size: 13px; width: 100%; }
    .customer-select-wrapper { flex-direction: column; align-items: stretch; gap: 8px; }
    .btn-add-customer { margin-top: 8px; padding: 10px; font-size: 13px; width: 100%; }
}

/* Respect reduced-motion preference */
@media (prefers-reduced-motion: reduce) {
    * { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
}
</style>
@endsection