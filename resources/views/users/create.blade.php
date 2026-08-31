@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Create User</h1>
        <p>Add a new user to the system</p>
    </div>
</div>

<div class="panel">
    <form method="post" action="{{ route('users.store') }}">
        @csrf
        
        <h2>User Information</h2>
        <div class="grid2">
            <label>
                Name
                <input type="text" name="name" required>
            </label>
            <label>
                Email
                <input type="email" name="email" required>
            </label>
        </div>
        <div class="grid2">
            <label>
                Password
                <input type="password" name="password" required>
            </label>
            <label>
                Confirm Password
                <input type="password" name="password_confirmation" required>
            </label>
        </div>
        
        <h2>Role Assignment</h2>
        <label>
            Assign Role
            <select name="role">
                <option value="">-- No Predefined Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </label>
        
        <label>
            Branch
            <select name="branch_id">
                <option value="">-- All Branches --</option>
                @if(auth()->user()->branch)
                    <option value="{{ auth()->user()->branch_id }}" selected>{{ auth()->user()->branch->name }}</option>
                @endif
            </select>
        </label>
        
        <label>
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" checked>
            Active User
        </label>

        <h2>Custom Permissions</h2>
        <p style="color: #666; font-size: 0.9em;">Select specific permissions (optional - overrides role permissions)</p>
        
        <div class="permissions-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="position: sticky; top: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); z-index: 10;">
                    <tr>
                        <th style="padding: 15px; text-align: left; font-size: 13px; font-weight: bold; color: white; text-transform: uppercase; letter-spacing: 0.5px;">Module</th>
                        <th style="padding: 15px; text-align: left; font-size: 13px; font-weight: bold; color: white; text-transform: uppercase; letter-spacing: 0.5px;">Permissions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $module => $modulePermissions)
                        <tr class="permission-row" style="border-bottom: 1px solid #eee; transition: background-color 0.2s ease;">
                            <td style="padding: 15px; vertical-align: top; width: 180px; background: #fafafa;">
                                <label class="module-label" style="display: flex; align-items: center; font-weight: 600; color: #2c3e50; font-size: 14px; cursor: pointer; transition: color 0.2s ease;">
                                    <input type="checkbox" class="module-checkbox" data-module="{{ $module }}" style="margin-right: 10px; transform: scale(1.1); cursor: pointer;">
                                    <span>{{ ucfirst($module) }}</span>
                                </label>
                            </td>
                            <td style="padding: 15px; vertical-align: top;">
                                <div class="permissions-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">
                                    @foreach($modulePermissions as $permission)
                                        <label class="permission-tag" style="display: inline-flex; align-items: center; padding: 6px 12px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 20px; font-size: 12px; cursor: pointer; margin: 0; transition: all 0.2s ease; border: 1px solid #e0e0e0;">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="permission-checkbox" data-module="{{ $module }}" style="margin-right: 8px; cursor: pointer;">
                                            <span>{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <style>
            .permission-row:hover {
                background-color: #f0f4ff;
            }
            
            .module-label:hover {
                color: #667eea;
            }
            
            .permission-tag:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-color: #667eea;
            }
            
            .permission-tag input:checked + span {
                color: #667eea;
                font-weight: 600;
            }
            
            .permission-tag:has(input:checked) {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-color: #667eea;
            }
            
            .permissions-container::-webkit-scrollbar {
                width: 8px;
            }
            
            .permissions-container::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }
            
            .permissions-container::-webkit-scrollbar-thumb {
                background: #667eea;
                border-radius: 4px;
            }
            
            .permissions-container::-webkit-scrollbar-thumb:hover {
                background: #764ba2;
            }
        </style>

        <script>
            // Module checkbox functionality - select/deselect all permissions in a module
            document.querySelectorAll('.module-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const module = this.dataset.module;
                    const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
                    permissionCheckboxes.forEach(pc => pc.checked = this.checked);
                    
                    // Add visual feedback
                    const row = this.closest('.permission-row');
                    if (this.checked) {
                        row.style.backgroundColor = '#e8f0fe';
                    } else {
                        row.style.backgroundColor = '';
                    }
                });
            });

            // Update module checkbox when individual permissions change
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const module = this.dataset.module;
                    const moduleCheckbox = document.querySelector(`.module-checkbox[data-module="${module}"]`);
                    const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
                    const allChecked = Array.from(permissionCheckboxes).every(pc => pc.checked);
                    moduleCheckbox.checked = allChecked;
                    
                    // Visual feedback for row
                    const row = this.closest('.permission-row');
                    const anyChecked = Array.from(permissionCheckboxes).some(pc => pc.checked);
                    if (anyChecked) {
                        row.style.backgroundColor = '#e8f0fe';
                    } else {
                        row.style.backgroundColor = '';
                    }
                });
            });

            // Add hover effects for interactivity
            document.querySelectorAll('.permission-tag').forEach(tag => {
                tag.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                tag.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        </script>

        <button class="primary">Create User</button>
        <a href="{{ route('users.index') }}" class="secondary">Cancel</a>
    </form>
</div>
@endsection