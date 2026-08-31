@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Billing Settings</h1>
        <p>Configure your invoice and receipt templates</p>
    </div>
</div>

<div class="panel">
    <form method="post" action="{{ route('settings.billing.update') }}" enctype="multipart/form-data">
        @csrf
        
        <h2>Company Information</h2>
        <div class="grid2">
            <label>
                Company Name
                <input type="text" name="company_name" value="{{ $settings['company_name'] }}" required>
            </label>
            <label>
                Tax ID
                <input type="text" name="tax_id" value="{{ $settings['tax_id'] }}">
            </label>
        </div>
        <label>
            Address
            <textarea name="address" rows="2">{{ $settings['address'] }}</textarea>
        </label>
        <div class="grid3">
            <label>
                Phone
                <input type="text" name="phone" value="{{ $settings['phone'] }}">
            </label>
            <label>
                Email
                <input type="email" name="email" value="{{ $settings['email'] }}">
            </label>
            <label>
                Website
                <input type="url" name="website" value="{{ $settings['website'] }}">
            </label>
        </div>

        <h2>Invoice & Receipt Settings</h2>
        <div class="grid2">
            <label>
                Invoice Prefix
                <input type="text" name="invoice_prefix" value="{{ $settings['invoice_prefix'] }}" required>
            </label>
            <label>
                Receipt Prefix
                <input type="text" name="receipt_prefix" value="{{ $settings['receipt_prefix'] }}" required>
            </label>
        </div>

        <h2>Print Format</h2>
        <div class="grid2">
            <label>
                Default Format
                <select name="default_format">
                    <option value="a4" {{ $settings['default_format'] === 'a4' ? 'selected' : '' }}>A4 (Professional)</option>
                    <option value="thermal" {{ $settings['default_format'] === 'thermal' ? 'selected' : '' }}>80mm Thermal Receipt</option>
                </select>
            </label>
            <div>
                <label>
                    <input type="checkbox" name="a4_enabled" {{ $settings['a4_enabled'] ? 'checked' : '' }}>
                    Enable A4 Printing
                </label>
                <label>
                    <input type="checkbox" name="thermal_enabled" {{ $settings['thermal_enabled'] ? 'checked' : '' }}>
                    Enable Thermal Printing
                </label>
            </div>
        </div>

        <h2>Document Content</h2>
        <label>
            Footer Text
            <textarea name="footer_text" rows="2">{{ $settings['footer_text'] }}</textarea>
        </label>
        <label>
            Terms & Conditions
            <textarea name="terms_conditions" rows="4">{{ $settings['terms_conditions'] }}</textarea>
        </label>

        <h2>Logo</h2>
        <div style="border: 2px dashed #ccc; padding: 20px; text-align: center; border-radius: 4px; cursor: pointer;" id="logo-dropzone">
            @if($settings['logo_path'])
                <img src="{{ asset($settings['logo_path']) }}" alt="Current Logo" style="max-height: 100px; margin-bottom: 10px;">
                <br>
            @endif
            <input type="file" name="logo" id="logo-input" accept="image/*" style="display: none;">
            <label for="logo-input" style="cursor: pointer; color: #3498db;">
                <strong>Click to upload or drag and drop</strong>
            </label>
            <p style="color: #666; font-size: 0.9em; margin: 5px 0;">JPEG, PNG, JPG, GIF (Max 2MB)</p>
            @if($settings['logo_path'])
                <button type="button" onclick="removeLogo()" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-top: 10px;">Remove Logo</button>
            @endif
        </div>
        <input type="hidden" name="logo_path" value="{{ $settings['logo_path'] }}" id="logo-path-hidden">

        <h2>Reception Background</h2>
        <div style="border: 2px dashed #ccc; padding: 20px; text-align: center; border-radius: 4px; cursor: pointer;" id="bg-dropzone">
            @if($settings['reception_background_image'] ?? null)
                <img src="{{ asset($settings['reception_background_image']) }}" alt="Current Background" style="max-height: 150px; margin-bottom: 10px; border-radius: 4px;">
                <br>
            @endif
            <input type="file" name="reception_background" id="bg-input" accept="image/*" style="display: none;">
            <label for="bg-input" style="cursor: pointer; color: #3498db;">
                <strong>Click to upload or drag and drop</strong>
            </label>
            <p style="color: #666; font-size: 0.9em; margin: 5px 0;">JPEG, PNG, JPG, GIF (Max 5MB)</p>
            @if($settings['reception_background_image'] ?? null)
                <button type="button" onclick="removeBackground()" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-top: 10px;">Remove Background</button>
            @endif
        </div>
        <input type="hidden" name="reception_background_image" value="{{ $settings['reception_background_image'] ?? '' }}" id="bg-path-hidden">

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
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    previewImage(e.target.files[0], dropzone);
                }
            });

            function removeLogo() {
                logoPathHidden.value = '';
                fileInput.value = '';
                const img = dropzone.querySelector('img');
                if (img) img.remove();
                const removeBtn = dropzone.querySelector('button');
                if (removeBtn) removeBtn.remove();
            }

            // Background dropzone
            const bgDropzone = document.getElementById('bg-dropzone');
            const bgInput = document.getElementById('bg-input');
            const bgPathHidden = document.getElementById('bg-path-hidden');

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
                }
            });

            bgInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    previewImage(e.target.files[0], bgDropzone);
                }
            });

            function removeBackground() {
                bgPathHidden.value = '';
                bgInput.value = '';
                const img = bgDropzone.querySelector('img');
                if (img) img.remove();
                const removeBtn = bgDropzone.querySelector('button');
                if (removeBtn) removeBtn.remove();
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
                        newImg.style.maxHeight = dropzone.id === 'bg-dropzone' ? '150px' : '100px';
                        newImg.style.marginBottom = '10px';
                        if (dropzone.id === 'bg-dropzone') {
                            newImg.style.borderRadius = '4px';
                        }
                        dropzone.insertBefore(newImg, dropzone.firstChild);
                    }
                };
                reader.readAsDataURL(file);
            }
        </script>

        <button class="primary">Save Settings</button>
    </form>
</div>

<div class="panel">
    <h2>Preview</h2>
    <div class="grid2">
        <a href="{{ route('invoices.print', 1) }}" target="_blank" class="secondary">Preview A4 Format</a>
        <a href="{{ route('invoices.print', ['invoice' => 1, 'format' => 'thermal']) }}" target="_blank" class="secondary">Preview Thermal Format</a>
    </div>
</div>
@endsection