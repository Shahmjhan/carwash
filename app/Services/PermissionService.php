<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public function syncDefaultPermissions(): void
    {
        $modules = [
            'dashboard' => ['view', 'view_all_branches'],
            'customers' => ['view', 'create', 'edit', 'delete', 'view_history'],
            'vehicles' => ['view', 'create', 'edit', 'delete', 'view_history'],
            'appointments' => ['view', 'create', 'edit', 'delete', 'reschedule', 'cancel'],
            'inspections' => ['view', 'create', 'edit', 'delete', 'upload_photos'],
            'jobs' => ['view', 'create', 'edit', 'delete', 'change_status', 'assign_technician', 'assign_bay'],
            'job_services' => ['view', 'add', 'remove', 'approve', 'reject'],
            'job_parts' => ['view', 'add', 'remove', 'approve', 'consume'],
            'inventory' => ['view', 'create', 'edit', 'delete', 'adjust', 'transfer', 'view_movements'],
            'products' => ['view', 'create', 'edit', 'delete'],
            'suppliers' => ['view', 'create', 'edit', 'delete'],
            'purchase_orders' => ['view', 'create', 'edit', 'delete', 'approve', 'receive', 'cancel'],
            'invoices' => ['view', 'create', 'edit', 'delete', 'finalize', 'print'],
            'payments' => ['view', 'create', 'refund', 'approve_refund'],
            'expenses' => ['view', 'create', 'edit', 'delete', 'approve'],
            'reports' => ['view_sales', 'view_services', 'view_inventory', 'view_financial', 'view_customers', 'view_employees', 'export'],
            'users' => ['view', 'create', 'edit', 'delete', 'assign_roles'],
            'roles' => ['view', 'create', 'edit', 'delete', 'assign_permissions'],
            'branches' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit_general', 'edit_pricing', 'edit_loyalty', 'edit_notifications'],
            'communications' => ['view', 'send', 'view_templates', 'edit_templates'],
            'quality_checks' => ['view', 'create', 'edit', 'approve', 'reject'],
            'service_bays' => ['view', 'create', 'edit', 'delete', 'assign'],
            'technicians' => ['view', 'create', 'edit', 'delete', 'view_performance'],
            'loyalty' => ['view', 'adjust_points', 'configure'],
            'memberships' => ['view', 'create', 'edit', 'delete'],
            'discounts' => ['view', 'create', 'edit', 'delete', 'apply'],
            'warranties' => ['view', 'create', 'edit', 'delete'],
            'complaints' => ['view', 'create', 'edit', 'delete', 'resolve'],
            'cash_registers' => ['view', 'open', 'close', 'view_transactions'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = ucfirst($action) . ' ' . ucfirst($module);
                $slug = $action . '_' . $module;

                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'module' => $module,
                        'description' => "Permission to {$action} {$module}"
                    ]
                );
            }
        }
    }

    public function syncDefaultRoles(): void
    {
        $rolePermissions = [
            'super_admin' => [], // All permissions
            'owner' => [
                'dashboard', 'customers', 'vehicles', 'appointments', 'inspections', 'jobs',
                'inventory', 'products', 'suppliers', 'purchase_orders', 'invoices', 'payments',
                'expenses', 'reports', 'users', 'roles', 'branches', 'settings',
                'communications', 'quality_checks', 'service_bays', 'technicians',
                'loyalty', 'memberships', 'discounts', 'warranties', 'complaints'
            ],
            'manager' => [
                'dashboard', 'customers', 'vehicles', 'appointments', 'inspections', 'jobs',
                'inventory', 'products', 'suppliers', 'purchase_orders', 'invoices', 'payments',
                'expenses', 'reports', 'service_bays', 'technicians', 'quality_checks'
            ],
            'receptionist' => [
                'dashboard_view', 'customers_view', 'customers_create', 'vehicles_view', 'vehicles_create',
                'appointments_view', 'appointments_create', 'jobs_view', 'jobs_create',
                'invoices_view', 'invoices_create', 'invoices_print', 'payments_view', 'payments_create'
            ],
            'service_advisor' => [
                'dashboard_view', 'customers_view', 'vehicles_view', 'inspections_view', 'inspections_create',
                'inspections_edit', 'inspections_upload_photos', 'jobs_view', 'jobs_edit',
                'job_services_view', 'job_services_add', 'job_parts_view', 'job_parts_add'
            ],
            'technician' => [
                'dashboard_view', 'jobs_view', 'job_services_view', 'job_parts_view',
                'job_parts_consume', 'quality_checks_view', 'quality_checks_create'
            ],
            'inventory_manager' => [
                'dashboard_view', 'inventory_view', 'inventory_create', 'inventory_edit',
                'inventory_adjust', 'inventory_transfer', 'inventory_view_movements',
                'products_view', 'products_create', 'products_edit', 'suppliers_view',
                'suppliers_create', 'purchase_orders_view', 'purchase_orders_create',
                'purchase_orders_receive'
            ],
            'cashier' => [
                'dashboard_view', 'invoices_view', 'invoices_create', 'invoices_print',
                'payments_view', 'payments_create', 'cash_registers_view', 'cash_registers_open',
                'cash_registers_close', 'cash_registers_view_transactions'
            ],
            'accountant' => [
                'dashboard_view', 'invoices_view', 'payments_view', 'payments_refund',
                'expenses_view', 'expenses_create', 'reports_view_sales', 'reports_view_financial',
                'reports_view_customers', 'reports_export'
            ],
            'quality_inspector' => [
                'dashboard_view', 'jobs_view', 'quality_checks_view', 'quality_checks_create',
                'quality_checks_edit', 'quality_checks_approve', 'quality_checks_reject'
            ],
        ];

        foreach ($rolePermissions as $roleName => $modules) {
            $role = Role::firstOrCreate(
                ['slug' => $roleName],
                [
                    'name' => ucfirst(str_replace('_', ' ', $roleName)),
                    'is_system' => true
                ]
            );

            if ($roleName === 'super_admin') {
                // Super admin gets all permissions
                $allPermissions = Permission::all();
                $role->permissions()->sync($allPermissions->pluck('id'));
            } else {
                $permissionSlugs = [];
                foreach ($modules as $module) {
                    $modulePermissions = Permission::where('module', $module)->get();
                    foreach ($modulePermissions as $permission) {
                        $permissionSlugs[] = $permission->slug;
                    }
                }
                $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
                $role->permissions()->sync($permissions->pluck('id'));
            }
        }
    }

    public function userHasPermission(?\App\Models\User $user, string $permissionSlug): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return Cache::remember("user.{$user->id}.permissions", 3600, function () use ($user) {
            return $user->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('slug')
                ->toArray();
        }) && in_array($permissionSlug, Cache::get("user.{$user->id}.permissions", []));
    }
}
