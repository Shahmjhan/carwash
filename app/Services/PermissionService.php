<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public function syncDefaultPermissions(): void
    {
        // First, delete all existing permissions to clean up
        Permission::truncate();

        $modules = [
            'dashboard' => ['view'],
            'customers' => ['view', 'create', 'edit', 'delete'],
            'vehicles' => ['view', 'create', 'edit', 'delete'],
            'appointments' => ['view', 'create', 'edit', 'delete'],
            'jobs' => ['view', 'create', 'edit', 'delete', 'change_status', 'request_additional_work', 'approve', 'consume_parts', 'view_board', 'edit_inspection'],
            'inventory' => ['view', 'create', 'edit', 'delete', 'adjust_stock'],
            'invoices' => ['view', 'pay', 'print'],
            'reports' => ['view'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit_billing'],
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
                'view_dashboard', 'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
                'view_vehicles', 'create_vehicles', 'edit_vehicles', 'delete_vehicles',
                'view_appointments', 'create_appointments', 'edit_appointments', 'delete_appointments',
                'view_jobs', 'create_jobs', 'edit_jobs', 'delete_jobs', 'change_status_jobs', 'request_additional_work_jobs', 'approve_jobs', 'consume_parts_jobs', 'view_board_jobs', 'edit_inspection_jobs',
                'view_inventory', 'create_inventory', 'edit_inventory', 'delete_inventory', 'adjust_stock_inventory',
                'view_invoices', 'pay_invoices', 'print_invoices',
                'view_reports',
                'view_users', 'create_users', 'edit_users', 'delete_users',
                'view_settings', 'edit_billing_settings'
            ],
            'manager' => [
                'view_dashboard', 'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
                'view_vehicles', 'create_vehicles', 'edit_vehicles', 'delete_vehicles',
                'view_appointments', 'create_appointments', 'edit_appointments', 'delete_appointments',
                'view_jobs', 'create_jobs', 'edit_jobs', 'delete_jobs', 'change_status_jobs', 'request_additional_work_jobs', 'approve_jobs', 'consume_parts_jobs', 'view_board_jobs', 'edit_inspection_jobs',
                'view_inventory', 'create_inventory', 'edit_inventory', 'delete_inventory', 'adjust_stock_inventory',
                'view_invoices', 'pay_invoices', 'print_invoices',
                'view_reports'
            ],
            'receptionist' => [
                'view_dashboard', 'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments', 'view_jobs', 'create_jobs',
                'view_invoices', 'pay_invoices', 'print_invoices'
            ],
            'cashier' => [
                'view_dashboard', 'view_invoices', 'pay_invoices', 'print_invoices',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments', 'view_jobs', 'create_jobs'
            ],
            'technician' => [
                'view_dashboard', 'view_jobs', 'view_board_jobs', 'change_status_jobs', 'consume_parts_jobs'
            ],
            'staff' => [
                'view_dashboard', 'view_jobs', 'view_board_jobs',
                'view_customers', 'create_customers', 'view_vehicles', 'create_vehicles',
                'view_appointments', 'create_appointments'
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
                // Use the permission slugs directly from the array
                $permissions = Permission::whereIn('slug', $modules)->get();
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
