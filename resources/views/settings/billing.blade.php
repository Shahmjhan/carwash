@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Settings</h1>
        <p>Configure your billing and reception settings</p>
    </div>
</div>

<div class="settings-tabs">
    <button class="tab-btn active" onclick="showTab('reception')" id="tab-reception">Reception Settings</button>
    <button class="tab-btn" onclick="showTab('billing')" id="tab-billing">Billing Settings</button>
</div>

<!-- Reception Settings Tab -->
<div class="tab-content active" id="content-reception">
    <div class="panel">
        <form method="post" action="{{ route('settings.reception.update') }}" enctype="multipart/form-data" id="reception-form">
            @csrf
        
            <h2>🎨 Background Appearance</h2>
            <div class="bg-type-selector">
                <label class="bg-type-option">
                    <input type="radio" name="background_type" value="image" {{ ($settings['background_type'] ?? 'image') === 'image' ? 'checked' : '' }} id="bg-type-image" onchange="toggleBackgroundType()">
                    <span class="bg-type-card">
                        <span class="bg-type-icon">🖼️</span>
                        <span class="bg-type-label">Use Image</span>
                        <span class="bg-type-desc">Upload a custom background image</span>
                    </span>
                </label>
                <label class="bg-type-option">
                    <input type="radio" name="background_type" value="color" {{ ($settings['background_type'] ?? 'image') === 'color' ? 'checked' : '' }} id="bg-type-color" onchange="toggleBackgroundType()">
                    <span class="bg-type-card">
                        <span class="bg-type-icon">🎨</span>
                        <span class="bg-type-label">Use Color</span>
                        <span class="bg-type-desc">Choose a solid or gradient color</span>
                    </span>
                </label>
            </div>

            <div class="bg-upload-section" id="bg-upload-section" {{ ($settings['background_type'] ?? 'image') === 'color' ? 'style="display:none;"' : '' }}>
                <h3>Background Image</h3>
                <div class="upload-zone" id="bg-dropzone">
                    @if($settings['reception_background_image'] ?? null)
                        <div class="current-image">
                            <img src="{{ asset($settings['reception_background_image']) }}" alt="Current Background">
                            <button type="button" onclick="removeBackground()" class="remove-btn">Remove Image</button>
                        </div>
                    @else
                        <div class="upload-placeholder">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">Click to upload or drag and drop</span>
                            <span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 5MB)</span>
                        </div>
                    @endif
                    <input type="file" name="reception_background" id="bg-input" accept="image/*" style="display: none;">
                </div>
                <input type="hidden" name="reception_background_image" value="{{ $settings['reception_background_image'] ?? '' }}" id="bg-path-hidden">
            </div>

            <div class="bg-color-section" id="bg-color-section" {{ ($settings['background_type'] ?? 'image') === 'image' ? 'style="display:none;"' : '' }}>
                <h3>Background Color</h3>
                <div class="color-presets">
                    @foreach([
                        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' => 'Purple',
                        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' => 'Pink',
                        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' => 'Blue',
                        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' => 'Green',
                        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' => 'Orange',
                        'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)' => 'Pastel',
                        'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)' => 'Dark Blue',
                        'linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%)' => 'Black',
                        'linear-gradient(135deg, #2d3436 0%, #636e72 100%)' => 'Gray',
                        '#667eea' => 'Purple Solid',
                        '#4facfe' => 'Blue Solid',
                        '#43e97b' => 'Green Solid',
                        '#fa709a' => 'Pink Solid',
                        '#fee140' => 'Yellow Solid',
                    ] as $color => $name)
                    <label class="color-option">
                        <input type="radio" name="background_color" value="{{ $color }}" {{ ($settings['background_color'] ?? '') === $color ? 'checked' : '' }} onchange="updateColorPreview()">
                        <div class="color-swatch" style="background: {{ $color }};">
                            <span class="color-check">✓</span>
                        </div>
                        <span class="color-name">{{ $name }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="custom-color-section">
                    <label>Custom Color</label>
                    <div class="color-picker-wrapper">
                        <div class="color-preview-box" id="custom-color-preview" style="background: {{ $settings['custom_background_color'] ?? '#667eea' }};"></div>
                        <input type="color" name="custom_background_color" value="{{ $settings['custom_background_color'] ?? '#667eea' }}" id="custom-bg-color" onchange="updateCustomColorPreview()">
                        <input type="text" class="color-hex-input" value="{{ $settings['custom_background_color'] ?? '#667eea' }}" id="color-hex-value" oninput="updateColorFromHex()">
                    </div>
                </div>
            </div>

            <h2>👁️ Live Preview</h2>
            <div class="preview-wrapper">
                <div id="reception-preview" class="reception-preview-full">
                    @if(($settings['background_type'] ?? 'image') === 'color' && ($settings['background_color'] ?? ''))
                        <div class="preview-bg" style="background: {{ $settings['background_color'] }};"></div>
                    @elseif(($settings['background_type'] ?? 'image') === 'color' && ($settings['custom_background_color'] ?? ''))
                        <div class="preview-bg" style="background: {{ $settings['custom_background_color'] }};"></div>
                    @elseif($settings['reception_background_image'] ?? null)
                        <div class="preview-bg" style="background-image: url('{{ asset($settings['reception_background_image']) }}');"></div>
                    @else
                        <div class="preview-bg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                    @endif
                    <div class="preview-content">
                        <div class="reception-nav">
                            <button class="nav-toggle">☰</button>
                            <div class="nav-menu">
                                <a href="#">Dashboard</a>
                                <a href="#">Job Cards</a>
                                <a href="#">Customers</a>
                                <a href="#">Settings</a>
                            </div>
                        </div>
                        <div class="reception-header">
                            <h1>Vehicle Check-in</h1>
                            <p>Enter vehicle registration number to begin</p>
                        </div>
                        <div class="search-section">
                            <div class="search-box">
                                <input type="text" placeholder="Vehicle registration number..." disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 40px;">
                <button class="primary">Save Reception Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Billing Settings Tab -->
<div class="tab-content" id="content-billing">
    <div class="grid2">
        <div class="panel">
            <form method="post" action="{{ route('settings.billing.update') }}" enctype="multipart/form-data" id="billing-form">
                @csrf
                
                <h2>🏢 Company Information</h2>
                <div class="grid2">
                    <label>
                        Company Name
                        <input type="text" name="company_name" value="{{ $settings['company_name'] }}" required id="company-name">
                    </label>
                    <label>
                        Tax ID
                        <input type="text" name="tax_id" value="{{ $settings['tax_id'] }}" id="tax-id">
                    </label>
                </div>
                <label>
                    Address
                    <textarea name="address" rows="2" id="address">{{ $settings['address'] }}</textarea>
                </label>
                <div class="grid3">
                    <label>
                        Phone
                        <input type="text" name="phone" value="{{ $settings['phone'] }}" id="phone">
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" value="{{ $settings['email'] }}" id="email">
                    </label>
                    <label>
                        Website
                        <input type="url" name="website" value="{{ $settings['website'] }}" id="website">
                    </label>
                </div>

                <h2>📄 Invoice & Receipt Settings</h2>
                <div class="grid2">
                    <label>
                        Invoice Prefix
                        <input type="text" name="invoice_prefix" value="{{ $settings['invoice_prefix'] }}" required id="invoice-prefix">
                    </label>
                    <label>
                        Receipt Prefix
                        <input type="text" name="receipt_prefix" value="{{ $settings['receipt_prefix'] }}" required id="receipt-prefix">
                    </label>
                </div>

                <h2>🖨️ Print Format</h2>
                <div class="grid2">
                    <label>
                        Default Format
                        <select name="default_format" id="default-format">
                            <option value="a4" {{ $settings['default_format'] === 'a4' ? 'selected' : '' }}>A4 (Professional)</option>
                            <option value="thermal" {{ $settings['default_format'] === 'thermal' ? 'selected' : '' }}>80mm Thermal Receipt</option>
                        </select>
                    </label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="a4_enabled" {{ $settings['a4_enabled'] ? 'checked' : '' }} id="a4-enabled">
                            Enable A4 Printing
                        </label>
                        <label>
                            <input type="checkbox" name="thermal_enabled" {{ $settings['thermal_enabled'] ? 'checked' : '' }} id="thermal-enabled">
                            Enable Thermal Printing
                        </label>
                    </div>
                </div>

                <h2>📝 Document Content</h2>
                <label>
                    Footer Text
                    <textarea name="footer_text" rows="2" id="footer-text">{{ $settings['footer_text'] }}</textarea>
                </label>
                <label>
                    Terms & Conditions
                    <textarea name="terms_conditions" rows="4" id="terms-conditions">{{ $settings['terms_conditions'] }}</textarea>
                </label>

                <h2>🖼️ Logo</h2>
                <div class="upload-zone" id="logo-dropzone">
                    @if($settings['logo_path'])
                        <div class="current-image">
                            <img src="{{ asset($settings['logo_path']) }}" alt="Current Logo" id="current-logo">
                            <button type="button" onclick="removeLogo()" class="remove-btn">Remove Logo</button>
                        </div>
                    @else
                        <div class="upload-placeholder">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">Click to upload or drag and drop</span>
                            <span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 2MB)</span>
                        </div>
                    @endif
                    <input type="file" name="logo" id="logo-input" accept="image/*" style="display: none;">
                </div>
                <input type="hidden" name="logo_path" value="{{ $settings['logo_path'] }}" id="logo-path-hidden">
                
                <label style="margin-top: 15px;">
                    Logo Size (A4 Invoice)
                    <input type="range" name="logo_size_a4" min="30" max="150" value="{{ $settings['logo_size_a4'] ?? 60 }}" id="logo-size-a4" style="width: 100%;">
                    <small>Current: <span id="logo-size-a4-value">{{ $settings['logo_size_a4'] ?? 60 }}</span>px</small>
                </label>
                
                <label>
                    Logo Size (Thermal Receipt)
                    <input type="range" name="logo_size_thermal" min="20" max="80" value="{{ $settings['logo_size_thermal'] ?? 40 }}" id="logo-size-thermal" style="width: 100%;">
                    <small>Current: <span id="logo-size-thermal-value">{{ $settings['logo_size_thermal'] ?? 40 }}</span>px</small>
                </label>

                <script>
                    // Logo dropzone
                    const dropzone = document.getElementById('logo-dropzone');
                    const fileInput = document.getElementById('logo-input');
                    const logoPathHidden = document.getElementById('logo-path-hidden');

                    dropzone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropzone.style.borderColor = '#3498db';
                        dropzone.style.backgroundColor = '#f0f8ff';
                    });

                    dropzone.addEventListener('dragleave', (e) => {
                        e.preventDefault();
                        dropzone.style.borderColor = '#ccc';
                        dropzone.style.backgroundColor = 'transparent';
                    });

                    dropzone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropzone.style.borderColor = '#ccc';
                        dropzone.style.backgroundColor = 'transparent';
                        
                        const files = e.dataTransfer.files;
                        if (files.length > 0) {
                            fileInput.files = files;
                            previewImage(files[0], dropzone);
                            updateLogoPreview(files[0]);
                        }
                    });

                    fileInput.addEventListener('change', (e) => {
                        if (e.target.files.length > 0) {
                            previewImage(e.target.files[0], dropzone);
                            updateLogoPreview(e.target.files[0]);
                        }
                    });

                    function removeLogo() {
                        logoPathHidden.value = '';
                        fileInput.value = '';
                        const img = dropzone.querySelector('img');
                        if (img) img.remove();
                        const removeBtn = dropzone.querySelector('button');
                        if (removeBtn) removeBtn.remove();
                        
                        document.querySelectorAll('.preview-logo').forEach(el => el.style.display = 'none');
                    }

                    function previewImage(file, dropzone) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const img = dropzone.querySelector('img');
                            if (img) {
                                img.src = e.target.result;
                            } else {
                                const newImg = document.createElement('img');
                                newImg.src = e.target.result;
                                newImg.style.maxHeight = '100px';
                                newImg.style.marginBottom = '10px';
                                dropzone.insertBefore(newImg, dropzone.firstChild);
                            }
                        };
                        reader.readAsDataURL(file);
                    }

                    function updateLogoPreview(file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            document.querySelectorAll('.preview-logo').forEach(el => {
                                el.src = e.target.result;
                                el.style.display = 'block';
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                </script>

                <div style="margin-top: 40px;">
                    <button class="primary">Save Billing Settings</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <h2>👁️ Live Preview</h2>
            <div class="preview-toggle">
                <button type="button" onclick="showPreview('a4')" id="btn-a4" class="secondary">A4 Format</button>
                <button type="button" onclick="showPreview('thermal')" id="btn-thermal" class="secondary">Thermal Format</button>
            </div>

            <!-- A4 Preview -->
            <div id="preview-a4" class="preview-container" style="display: {{ $settings['default_format'] === 'a4' ? 'block' : 'none' }};">
                <div style="background: white; padding: 20px; border: 1px solid #ddd; max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; font-size: 12px; color: #333;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px;">
                        <div style="flex: 1;">
                            @if($settings['logo_path'])
                                <img src="{{ asset($settings['logo_path']) }}" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_a4'] ?? 60 }}px; margin-bottom: 10px;">
                            @else
                                <img src="" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_a4'] ?? 60 }}px; margin-bottom: 10px; display: none;">
                            @endif
                            <h1 style="font-size: 20px; margin: 0 0 8px 0; color: #2c3e50;" id="preview-company-name-a4">{{ $settings['company_name'] }}</h1>
                            @if($settings['address'])<p style="margin: 3px 0; color: #666; font-size: 11px;" id="preview-address-a4">{{ $settings['address'] }}</p>@endif
                            @if($settings['phone'])<p style="margin: 3px 0; color: #666; font-size: 11px;" id="preview-phone-a4">Phone: {{ $settings['phone'] }}</p>@endif
                            @if($settings['email'])<p style="margin: 3px 0; color: #666; font-size: 11px;" id="preview-email-a4">Email: {{ $settings['email'] }}</p>@endif
                            @if($settings['tax_id'])<p style="margin: 3px 0; color: #666; font-size: 11px;">Tax ID: {{ $settings['tax_id'] }}</p>@endif
                        </div>
                        <div style="text-align: right;">
                            <h2 style="font-size: 18px; margin: 0 0 12px 0; color: #e74c3c;">INVOICE</h2>
                            <p style="margin: 4px 0; font-size: 11px;"><strong>Invoice #:</strong> <span id="preview-invoice-prefix-a4">{{ $settings['invoice_prefix'] }}-001</span></p>
                            <p style="margin: 4px 0; font-size: 11px;"><strong>Date:</strong> {{ now()->format('d M Y') }}</p>
                            <p style="margin: 4px 0; font-size: 11px;"><strong>Due Date:</strong> {{ now()->addDays(30)->format('d M Y') }}</p>
                            <p style="margin: 4px 0; font-size: 11px;"><strong>Status:</strong> <span style="color: green; font-weight: bold;">Paid</span></p>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <h3 style="font-size: 13px; margin: 0 0 8px 0; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px;">BILL TO</h3>
                            <p style="margin: 4px 0; color: #666; font-size: 11px;"><strong>John Doe</strong></p>
                            <p style="margin: 4px 0; color: #666; font-size: 11px;">Phone: 0771234567</p>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="font-size: 13px; margin: 0 0 8px 0; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px;">VEHICLE DETAILS</h3>
                            <p style="margin: 4px 0; color: #666; font-size: 11px;"><strong>ABC-1234</strong></p>
                            <p style="margin: 4px 0; color: #666; font-size: 11px;">Toyota Corolla - White</p>
                        </div>
                    </div>

                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold; color: #2c3e50;">Description</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: center; font-weight: bold; color: #2c3e50;">Qty</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #2c3e50;">Unit Price</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #2c3e50;">Tax</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #2c3e50;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background: #f9f9f9;">
                                <td style="border: 1px solid #ddd; padding: 8px;">Full Car Wash Service</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">1</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">500.00</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">50.00</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">550.00</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;">Interior Detailing</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">1</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">300.00</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">30.00</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">330.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="width: 250px; margin-left: auto;">
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #eee; font-size: 11px;">
                            <span>Subtotal:</span>
                            <span>800.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #eee; font-size: 11px;">
                            <span>Tax:</span>
                            <span>80.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid #333; border-bottom: none; margin-top: 8px; font-size: 13px; font-weight: bold; color: #2c3e50;">
                            <span>Total Due:</span>
                            <span>880.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #eee; font-size: 11px;">
                            <span>Amount Received:</span>
                            <span>880.00</span>
                        </div>
                    </div>

                    @if($settings['terms_conditions'])
                        <div style="margin-top: 15px; padding: 12px; background: #f8f9fa; border-left: 3px solid #e74c3c; font-size: 10px;">
                            <strong>Terms & Conditions:</strong>
                            <p style="margin: 4px 0;" id="preview-terms-a4">{{ $settings['terms_conditions'] }}</p>
                        </div>
                    @endif

                    <div style="margin-top: 15px; padding: 12px; background: #f0f8ff; border-left: 3px solid #3498db; font-size: 10px;">
                        <strong>Payment Information:</strong>
                        <p style="margin: 4px 0;">We accept Cash, Card, and Bank Transfer</p>
                        @if($settings['phone'])<p style="margin: 4px 0;">For inquiries: {{ $settings['phone'] }}</p>@endif
                    </div>

                    <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 10px;">
                        <p style="margin: 4px 0;" id="preview-footer-a4">{{ $settings['footer_text'] }}</p>
                        <p style="margin: 4px 0;">Generated on {{ now()->format('d M Y H:i') }} · {{ $settings['company_name'] }}</p>
                        <p style="font-weight: bold; margin-top: 10px;">Powered by Vellix Global - 0773208478</p>
                    </div>
                </div>
            </div>

            <!-- Thermal Preview -->
            <div id="preview-thermal" class="preview-container" style="display: {{ $settings['default_format'] === 'thermal' ? 'block' : 'none' }};">
                <div style="background: white; padding: 10px; border: 1px solid #ddd; max-width: 280px; margin: 0 auto; font-family: 'Courier New', monospace; font-size: 12px; font-weight: bold;">
                    <div style="text-align: center; margin-bottom: 8px;">
                        @if($settings['logo_path'])
                            <img src="{{ asset($settings['logo_path']) }}" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_thermal'] ?? 40 }}px; margin-bottom: 4px;">
                        @else
                            <img src="" alt="Logo" class="preview-logo" style="max-height: {{ $settings['logo_size_thermal'] ?? 40 }}px; margin-bottom: 4px; display: none;">
                        @endif
                        <div style="font-size: 16px; font-weight: 900; margin: 4px 0;" id="preview-company-name-thermal">{{ $settings['company_name'] }}</div>
                        @if($settings['address'])<div style="font-size: 11px; margin: 2px 0;" id="preview-address-thermal">{{ $settings['address'] }}</div>@endif
                        @if($settings['phone'])<div style="font-size: 11px; margin: 2px 0;">Tel: {{ $settings['phone'] }}</div>@endif
                        @if($settings['tax_id'])<div style="font-size: 11px; margin: 2px 0;">Tax ID: {{ $settings['tax_id'] }}</div>@endif
                    </div>
                    <div style="border-top: 2px dashed #000; margin: 8px 0;"></div>
                    <div style="text-align: center; margin-bottom: 8px;">
                        <div style="font-size: 14px; font-weight: 900;">{{ $settings['invoice_prefix'] }}-001</div>
                        <div style="font-size: 11px;">{{ now()->format('d/m/Y H:i') }}</div>
                    </div>
                    <div style="margin-bottom: 6px; font-size: 11px;">
                        <strong>{{ $settings['company_name'] }}</strong>
                    </div>
                    <div style="margin-bottom: 6px; font-size: 11px;">
                        <strong>Customer:</strong> John Doe
                    </div>
                    <div style="margin-bottom: 6px; font-size: 11px;">
                        <strong>Vehicle:</strong> ABC-1234
                    </div>
                    <div style="border-top: 2px dashed #000; margin: 8px 0;"></div>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 11px;">
                        <tr>
                            <td style="text-align: left; padding: 2px 0;">Full Car Wash Service</td>
                            <td style="text-align: center; padding: 2px 0;">1</td>
                            <td style="text-align: right; padding: 2px 0;">500</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 2px 0;">Interior Detailing</td>
                            <td style="text-align: center; padding: 2px 0;">1</td>
                            <td style="text-align: right; padding: 2px 0;">300</td>
                        </tr>
                    </table>
                    <div style="border-top: 2px dashed #000; margin: 8px 0;"></div>
                    <div style="text-align: right; margin-bottom: 4px; font-size: 11px;">
                        Subtotal: 800
                    </div>
                    <div style="text-align: right; margin-bottom: 4px; font-size: 11px;">
                        Tax: 80
                    </div>
                    <div style="text-align: right; margin-bottom: 6px; font-size: 13px; font-weight: 900; border-top: 2px solid #000; padding-top: 4px;">
                        TOTAL: Rs. 880
                    </div>
                    <div style="text-align: right; margin-bottom: 4px; font-size: 11px;">
                        Received: Rs. 880
                    </div>
                    <div style="text-align: right; margin-bottom: 4px; font-size: 11px;">
                        Return: Rs. 0
                    </div>
                    <div style="border-top: 2px dashed #000; margin: 8px 0;"></div>
                    <div style="text-align: center; margin-top: 8px; font-size: 14px; font-weight: 900;">
                        PAID
                    </div>
                    <div style="border-top: 2px dashed #000; margin: 8px 0;"></div>
                    <div style="text-align: center; margin-top: 8px; font-size: 10px;">
                        <div style="margin: 2px 0;" id="preview-footer-thermal">{{ $settings['footer_text'] }}</div>
                        <div style="margin: 2px 0;">{{ now()->format('d/m/Y H:i') }}</div>
                        <div style="margin: 2px 0;">Thank you for your business!</div>
                        <div style="margin: 4px 0 0 0; font-weight: bold;">Powered by Vellix Global - 0773208478</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching
    function showTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('content-' + tab).classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }

    // Reception settings scripts
    const bgDropzone = document.getElementById('bg-dropzone');
    const bgInput = document.getElementById('bg-input');
    const bgPathHidden = document.getElementById('bg-path-hidden');
    const previewContainer = document.getElementById('reception-preview');

    bgDropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        bgDropzone.style.borderColor = '#3498db';
        bgDropzone.style.backgroundColor = '#f0f8ff';
    });

    bgDropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        bgDropzone.style.borderColor = '#ccc';
        bgDropzone.style.backgroundColor = 'transparent';
    });

    bgDropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        bgDropzone.style.borderColor = '#ccc';
        bgDropzone.style.backgroundColor = 'transparent';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            bgInput.files = files;
            previewImage(files[0], bgDropzone);
            updateImagePreview(files[0]);
        }
    });

    bgDropzone.addEventListener('click', () => {
        bgInput.click();
    });

    bgInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            previewImage(e.target.files[0], bgDropzone);
            updateImagePreview(e.target.files[0]);
        }
    });

    function removeBackground() {
        bgPathHidden.value = '';
        bgInput.value = '';
        const img = bgDropzone.querySelector('img');
        if (img) img.remove();
        const removeBtn = bgDropzone.querySelector('button');
        if (removeBtn) removeBtn.remove();
        
        bgDropzone.innerHTML = `
            <div class="upload-placeholder">
                <span class="upload-icon">📁</span>
                <span class="upload-text">Click to upload or drag and drop</span>
                <span class="upload-subtext">JPEG, PNG, JPG, GIF (Max 5MB)</span>
            </div>
        `;
        bgDropzone.appendChild(bgInput);
        
        const bgDiv = previewContainer.querySelector('.preview-bg');
        if (bgDiv) {
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    }

    function previewImage(file, dropzone) {
        const reader = new FileReader();
        reader.onload = (e) => {
            dropzone.innerHTML = `
                <div class="current-image">
                    <img src="${e.target.result}" alt="Current Background">
                    <button type="button" onclick="removeBackground()" class="remove-btn">Remove Image</button>
                </div>
            `;
            dropzone.appendChild(bgInput);
        };
        reader.readAsDataURL(file);
    }

    function updateImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const bgDiv = previewContainer.querySelector('.preview-bg');
            if (bgDiv) {
                bgDiv.style.backgroundImage = `url(${e.target.result})`;
                bgDiv.style.background = 'none';
            }
        };
        reader.readAsDataURL(file);
    }

    function toggleBackgroundType() {
        const isImage = document.getElementById('bg-type-image').checked;
        const uploadSection = document.getElementById('bg-upload-section');
        const colorSection = document.getElementById('bg-color-section');
        const bgInput = document.getElementById('bg-input');

        if (isImage) {
            uploadSection.style.display = 'block';
            colorSection.style.display = 'none';
            bgInput.disabled = false;
        } else {
            uploadSection.style.display = 'none';
            colorSection.style.display = 'block';
            bgInput.disabled = true;
        }
    }

    function updateColorPreview() {
        const colorSelect = document.querySelector('input[name="background_color"]:checked');
        const customColorInput = document.getElementById('custom-bg-color');
        const hexInput = document.getElementById('color-hex-value');
        const customPreview = document.getElementById('custom-color-preview');
        const bgDiv = previewContainer.querySelector('.preview-bg');
        
        if (bgDiv && colorSelect && colorSelect.value) {
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = colorSelect.value;
            // Clear custom color when predefined color is selected
            customColorInput.value = '#667eea';
            hexInput.value = '#667eea';
            customPreview.style.background = '#667eea';
        }
    }

    function updateCustomColorPreview() {
        const customColorInput = document.getElementById('custom-bg-color');
        const hexInput = document.getElementById('color-hex-value');
        const customPreview = document.getElementById('custom-color-preview');
        const bgDiv = previewContainer.querySelector('.preview-bg');
        
        if (bgDiv && customColorInput.value) {
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = customColorInput.value;
            document.querySelectorAll('input[name="background_color"]').forEach(el => el.checked = false);
            hexInput.value = customColorInput.value;
            customPreview.style.background = customColorInput.value;
        }
    }

    function updateColorFromHex() {
        const hexInput = document.getElementById('color-hex-value');
        const customColorInput = document.getElementById('custom-bg-color');
        const customPreview = document.getElementById('custom-color-preview');
        const bgDiv = previewContainer.querySelector('.preview-bg');
        
        if (hexInput.value && /^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) {
            customColorInput.value = hexInput.value;
            customPreview.style.background = hexInput.value;
            bgDiv.style.backgroundImage = 'none';
            bgDiv.style.background = hexInput.value;
            document.querySelectorAll('input[name="background_color"]').forEach(el => el.checked = false);
        }
    }

    // Billing settings scripts
    document.getElementById('logo-size-a4').addEventListener('input', (e) => {
        document.getElementById('logo-size-a4-value').textContent = e.target.value;
        document.querySelectorAll('#preview-a4 .preview-logo').forEach(el => {
            el.style.maxHeight = e.target.value + 'px';
        });
    });
    
    document.getElementById('logo-size-thermal').addEventListener('input', (e) => {
        document.getElementById('logo-size-thermal-value').textContent = e.target.value;
        document.querySelectorAll('#preview-thermal .preview-logo').forEach(el => {
            el.style.maxHeight = e.target.value + 'px';
        });
    });
    
    document.getElementById('company-name').addEventListener('input', (e) => {
        document.getElementById('preview-company-name-a4').textContent = e.target.value || 'Company Name';
        document.getElementById('preview-company-name-thermal').textContent = e.target.value || 'Company Name';
    });
    
    document.getElementById('address').addEventListener('input', (e) => {
        document.getElementById('preview-address-a4').textContent = e.target.value || 'Address';
        document.getElementById('preview-address-thermal').textContent = e.target.value || 'Address';
    });
    
    document.getElementById('phone').addEventListener('input', (e) => {
        document.getElementById('preview-phone-a4').textContent = e.target.value ? 'Phone: ' + e.target.value : 'Phone';
    });
    
    document.getElementById('email').addEventListener('input', (e) => {
        document.getElementById('preview-email-a4').textContent = e.target.value ? 'Email: ' + e.target.value : 'Email';
    });
    
    document.getElementById('invoice-prefix').addEventListener('input', (e) => {
        document.getElementById('preview-invoice-prefix-a4').textContent = (e.target.value || 'INV') + '-001';
        document.getElementById('preview-invoice-prefix-thermal').textContent = (e.target.value || 'INV') + '-001';
    });
    
    document.getElementById('footer-text').addEventListener('input', (e) => {
        document.getElementById('preview-footer-a4').textContent = e.target.value || 'Footer Text';
        document.getElementById('preview-footer-thermal').textContent = e.target.value || 'Footer Text';
    });
    
    document.getElementById('terms-conditions').addEventListener('input', (e) => {
        document.getElementById('preview-terms-a4').textContent = e.target.value || 'Terms & Conditions';
    });
    
    function showPreview(format) {
        document.querySelectorAll('.preview-container').forEach(el => el.style.display = 'none');
        document.getElementById('preview-' + format).style.display = 'block';
        
        document.getElementById('btn-a4').classList.remove('primary');
        document.getElementById('btn-thermal').classList.remove('primary');
        document.getElementById('btn-' + format).classList.add('primary');
    }
    
    showPreview('{{ $settings['default_format'] }}');
    toggleBackgroundType();
</script>

<style>
.settings-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 12px 24px;
    border: none;
    background: #f8f9fa;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    background: #e9ecef;
}

.tab-btn.active {
    background: #4a90e2;
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.bg-type-selector {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.bg-type-option {
    cursor: pointer;
}

.bg-type-option input {
    display: none;
}

.bg-type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: white;
}

.bg-type-option input:checked + .bg-type-card {
    border-color: #4a90e2;
    background: #f0f8ff;
}

.bg-type-card:hover {
    border-color: #4a90e2;
}

.bg-type-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.bg-type-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.bg-type-desc {
    font-size: 12px;
    color: #666;
    text-align: center;
}

.upload-zone {
    border: 2px dashed #ccc;
    padding: 30px;
    text-align: center;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.upload-zone:hover {
    border-color: #4a90e2;
    background: #f0f8ff;
}

.current-image {
    position: relative;
}

.current-image img {
    max-height: 150px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.remove-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
}

.remove-btn:hover {
    background: #c0392b;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.upload-icon {
    font-size: 40px;
}

.upload-text {
    font-weight: 500;
    color: #333;
}

.upload-subtext {
    font-size: 12px;
    color: #666;
}

.color-presets {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.color-option {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.color-option input {
    display: none;
}

.color-swatch {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    border: 3px solid transparent;
    transition: all 0.3s ease;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.color-option input:checked + .color-swatch {
    border-color: #4a90e2;
    box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.2);
}

.color-check {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 24px;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.3s ease;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.color-option input:checked + .color-swatch .color-check {
    opacity: 1;
}

.color-name {
    font-size: 12px;
    font-weight: 500;
    color: #666;
    text-align: center;
}

.custom-color-section {
    margin-top: 25px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
}

.color-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 15px;
}

.color-preview-box {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    border: 3px solid #e0e0e0;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.color-picker-wrapper input[type="color"] {
    width: 60px;
    height: 60px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
}

.color-hex-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-family: monospace;
    font-size: 14px;
    color: #333;
    transition: border-color 0.3s ease;
}

.color-hex-input:focus {
    outline: none;
    border-color: #4a90e2;
}

.preview-wrapper {
    margin-top: 25px;
}

.reception-preview-full {
    height: 500px;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    background-size: cover;
    background-position: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.preview-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}

.preview-content {
    position: relative;
    z-index: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    color: white;
}

.preview-content .reception-nav {
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.preview-content .nav-toggle {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 20px;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
}

.preview-content .nav-menu {
    display: flex;
    gap: 20px;
}

.preview-content .nav-menu a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    opacity: 0.9;
}

.preview-content .reception-header {
    text-align: center;
    padding: 40px 20px;
}

.preview-content .reception-header h1 {
    font-size: 32px;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.preview-content .reception-header p {
    font-size: 16px;
    margin: 0;
    opacity: 0.9;
}

.preview-content .search-section {
    padding: 0 20px;
    max-width: 600px;
    margin: 0 auto;
}

.preview-content .search-box {
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 15px 20px;
    backdrop-filter: blur(10px);
}

.preview-content .search-box input {
    width: 100%;
    border: none;
    background: transparent;
    color: white;
    font-size: 16px;
    outline: none;
}

.preview-content .search-box input::placeholder {
    color: rgba(255,255,255,0.7);
}

.preview-toggle {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
</style>
@endsection