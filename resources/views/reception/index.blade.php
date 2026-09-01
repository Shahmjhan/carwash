@extends('layouts.reception')

@section('content')
@php
    $business = auth()->user()->business;
    $settings = $business ? $business->getBillingSettings() : [];

    $bgType = $settings['background_type'] ?? 'image';
    $bgImage = $settings['reception_background_image'] ?? '';
    $bgColor = $settings['background_color'] ?? '';
    $customBgColor = $settings['custom_background_color'] ?? '';

    if ($bgType === 'color' && $customBgColor) {
        $receptionBackground = $customBgColor;
        $receptionBackgroundType = 'color';
    } elseif ($bgType === 'color' && $bgColor) {
        $receptionBackground = $bgColor;
        $receptionBackgroundType = 'color';
    } elseif ($bgImage) {
        $receptionBackground = "url('" . asset($bgImage) . "')";
        $receptionBackgroundType = 'image';
    } else {
        $receptionBackground = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $receptionBackgroundType = 'color';
    }
@endphp

<style>
    :root {
        --reception-background: {!! $receptionBackground !!};
    }
</style>

{{-- Toast outside reception-container so it is never trapped under modal blur --}}
<div id="toastContainer"></div>

<div class="reception-container"
    @if($receptionBackgroundType === 'image')
        style="--reception-background: {{ $receptionBackground }};"
    @endif>
    
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
                <!-- Vehicle Image Upload -->
                <div class="form-group">
                    <label>Vehicle Image</label>
                    <div class="image-upload-area" id="vehicleImageUploadArea">
                        <div class="image-preview" id="vehicleImagePreview" style="display:none;">
                            <img id="vehiclePreviewImg" src="" alt="Vehicle preview">
                            <button type="button" class="btn-remove-image" onclick="removeVehicleImage()" title="Remove image">&times;</button>
                        </div>
                        <div class="image-upload-placeholder" id="vehicleImagePlaceholder">
                            <div class="upload-icon">📷</div>
                            <p>Add vehicle photo</p>
                            <div class="upload-actions">
                                <button type="button" class="btn-upload" onclick="openLiveCamera('vehicle')">
                                    📷 Camera
                                </button>
                                <label class="btn-upload btn-upload-secondary" for="vehicleImageInput">
                                    <input type="file" id="vehicleImageInput" accept="image/*" style="display:none;">
                                    🖼️ Gallery / Files
                                </label>
                            </div>
                            <small class="upload-hint">Phone: Camera or Gallery · Desktop: choose either</small>
                        </div>
                    </div>
                </div>

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
                    <!-- Change image buttons in job form -->
                    <div class="job-image-actions">
                        <button type="button" class="btn-change-image" onclick="openLiveCamera('job')">
                            📷 Camera
                        </button>
                        <label class="btn-change-image" for="jobVehicleImageInput">
                            <input type="file" id="jobVehicleImageInput" accept="image/*" style="display:none;">
                            🖼️ Gallery / Files
                        </label>
                        <button type="button" id="removeJobImageBtn" class="btn-remove-job-image" onclick="removeJobImage()" style="display:none;">Remove</button>
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

    <!-- Live Camera Modal (desktop + phone) -->
    <div id="liveCameraModal" class="modal">
        <div class="modal-content" style="max-width:640px;">
            <div class="modal-header">
                <h3>Take Photo</h3>
                <button class="modal-close" onclick="closeLiveCamera()">&times;</button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <video id="liveCameraVideo" autoplay playsinline style="width:100%;max-height:360px;border-radius:12px;background:#000;"></video>
                <canvas id="liveCameraCanvas" style="display:none;"></canvas>
                <p id="liveCameraError" style="color:#fca5a5;display:none;margin-top:12px;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary" onclick="captureLivePhoto()">Capture</button>
                <button type="button" class="btn-secondary" onclick="closeLiveCamera()">Cancel</button>
            </div>
        </div>
    </div>

<script>
let selectedCustomer = null;
let selectedVehicle = null;
let selectedServices = [];
let searchTimeout = null;

// Image state
let vehicleImageFile = null;           // File object for new vehicle
let vehicleImagePreviewDataUrl = null; // data URL kept until job form opens
let jobImageFile = null;               // File object when changing image in job form
let jobImagePreviewUrl = null;         // Object URL or existing image URL for job form

// Build a usable image URL from whatever the API returns
function resolveImageUrl(image) {
    if (!image || typeof image !== 'string') return null;
    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('blob:') ||
        image.startsWith('data:')
    ) {
        return image;
    }
    return '/storage/' + image.replace(/^\/+/, '');
}

function showToast(message, type = 'success') {
    let container = document.getElementById('toastContainer');
    // Ensure toast container is a direct child of body (above everything)
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        document.body.appendChild(container);
    } else if (container.parentElement !== document.body) {
        document.body.appendChild(container);
    }

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

document.getElementById('cancelBtn').addEventListener('click', closeJobModal);
document.getElementById('createJobBtn').addEventListener('click', createJob);

// Wire gallery/file inputs
document.getElementById('vehicleImageInput')?.addEventListener('change', handleVehicleImageSelect);
document.getElementById('jobVehicleImageInput')?.addEventListener('change', handleJobImageSelect);

// ---------- Live camera (works on desktop + phone) ----------
let liveCameraStream = null;
let liveCameraTarget = null; // 'vehicle' or 'job'

async function openLiveCamera(target) {
    liveCameraTarget = target;
    const modal = document.getElementById('liveCameraModal');
    const video = document.getElementById('liveCameraVideo');
    const errEl = document.getElementById('liveCameraError');
    errEl.style.display = 'none';
    errEl.textContent = '';
    modal.classList.add('active');

    // Stop any previous stream
    if (liveCameraStream) {
        liveCameraStream.getTracks().forEach(t => t.stop());
        liveCameraStream = null;
    }

    try {
        // Prefer back camera when available
        liveCameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        });
        video.srcObject = liveCameraStream;
        await video.play();
    } catch (err) {
        console.error(err);
        // Fallback: any camera
        try {
            liveCameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            video.srcObject = liveCameraStream;
            await video.play();
        } catch (err2) {
            errEl.textContent = 'Camera access denied or not available. Use Gallery / Files instead.';
            errEl.style.display = 'block';
            showToast('Camera not available', 'error');
        }
    }
}

function closeLiveCamera() {
    const modal = document.getElementById('liveCameraModal');
    const video = document.getElementById('liveCameraVideo');
    if (liveCameraStream) {
        liveCameraStream.getTracks().forEach(t => t.stop());
        liveCameraStream = null;
    }
    if (video) video.srcObject = null;
    modal.classList.remove('active');
    liveCameraTarget = null;
}

function captureLivePhoto() {
    const video = document.getElementById('liveCameraVideo');
    const canvas = document.getElementById('liveCameraCanvas');
    if (!video || !video.videoWidth) {
        showToast('Camera not ready yet', 'error');
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(function (blob) {
        if (!blob) {
            showToast('Could not capture photo', 'error');
            return;
        }
        const file = new File([blob], 'camera-capture.jpg', { type: 'image/jpeg' });

        if (liveCameraTarget === 'vehicle') {
            // Feed into vehicle image flow
            vehicleImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                vehicleImagePreviewDataUrl = e.target.result;
                document.getElementById('vehiclePreviewImg').src = vehicleImagePreviewDataUrl;
                document.getElementById('vehicleImagePreview').style.display = 'block';
                document.getElementById('vehicleImagePlaceholder').style.display = 'none';
                showToast('Photo captured', 'success');
            };
            reader.readAsDataURL(file);
        } else if (liveCameraTarget === 'job') {
            jobImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                jobImagePreviewUrl = e.target.result;
                const container = document.getElementById('vehicleImageContainer');
                if (container) {
                    container.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = jobImagePreviewUrl;
                    img.alt = 'Vehicle Image';
                    img.style.cssText = 'width:100%;height:auto;display:block;object-fit:cover;min-height:180px;';
                    container.appendChild(img);
                }
                const removeBtn = document.getElementById('removeJobImageBtn');
                if (removeBtn) removeBtn.style.display = 'inline-block';
                showToast('Photo captured', 'success');
            };
            reader.readAsDataURL(file);
        }

        closeLiveCamera();
    }, 'image/jpeg', 0.92);
}

// ---------- Vehicle image (Add New Vehicle modal) ----------
function handleVehicleImageSelect(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showToast('Please select an image file', 'error');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showToast('Image must be under 5MB', 'error');
        return;
    }

    vehicleImageFile = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        vehicleImagePreviewDataUrl = e.target.result;
        document.getElementById('vehiclePreviewImg').src = vehicleImagePreviewDataUrl;
        document.getElementById('vehicleImagePreview').style.display = 'block';
        document.getElementById('vehicleImagePlaceholder').style.display = 'none';
        showToast('Photo selected', 'success');
    };
    reader.onerror = () => showToast('Could not read image', 'error');
    reader.readAsDataURL(file);
}

function removeVehicleImage() {
    vehicleImageFile = null;
    vehicleImagePreviewDataUrl = null;
    const input = document.getElementById('vehicleImageInput');
    if (input) input.value = '';
    const cameraInput = document.getElementById('vehicleImageCameraInput');
    if (cameraInput) cameraInput.value = '';
    const previewImg = document.getElementById('vehiclePreviewImg');
    if (previewImg) previewImg.src = '';
    const preview = document.getElementById('vehicleImagePreview');
    if (preview) preview.style.display = 'none';
    const placeholder = document.getElementById('vehicleImagePlaceholder');
    if (placeholder) placeholder.style.display = 'block';
}

// ---------- Job form image (edit existing vehicle image) ----------
function handleJobImageSelect(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showToast('Please select an image file', 'error');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showToast('Image must be under 5MB', 'error');
        return;
    }

    jobImageFile = file;

    // Revoke previous object URL if any
    if (jobImagePreviewUrl && jobImagePreviewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(jobImagePreviewUrl);
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        jobImagePreviewUrl = e.target.result;
        const container = document.getElementById('vehicleImageContainer');
        if (container) {
            container.innerHTML = `<img src="${jobImagePreviewUrl}" alt="Vehicle Image" style="width:100%;height:auto;display:block;object-fit:cover;min-height:180px;">`;
        }
        const removeBtn = document.getElementById('removeJobImageBtn');
        if (removeBtn) removeBtn.style.display = 'inline-block';
        showToast('Photo selected', 'success');
    };
    reader.onerror = function () {
        showToast('Could not read image file', 'error');
    };
    reader.readAsDataURL(file);
}

function removeJobImage() {
    jobImageFile = null;
    if (jobImagePreviewUrl && jobImagePreviewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(jobImagePreviewUrl);
    }
    jobImagePreviewUrl = null;

    const galleryInput = document.getElementById('jobVehicleImageInput');
    if (galleryInput) galleryInput.value = '';
    const cameraInput = document.getElementById('jobVehicleCameraInput');
    if (cameraInput) cameraInput.value = '';
    document.getElementById('vehicleImageContainer').innerHTML = `
        <div class="vehicle-placeholder">
            <span>Vehicle Image</span>
        </div>
    `;
    document.getElementById('removeJobImageBtn').style.display = 'none';
}

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
    removeVehicleImage(); // reset image each time
    loadCustomersForModal();
}

function closeVehicleModal(keepPreview = false) {
    document.getElementById('vehicleModal').classList.remove('active');
    if (!keepPreview) {
        removeVehicleImage();
    } else {
        // Only clear the modal UI, keep vehicleImagePreviewDataUrl / vehicleImageFile
        // for the job form (they will be cleared when job modal closes)
        const input = document.getElementById('vehicleImageInput');
        if (input) input.value = '';
        const preview = document.getElementById('vehicleImagePreview');
        if (preview) preview.style.display = 'none';
        const placeholder = document.getElementById('vehicleImagePlaceholder');
        if (placeholder) placeholder.style.display = 'block';
        const previewImg = document.getElementById('vehiclePreviewImg');
        if (previewImg) previewImg.src = '';
    }
}

function openCustomerModal() {
    const modal = document.getElementById('customerModal');
    modal.classList.add('active');
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.remove('active');
}

function closeJobModal() {
    document.getElementById('jobModal').classList.remove('active');
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchInput').value = '';
    selectedCustomer = null;
    selectedVehicle = null;
    selectedServices = [];

    // Clean up job image state
    if (jobImagePreviewUrl && jobImagePreviewUrl.startsWith('blob:')) {
        URL.revokeObjectURL(jobImagePreviewUrl);
    }
    jobImageFile = null;
    jobImagePreviewUrl = null;
    document.getElementById('jobVehicleImageInput').value = '';
    document.getElementById('removeJobImageBtn').style.display = 'none';
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
        // Use FormData so we can send the image file
        const formData = new FormData();
        formData.append('customer_id', customerId);
        formData.append('registration_number', registration);
        formData.append('make', make);
        formData.append('model', model);
        formData.append('category', category);
        if (vehicleImageFile) {
            formData.append('image', vehicleImageFile);
        }

        const response = await fetch('/vehicles', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                // Do NOT set Content-Type — browser sets multipart/form-data with boundary
            },
            body: formData
        });

        const data = await response.json();
        
        if (response.ok) {
            showToast('Vehicle created successfully!', 'success');

            // Prefer server URL, otherwise use the local preview we saved when picking the photo
            const imageForJob =
                resolveImageUrl(data.image_url || data.image || null) ||
                vehicleImagePreviewDataUrl ||
                null;

            // Capture customer name before closing modal (modal fields get cleared)
            const customerSelect = document.getElementById('modalVehicleCustomer');
            const customerName = customerSelect?.selectedOptions?.[0]?.text?.split(' - ')[0] || '';

            selectedVehicle = {
                vehicle_id: data.id,
                id: data.id,
                registration_number: data.registration_number,
                make: data.make,
                model: data.model,
                category: data.category || category,
                customer_id: data.customer_id,
                image: imageForJob
            };
            selectedCustomer = { id: customerId, name: customerName };

            // Clear modal UI but keep preview data for the job form
            closeVehicleModal(true);

            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('searchInput').value = registration;
            showJobForm();

            // Done with the create-vehicle preview
            vehicleImageFile = null;
            vehicleImagePreviewDataUrl = null;
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
        customer_id: vehicle.customer_id,
        image: resolveImageUrl(vehicle.image_url || vehicle.image || null)
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
        customer_id: vehicle.customer_id,
        image: resolveImageUrl(vehicle.image_url || vehicle.image || null)
    };

    showJobForm();
}

function showJobForm() {
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('jobModal').classList.add('active');

    // Reset job image edit state
    jobImageFile = null;
    jobImagePreviewUrl = resolveImageUrl(selectedVehicle?.image) || null;

    document.getElementById('customerDetails').innerHTML = `
        <p>
            <strong>Name:</strong>
            ${selectedCustomer?.name ?? ''}
        </p>
    `;

    const vehicleImageContainer = document.getElementById('vehicleImageContainer');
    const imageUrl = resolveImageUrl(selectedVehicle?.image);

    vehicleImageContainer.innerHTML = '';
    if (imageUrl) {
        const img = document.createElement('img');
        img.alt = 'Vehicle Image';
        img.style.width = '100%';
        img.style.height = 'auto';
        img.style.display = 'block';
        img.style.objectFit = 'cover';
        img.style.minHeight = '180px';
        img.onerror = function () {
            vehicleImageContainer.innerHTML = '<div class="vehicle-placeholder"><span>Vehicle Image</span></div>';
            const btn = document.getElementById('removeJobImageBtn');
            if (btn) btn.style.display = 'none';
        };
        img.onload = function () {
            const btn = document.getElementById('removeJobImageBtn');
            if (btn) btn.style.display = 'inline-block';
        };
        img.src = imageUrl;
        vehicleImageContainer.appendChild(img);
        document.getElementById('removeJobImageBtn').style.display = 'inline-block';
    } else {
        vehicleImageContainer.innerHTML = `
            <div class="vehicle-placeholder">
                <span>Vehicle Image</span>
            </div>
        `;
        document.getElementById('removeJobImageBtn').style.display = 'none';
    }

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
        // Use FormData so we can optionally send a new image
        const formData = new FormData();
        formData.append('customer_id', selectedCustomer.id);
        formData.append('vehicle_id', selectedVehicle.vehicle_id);
        selectedServices.forEach(id => formData.append('service_ids[]', id));
        formData.append('notes', document.getElementById('jobNotes').value);

        if (jobImageFile) {
            formData.append('vehicle_image', jobImageFile);
        }

        const response = await fetch('/reception/job', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                // Do NOT set Content-Type for FormData
            },
            body: formData
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
    margin: 0;
    padding: 40px 20px;
    min-height: 100vh;
    width: 100%;
    max-width: none;
    box-sizing: border-box;

    background: var(--reception-background);

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;

    position: relative;
    overflow-x: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.reception-container::before {
    content: '';
    position: fixed;
    inset: 0;

    background:
        radial-gradient(
            circle at 15% 20%,
            rgba(125, 211, 252, 0.10),
            transparent 35%
        ),
        radial-gradient(
            circle at 85% 75%,
            rgba(56, 189, 248, 0.08),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            rgba(3, 7, 18, 0.38) 0%,
            rgba(8, 15, 30, 0.42) 50%,
            rgba(4, 10, 22, 0.48) 100%
        );

    z-index: 0;
    pointer-events: none;
}

.reception-container > * {
    position: relative;
    z-index: 1;
}

.reception-header {
    text-align: center;
    margin-bottom: 44px;
    padding: 36px 20px 0;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.reception-header h1 {
    color: var(--text);
    margin-bottom: 10px;
    font-size: 44px;
    font-weight: 700;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

.reception-header p {
    color: var(--text-dim);
    font-size: 18px;
    margin: 0;
    font-weight: 400;
}

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

.search-section {
    margin-bottom: 30px;
    position: relative;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
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
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.14),
        rgba(255, 255, 255, 0.06)
    );
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

#toastContainer {
    position: fixed !important;
    top: 80px;
    right: 20px;
    z-index: 99999 !important; /* always above modals + blur overlay */
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

/* ---------- Image upload styles ---------- */
.image-upload-area {
    border: 1px dashed rgba(186, 230, 253, 0.35);
    border-radius: 14px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(125, 211, 252, 0.08), rgba(59, 130, 246, 0.04));
    text-align: center;
}

.image-upload-placeholder .upload-icon {
    font-size: 36px;
    margin-bottom: 8px;
}

.image-upload-placeholder p {
    color: var(--text);
    margin: 0 0 12px;
    font-size: 14px;
}

.upload-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-upload:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.btn-upload-secondary {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(186, 230, 253, 0.35);
    color: var(--text);
}

.btn-upload-secondary:hover {
    background: rgba(56, 189, 248, 0.2);
    box-shadow: none;
}

.upload-hint {
    display: block;
    margin-top: 10px;
    color: var(--text-dim);
    font-size: 12px;
}

.image-preview {
    position: relative;
    display: inline-block;
    max-width: 100%;
}

.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 12px;
    display: block;
    object-fit: cover;
}

.btn-remove-image {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    font-size: 18px;
    cursor: pointer;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.job-image-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.btn-change-image {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.12);
    color: var(--text);
    border: 1px solid rgba(186, 230, 253, 0.3);
    padding: 8px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.2s ease;
}

.btn-change-image:hover {
    background: rgba(56, 189, 248, 0.2);
}

.btn-remove-job-image {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border: 1px solid rgba(248, 113, 113, 0.4);
    padding: 8px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.btn-remove-job-image:hover {
    background: rgba(239, 68, 68, 0.35);
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
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.16),
        rgba(255, 255, 255, 0.07)
    );
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

.job-modal-content {
    max-width: 960px;
    --job-accent: #7dd3fc;
    --job-accent-deep: #38bdf8;
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

@media (prefers-reduced-motion: reduce) {
    * { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
}
</style>
@endsection