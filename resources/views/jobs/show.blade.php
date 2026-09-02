@extends('layouts.app')
@section('content')
<div class="job-detail-container">
    <!-- Header Section -->
    <div class="job-header">
        <div class="job-info">
            <h1 class="job-number">{{ $job->job_number }}</h1>
            <div class="job-meta">
                <span class="vehicle-reg">{{ $job->vehicle->registration_number }}</span>
                <span class="separator">·</span>
                <span class="customer-name">{{ $job->customer->full_name }}</span>
            </div>
        </div>
        <div class="status-badge status-{{ $job->status->getColor() }}">
            {{ $job->status->getLabel() }}
        </div>
    </div>

    <!-- Status Workflow -->
    <div class="status-workflow">
        <h3>Job Progress</h3>
        <div class="workflow-steps">
            @php
                $workflowSteps = [
                    'checked_in' => 'Checked In',
                    'inspection_completed' => 'Inspection',
                    'approved' => 'Approved',
                    'waiting_for_parts' => 'Parts',
                    'in_service' => 'In Service',
                    'quality_check' => 'Quality Check',
                    'ready_for_payment' => 'Ready',
                    'paid' => 'Paid',
                    'delivered' => 'Delivered'
                ];
                $currentIndex = array_search($job->status->value, array_keys($workflowSteps));
            @endphp
            @foreach($workflowSteps as $status => $label)
                @php
                    $stepIndex = array_search($status, array_keys($workflowSteps));
                    $isCompleted = $stepIndex < $currentIndex;
                    $isCurrent = $stepIndex === $currentIndex;
                    $isFuture = $stepIndex > $currentIndex;
                    $canTransition = $job->status->canTransitionTo(\App\Enums\JobStatus::from($status));
                @endphp
                <div class="workflow-step {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }} {{ $isFuture ? 'future' : '' }}">
                    @if($canTransition && !$isCurrent)
                        <button onclick="changeStatus('{{ $status }}')" class="step-action" title="Change to {{ $label }}">
                    @endif
                    <div class="step-indicator">
                        @if($isCompleted)
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        @elseif($isCurrent)
                            <div class="step-pulse"></div>
                        @else
                            <div class="step-number">{{ $stepIndex + 1 }}</div>
                        @endif
                    </div>
                    <span class="step-label">{{ $label }}</span>
                    @if($canTransition && !$isCurrent)
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            @if($job->status->value === \App\Enums\JobStatus::CHECKED_IN->value)
                <button onclick="changeStatus('inspection_pending')" class="action-btn action-yellow">Start Inspection</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Start Service</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::INSPECTION_PENDING->value)
                <button onclick="changeStatus('inspection_completed')" class="action-btn action-purple">Complete Inspection</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::INSPECTION_COMPLETED->value)
                <button onclick="changeStatus('customer_approval_pending')" class="action-btn action-yellow">Request Approval</button>
                <button onclick="changeStatus('approved')" class="action-btn action-blue">Approve</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::CUSTOMER_APPROVAL_PENDING->value)
                <button onclick="changeStatus('approved')" class="action-btn action-blue">Approve</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
            @elseif($job->status->value === \App\Enums\JobStatus::APPROVED->value)
                <button onclick="changeStatus('waiting_for_parts')" class="action-btn action-orange">Wait for Parts</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Start Service</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::WAITING_FOR_PARTS->value)
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Start Service</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
            @elseif($job->status->value === \App\Enums\JobStatus::IN_SERVICE->value)
                <button onclick="changeStatus('quality_check')" class="action-btn action-purple">Quality Check</button>
                <button onclick="changeStatus('on_hold')" class="action-btn action-orange">Put On Hold</button>
            @elseif($job->status->value === \App\Enums\JobStatus::QUALITY_CHECK->value)
                <button onclick="changeStatus('ready_for_payment')" class="action-btn action-cyan">Ready for Payment</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Back to Service</button>
            @elseif($job->status->value === \App\Enums\JobStatus::READY_FOR_PAYMENT->value)
                <div class="info-note">Job is ready for payment. Cashier will handle payment and delivery.</div>
            @elseif($job->status->value === \App\Enums\JobStatus::ON_HOLD->value)
                <button onclick="changeStatus('checked_in')" class="action-btn action-blue">Resume (Checked In)</button>
                <button onclick="changeStatus('inspection_completed')" class="action-btn action-purple">Resume (Inspection)</button>
                <button onclick="changeStatus('approved')" class="action-btn action-blue">Resume (Approved)</button>
                <button onclick="changeStatus('in_service')" class="action-btn action-blue">Resume (In Service)</button>
                <button onclick="changeStatus('waiting_for_parts')" class="action-btn action-orange">Resume (Wait Parts)</button>
                <button onclick="changeStatus('cancelled')" class="action-btn action-red">Cancel Job</button>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Vehicle & Customer Info -->
        <div class="info-panel">
            <h3>Vehicle & Customer</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Customer</label>
                    <a href="{{ route('customers.show',$job->customer) }}" class="info-value">{{ $job->customer->full_name }}</a>
                </div>
                <div class="info-item">
                    <label>Vehicle</label>
                    <a href="{{ route('vehicles.show',$job->vehicle) }}" class="info-value">{{ $job->vehicle->registration_number }}</a>
                </div>
                <div class="info-item">
                    <label>Make/Model</label>
                    <span class="info-value">{{ $job->vehicle->make }} {{ $job->vehicle->model }}</span>
                </div>
                <div class="info-item">
                    <label>Category</label>
                    <span class="info-value">{{ $job->vehicle->category ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <label>Priority</label>
                    <span class="info-value priority-{{ $job->priority }}">{{ ucfirst($job->priority) }}</span>
                </div>
                <div class="info-item">
                    <label>Checked In</label>
                    <span class="info-value">{{ $job->checked_in_at ? $job->checked_in_at->format('M d, Y H:i') : '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="info-panel">
            <h3>Services</h3>
            <div class="services-list">
                @forelse($job->services as $service)
                    <div class="service-item">
                        <div class="service-info">
                            <span class="service-name">{{ $service->name_snapshot }}</span>
                            <span class="service-status status-{{ $service->approval_status === 'approved' ? 'green' : ($service->approval_status === 'pending' ? 'yellow' : 'red') }}">
                                {{ ucfirst($service->approval_status) }}
                            </span>
                        </div>
                        <span class="service-price">Rs. {{ number_format($service->unit_price,2) }}</span>
                    </div>
                @empty
                    <div class="empty-state">No services added</div>
                @endforelse
            </div>
            <div class="add-service-form">
                <h4>Add Service</h4>
                <form class="inline-form" method="post" action="{{ route('jobs.additional-work',$job) }}">
                    @csrf
                    <select name="service_id">
                        <option value="">Select Service</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} — Rs. {{ number_format($s->base_price,2) }}</option>
                        @endforeach
                    </select>
                    <button type="submit">Add</button>
                </form>
            </div>
        </div>

        <!-- Parts -->
        <div class="info-panel">
            <h3>Parts Used</h3>
            <div class="parts-list">
                @forelse($job->parts as $part)
                    <div class="part-item">
                        <div class="part-info">
                            <span class="part-name">{{ $part->product->name }}</span>
                            <span class="part-quantity">{{ $part->quantity }} {{ $part->product->unit ?? 'unit' }}</span>
                        </div>
                        <span class="part-price">Rs. {{ number_format($part->unit_price * $part->quantity,2) }}</span>
                    </div>
                @empty
                    <div class="empty-state">No parts consumed</div>
                @endforelse
            </div>
            <div class="add-part-form">
                <h4>Consume Part</h4>
                <form class="inline-form" method="post" action="{{ route('jobs.consume-part',$job) }}">
                    @csrf
                    <select name="product_id" id="productSelect" onchange="updateSellingPrice(); updateStockInfo()">
                        <option value="">Select Product</option>
                        @foreach($products as $p)
                            @php
                                $inventory = \App\Models\Inventory::where('product_id', $p->id)->where('branch_id', $job->branch_id)->first();
                                $availableStock = $inventory ? $inventory->quantity : 0;
                            @endphp
                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-stock="{{ $availableStock }}">{{ $p->name }} (Stock: {{ $availableStock }})</option>
                        @endforeach
                    </select>
                    <input name="quantity" type="number" step=".001" value="1" min="0.001" placeholder="Qty">
                    <input name="unit_price" type="number" step=".01" placeholder="Price" id="unitPriceInput">
                    <button type="submit">Add</button>
                </form>
                <div id="stockWarning" class="stock-warning"></div>
            </div>
        </div>

        <!-- Notes -->
        <div class="info-panel">
            <h3>Notes</h3>
            <div class="notes-content">
                {{ $job->notes ?: 'No notes added' }}
            </div>
        </div>
    </div>

    <!-- Invoice Link -->
    @if($job->invoice)
        <div class="invoice-link">
            <a href="{{ route('invoices.show',$job->invoice) }}" class="btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                View Invoice
            </a>
        </div>
    @endif
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Job Status</h3>
            <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to change the status to <strong id="newStatusLabel"></strong>?</p>
            
            <div class="custom-notes-section">
                <label>Additional Notes (Optional)</label>
                <textarea id="customNotes" rows="3" placeholder="Add any additional notes for the customer (e.g., specific issues found, recommendations, etc.)"></textarea>
                <small>This will be included in the WhatsApp message</small>
            </div>

            <div class="whatsapp-section">
                @if($job->customer->whatsapp_number)
                    <label class="whatsapp-toggle">
                        <input type="checkbox" id="sendWhatsapp" checked>
                        <span class="slider"></span>
                        <span class="toggle-label">Send WhatsApp update to customer ({{ $job->customer->whatsapp_number }})</span>
                    </label>
                @else
                    <div class="whatsapp-number-input">
                        <label>Customer doesn't have WhatsApp number. Add one to send update:</label>
                        <input type="text" id="customerWhatsappNumber" value="+94" placeholder="+94XXXXXXXXX" oninput="toggleWhatsappCheckbox()" onfocus="this.select()">
                        <label class="whatsapp-toggle" style="margin-top: 8px;">
                            <input type="checkbox" id="sendWhatsapp" disabled>
                            <span class="slider"></span>
                            <span class="toggle-label">Send WhatsApp update to customer</span>
                        </label>
                    </div>
                @endif
            </div>

            <form id="statusForm" method="post" action="{{ route('jobs.status',$job) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" id="statusInput">
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Change</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.job-detail-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px;
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding: 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    color: white;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.job-info h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
}

.job-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    opacity: 0.9;
}

.separator {
    opacity: 0.5;
}

.status-badge {
    padding: 10px 20px;
    border-radius: 24px;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-blue { background: #3b82f6; color: white; }
.status-green { background: #10b981; color: white; }
.status-yellow { background: #f59e0b; color: white; }
.status-orange { background: #f97316; color: white; }
.status-purple { background: #8b5cf6; color: white; }
.status-red { background: #ef4444; color: white; }
.status-cyan { background: #06b6d4; color: white; }

.status-workflow {
    background: white;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.status-workflow h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #1f2937;
}

.workflow-steps {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
}

.workflow-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 100px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.workflow-step .step-action {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.step-indicator {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.workflow-step.completed .step-indicator {
    background: #10b981;
    color: white;
}

.workflow-step.current .step-indicator {
    background: #3b82f6;
    color: white;
}

.workflow-step.future .step-indicator {
    background: #e5e7eb;
    color: #9ca3af;
}

.step-pulse {
    width: 16px;
    height: 16px;
    background: #3b82f6;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.7; }
}

.step-number {
    color: inherit;
}

.step-label {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    color: #4b5563;
}

.workflow-step.completed .step-label {
    color: #10b981;
}

.workflow-step.current .step-label {
    color: #3b82f6;
    font-weight: 600;
}

.quick-actions {
    background: white;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.quick-actions h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
    color: #1f2937;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.action-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.action-green { background: linear-gradient(135deg, #10b981, #059669); }
.action-yellow { background: linear-gradient(135deg, #f59e0b, #d97706); }
.action-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.action-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.action-cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.action-red { background: linear-gradient(135deg, #ef4444, #dc2626); }

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.info-panel {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.info-panel h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #1f2937;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 12px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
}

.info-value {
    font-size: 15px;
    color: #1f2937;
    font-weight: 500;
}

.info-value a {
    color: #3b82f6;
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

.priority-high { color: #ef4444; }
.priority-medium { color: #f59e0b; }
.priority-low { color: #10b981; }

.services-list, .parts-list {
    margin-bottom: 20px;
}

.service-item, .part-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 8px;
}

.service-info, .part-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.service-name, .part-name {
    font-weight: 600;
    color: #1f2937;
}

.service-status {
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
}

.service-status.status-green { background: #d1fae5; color: #065f46; }
.service-status.status-yellow { background: #fef3c7; color: #92400e; }
.service-status.status-red { background: #fee2e2; color: #991b1b; }

.service-price, .part-price {
    font-weight: 600;
    color: #3b82f6;
}

.part-quantity {
    font-size: 13px;
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 24px;
    color: #9ca3af;
    font-style: italic;
}

.add-service-form, .add-part-form {
    padding-top: 16px;
    border-top: 2px solid #f3f4f6;
}

.add-service-form h4, .add-part-form h4 {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #6b7280;
}

.inline-form {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.inline-form select,
.inline-form input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.inline-form select {
    flex: 1;
    min-width: 150px;
}

.inline-form input {
    width: 80px;
}

.inline-form button {
    padding: 8px 16px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
}

.inline-form button:hover {
    background: #2563eb;
}

.stock-warning {
    margin-top: 8px;
    padding: 8px 12px;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 6px;
    color: #dc2626;
    font-size: 13px;
    display: none;
}

.notes-content {
    color: #4b5563;
    line-height: 1.6;
    white-space: pre-wrap;
}

.invoice-link {
    text-align: center;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    padding: 10px 20px;
    background: #e5e7eb;
    color: #374151;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-secondary:hover {
    background: #d1d5db;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #9ca3af;
}

.modal-close:hover {
    color: #1f2937;
}

.modal-body p {
    margin: 0 0 20px 0;
    color: #4b5563;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.image-upload-section {
    margin: 20px 0;
}

.image-upload-section label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.image-upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.image-upload-area:hover {
    border-color: #3b82f6;
    background: #f9fafb;
}

.upload-placeholder {
    cursor: pointer;
}

.upload-placeholder svg {
    color: #9ca3af;
    margin-bottom: 8px;
}

.upload-placeholder span {
    display: block;
    color: #374151;
    font-weight: 500;
    margin-bottom: 4px;
}

.upload-placeholder small {
    display: block;
    color: #6b7280;
    font-size: 12px;
}

.image-preview {
    position: relative;
    display: inline-block;
}

.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    object-fit: cover;
}

.remove-image-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 28px;
    height: 28px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.whatsapp-section {
    margin: 16px 0;
    padding: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
}

.whatsapp-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.whatsapp-toggle input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: #22c55e;
}

.whatsapp-toggle .toggle-label {
    color: #166534;
    font-weight: 500;
    font-size: 14px;
}

.whatsapp-number-input {
    padding: 12px;
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 8px;
}

.whatsapp-number-input label {
    display: block;
    font-weight: 600;
    color: #92400e;
    margin-bottom: 8px;
    font-size: 13px;
}

.whatsapp-number-input input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.custom-notes-section {
    margin: 20px 0;
}

.custom-notes-section label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.custom-notes-section textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
}

.custom-notes-section small {
    display: block;
    margin-top: 4px;
    color: #6b7280;
    font-size: 12px;
}

@media (max-width: 768px) {
    .job-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .job-meta {
        justify-content: center;
    }

    .workflow-steps {
        flex-wrap: wrap;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .action-btn {
        width: 100%;
    }
}
</style>

<script>
function changeStatus(status) {
    const statusLabels = {
        'checked_in': 'Checked In',
        'inspection_pending': 'Inspection Pending',
        'inspection_completed': 'Inspection Completed',
        'customer_approval_pending': 'Waiting Approval',
        'approved': 'Approved',
        'waiting_for_parts': 'Waiting for Parts',
        'in_service': 'In Service',
        'quality_check': 'Quality Check',
        'ready_for_payment': 'Ready for Payment',
        'paid': 'Paid',
        'delivered': 'Delivered',
        'cancelled': 'Cancelled',
        'on_hold': 'On Hold'
    };

    document.getElementById('newStatusLabel').textContent = statusLabels[status] || status;
    document.getElementById('statusInput').value = status;
    document.getElementById('statusModal').classList.add('active');
    
    // Reset WhatsApp number input if exists
    const whatsappInput = document.getElementById('customerWhatsappNumber');
    if (whatsappInput) {
        whatsappInput.value = '+94';
        document.getElementById('sendWhatsapp').disabled = true;
        document.getElementById('sendWhatsapp').checked = false;
    }
    
    // Reset custom notes
    const notesInput = document.getElementById('customNotes');
    if (notesInput) {
        notesInput.value = '';
    }
}

function toggleWhatsappCheckbox() {
    const whatsappInput = document.getElementById('customerWhatsappNumber');
    const whatsappCheckbox = document.getElementById('sendWhatsapp');
    
    if (whatsappInput && whatsappCheckbox) {
        if (whatsappInput.value.trim().length > 5) {
            whatsappCheckbox.disabled = false;
            whatsappCheckbox.checked = true;
        } else {
            whatsappCheckbox.disabled = true;
            whatsappCheckbox.checked = false;
        }
    }
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.remove('active');
    
    // Reset WhatsApp number input if exists
    const whatsappInput = document.getElementById('customerWhatsappNumber');
    if (whatsappInput) {
        whatsappInput.value = '+94';
        document.getElementById('sendWhatsapp').disabled = true;
        document.getElementById('sendWhatsapp').checked = false;
    }
    
    // Reset custom notes
    const notesInput = document.getElementById('customNotes');
    if (notesInput) {
        notesInput.value = '';
    }
}

// Add form data before submission
document.getElementById('statusForm').addEventListener('submit', function(e) {
    const formData = new FormData(this);
    
    formData.append('send_whatsapp', document.getElementById('sendWhatsapp').checked ? '1' : '0');
    
    // Add WhatsApp number if provided
    const whatsappInput = document.getElementById('customerWhatsappNumber');
    if (whatsappInput && whatsappInput.value.trim() && whatsappInput.value.trim() !== '+94') {
        formData.append('customer_whatsapp_number', whatsappInput.value.trim());
    }
    
    // Add custom notes if provided
    const notesInput = document.getElementById('customNotes');
    if (notesInput && notesInput.value.trim()) {
        formData.append('custom_notes', notesInput.value.trim());
    }
    
    // Replace form submission with FormData
    e.preventDefault();
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // If WhatsApp URL is provided, open it in new tab
            if (data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
                // Reload page after a delay to allow WhatsApp to open
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                // Reload immediately if no WhatsApp
                window.location.reload();
            }
        } else {
            alert('Error updating status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
});

function resumeFromHold() {
    const currentStatus = '{{ $job->status->value }}';
    let resumeTo = 'checked_in';

    if (currentStatus === 'on_hold') {
        // Default to checked_in, user can change if needed
        resumeTo = 'checked_in';
    }

    changeStatus(resumeTo);
}

function updateSellingPrice() {
    const select = document.getElementById('productSelect');
    const selectedOption = select.options[select.selectedIndex];
    const priceInput = document.getElementById('unitPriceInput');
    if (selectedOption.value) {
        priceInput.value = selectedOption.getAttribute('data-price');
    } else {
        priceInput.value = '';
    }
}

function updateStockInfo() {
    const select = document.getElementById('productSelect');
    const selectedOption = select.options[select.selectedIndex];
    const stockWarning = document.getElementById('stockWarning');
    const quantityInput = document.querySelector('input[name="quantity"]');

    if (selectedOption.value) {
        const availableStock = parseFloat(selectedOption.getAttribute('data-stock'));
        const requestedQty = parseFloat(quantityInput.value) || 1;

        if (availableStock === 0) {
            stockWarning.textContent = '⚠️ This product is out of stock!';
            stockWarning.style.display = 'block';
        } else if (availableStock < requestedQty) {
            stockWarning.textContent = '⚠️ Insufficient stock. Available: ' + availableStock + ', Requested: ' + requestedQty;
            stockWarning.style.display = 'block';
        } else {
            stockWarning.style.display = 'none';
        }
    } else {
        stockWarning.style.display = 'none';
    }
}

document.querySelector('input[name="quantity"]').addEventListener('input', updateStockInfo);

// Close modal on outside click
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});
</script>
@endsection
