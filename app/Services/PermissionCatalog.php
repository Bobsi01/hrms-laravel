<?php

namespace App\Services;

/**
 * Complete permissions catalog organized by domain.
 * Migrated from includes/permissions_catalog.php
 */
class PermissionCatalog
{
    public static function all(): array
    {
        return [
            'system' => [
                'label' => 'System Administration',
                'description' => 'Core system settings, configuration, and monitoring',
                'icon' => 'cog',
                'resources' => [
                    'dashboard' => [
                        'label' => 'Dashboard',
                        'description' => 'View system dashboard and statistics',
                    ],
                    'system_settings' => [
                        'label' => 'System Settings',
                        'description' => 'Configure global system parameters',
                    ],
                    'audit_logs' => [
                        'label' => 'Audit Logs',
                        'description' => 'View system audit trail and user actions',
                    ],
                    'system_logs' => [
                        'label' => 'System Logs',
                        'description' => 'View technical system logs and errors',
                    ],
                    'backup_restore' => [
                        'label' => 'Backup & Restore',
                        'description' => 'Manage database backups and restoration',
                    ],
                    'tools_workbench' => [
                        'label' => 'Tools & Workbench',
                        'description' => 'Access developer tools and database workbench',
                    ],
                    'access_control' => [
                        'label' => 'Access Control',
                        'description' => 'Manage device bindings, IP lists, and module access',
                    ],
                ],
            ],

            'hr_core' => [
                'label' => 'HR Core Functions',
                'description' => 'Employee records, departments, positions, and organizational structure',
                'icon' => 'users',
                'resources' => [
                    'employees' => [
                        'label' => 'Employee Management',
                        'description' => 'View, create, update, and manage employee records',
                    ],
                    'departments' => [
                        'label' => 'Department Management',
                        'description' => 'Manage organizational departments',
                    ],
                    'positions' => [
                        'label' => 'Position Management',
                        'description' => 'Define and manage job positions',
                    ],
                    'branches' => [
                        'label' => 'Branch Management',
                        'description' => 'Manage company branches and locations',
                    ],
                    'recruitment' => [
                        'label' => 'Recruitment & Hiring',
                        'description' => 'Manage job applications and recruitment pipeline',
                    ],
                ],
            ],

            'payroll' => [
                'label' => 'Payroll Management',
                'description' => 'Payroll runs, batches, payslips, and compensation',
                'icon' => 'currency-dollar',
                'resources' => [
                    'payroll_runs' => [
                        'label' => 'Payroll Runs',
                        'description' => 'Create and manage payroll processing runs',
                    ],
                    'payslips' => [
                        'label' => 'Payslips',
                        'description' => 'View and manage individual payslips',
                    ],
                    'my_payslips' => [
                        'label' => 'My Payslips',
                        'description' => 'View own payslips',
                        'self_service' => true,
                    ],
                    'compensation' => [
                        'label' => 'Compensation Config',
                        'description' => 'Manage compensation templates and rates',
                    ],
                    'cutoff_periods' => [
                        'label' => 'Cutoff Periods',
                        'description' => 'Manage payroll cutoff periods',
                    ],
                ],
            ],

            'leave' => [
                'label' => 'Leave Management',
                'description' => 'Leave requests, approvals, and balance tracking',
                'icon' => 'calendar',
                'resources' => [
                    'leave_requests' => [
                        'label' => 'Leave Requests',
                        'description' => 'File and manage leave requests',
                        'self_service' => true,
                    ],
                    'leave_admin' => [
                        'label' => 'Leave Administration',
                        'description' => 'Approve/reject leave requests for all employees',
                    ],
                    'leave_policies' => [
                        'label' => 'Leave Policies',
                        'description' => 'Configure leave filing policies and entitlements',
                    ],
                ],
            ],

            'attendance' => [
                'label' => 'Attendance Management',
                'description' => 'Attendance tracking, DTR, and work schedules',
                'icon' => 'clock',
                'resources' => [
                    'self_attendance' => [
                        'label' => 'My Attendance',
                        'description' => 'View own attendance records',
                        'self_service' => true,
                    ],
                    'attendance_admin' => [
                        'label' => 'Attendance Administration',
                        'description' => 'Manage attendance records for all employees',
                    ],
                    'work_schedules' => [
                        'label' => 'Work Schedules',
                        'description' => 'Manage work schedule templates and assignments',
                    ],
                ],
            ],

            'documents' => [
                'label' => 'Document Management',
                'description' => 'Company documents, memos, and file management',
                'icon' => 'document-text',
                'resources' => [
                    'documents' => [
                        'label' => 'Documents',
                        'description' => 'Manage employee and company documents',
                    ],
                    'memos' => [
                        'label' => 'Memos & Announcements',
                        'description' => 'Create and manage company memos',
                    ],
                ],
            ],

            'performance' => [
                'label' => 'Performance Management',
                'description' => 'Performance reviews and KPI tracking',
                'icon' => 'chart-bar',
                'resources' => [
                    'reviews' => [
                        'label' => 'Performance Reviews',
                        'description' => 'Create and manage performance reviews',
                    ],
                ],
            ],

            'notifications' => [
                'label' => 'Notifications',
                'description' => 'System notifications and alerts',
                'icon' => 'bell',
                'resources' => [
                    'view_notifications' => [
                        'label' => 'View Notifications',
                        'description' => 'View own notifications',
                        'self_service' => true,
                    ],
                    'manage_notifications' => [
                        'label' => 'Manage Notifications',
                        'description' => 'Create and manage system-wide notifications',
                    ],
                ],
            ],

            'user_management' => [
                'label' => 'User Management',
                'description' => 'User accounts and access management',
                'icon' => 'user-group',
                'resources' => [
                    'user_accounts' => [
                        'label' => 'User Accounts',
                        'description' => 'Manage user accounts and credentials',
                    ],
                    'self_profile' => [
                        'label' => 'My Profile',
                        'description' => 'View and edit own profile',
                        'self_service' => true,
                    ],
                ],
            ],

            'inventory' => [
                'label' => 'Inventory & POS',
                'description' => 'Inventory management and point-of-sale',
                'icon' => 'cube',
                'resources' => [
                    'inventory_items' => [
                        'label' => 'Inventory Items',
                        'description' => 'Manage inventory items and stock levels',
                    ],
                    'pos_transactions' => [
                        'label' => 'POS Transactions',
                        'description' => 'Process and manage POS transactions',
                    ],
                    'inventory_reports' => [
                        'label' => 'Inventory Reports',
                        'description' => 'View inventory reports and analytics',
                    ],
                    'purchase_orders' => [
                        'label' => 'Purchase Orders',
                        'description' => 'Manage purchase orders and suppliers',
                    ],
                ],
            ],

            'reports' => [
                'label' => 'Reports',
                'description' => 'System-wide reporting and analytics',
                'icon' => 'chart-pie',
                'resources' => [
                    'general_reports' => [
                        'label' => 'General Reports',
                        'description' => 'View and generate system reports',
                    ],
                    'bir_reports' => [
                        'label' => 'BIR Reports & Compliance',
                        'description' => 'Generate BIR forms (2316, 1604-C), alphalists, and statutory remittance reports',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get resource info for a specific domain.resource.
     */
    public static function getResource(string $domain, string $resourceKey): ?array
    {
        $catalog = static::all();
        return $catalog[$domain]['resources'][$resourceKey] ?? null;
    }

    /**
     * Check if a resource is self-service (available to all authenticated users).
     */
    public static function isSelfService(string $domain, string $resourceKey): bool
    {
        $resource = static::getResource($domain, $resourceKey);
        return !empty($resource['self_service']);
    }
}
