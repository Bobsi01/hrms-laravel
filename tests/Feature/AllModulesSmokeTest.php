<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AllModulesSmokeTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'bobis.daniel.bscs2023@gmail.com')->firstOrFail();
    }

    /**
     * @dataProvider routeProvider
     */
    public function test_route_loads(string $uri, string $label): void
    {
        $response = $this->actingAs($this->user)->get($uri);
        
        $this->assertTrue(
            $response->status() >= 200 && $response->status() < 400,
            "FAIL [{$response->status()}] {$label} ({$uri})"
        );
    }

    public static function routeProvider(): array
    {
        return [
            // Dashboard
            ['/', 'Dashboard'],
            
            // HR Core
            ['/employees', 'Employees Index'],
            ['/employees/create', 'Employees Create'],
            ['/departments', 'Departments Index'],
            ['/departments/create', 'Departments Create'],
            ['/positions', 'Positions Index'],
            ['/positions/create', 'Positions Create'],
            
            // Leave
            ['/leave', 'Leave Index'],
            ['/leave/create', 'Leave Create'],
            ['/leave/admin', 'Leave Admin'],
            
            // Overtime
            ['/overtime', 'Overtime Index'],
            ['/overtime/create', 'Overtime Create'],
            ['/overtime/admin', 'Overtime Admin'],
            
            // Attendance
            ['/attendance/my', 'My Attendance'],
            ['/attendance', 'Attendance Admin'],
            ['/attendance/create', 'Attendance Create'],
            ['/attendance/import', 'Attendance Import'],
            
            // Payroll
            ['/payroll/my-payslips', 'My Payslips'],
            ['/payroll', 'Payroll Index'],
            ['/payroll/create', 'Payroll Create'],
            ['/payroll/complaints', 'Payroll Complaints'],
            
            // Documents
            ['/documents', 'Documents Index'],
            ['/documents/admin', 'Documents Admin'],
            ['/documents/create', 'Documents Create'],
            
            // Memos
            ['/memos', 'Memos Index'],
            ['/memos/admin', 'Memos Admin'],
            ['/memos/create', 'Memos Create'],
            
            // Recruitment
            ['/recruitment', 'Recruitment Index'],
            ['/recruitment/create', 'Recruitment Create'],
            
            // Account
            ['/account/profile', 'Account Profile'],
            ['/account/change-password', 'Change Password'],
            
            // Compliance
            ['/corrections', 'Corrections Index'],
            ['/corrections/create', 'Corrections Create'],
            ['/privacy/consent', 'Privacy Consent'],
            
            // Audit
            ['/audit', 'Audit Trail'],
            
            // Admin
            ['/admin', 'Admin Hub'],
            ['/admin/branches', 'Branches'],
            ['/admin/payroll-config', 'Payroll Config'],
            ['/admin/cutoff-periods', 'Cutoff Periods'],
            ['/admin/leave-defaults', 'Leave Defaults'],
            ['/admin/leave-entitlements', 'Leave Entitlements'],
            ['/admin/work-schedules', 'Work Schedules'],
            ['/admin/approval-workflow', 'Approval Workflow'],
            ['/admin/bir-reports', 'BIR Reports'],
            ['/admin/corrections', 'Admin Corrections'],
            ['/admin/privacy-consents', 'Privacy Consents Admin'],
            ['/admin/system', 'System Monitor'],
            ['/admin/users', 'User Management'],
            ['/admin/users/create', 'Create User'],
            
            // Inventory
            ['/inventory', 'Inventory Index'],
            ['/inventory/create', 'Inventory Create'],
            ['/inventory/categories', 'Categories'],
            ['/inventory/suppliers', 'Suppliers'],
            ['/inventory/locations', 'Locations'],
            ['/inventory/pos', 'POS Terminal'],
            ['/inventory/transactions', 'Transactions'],
            ['/inventory/reports', 'Inventory Reports'],
            ['/inventory/purchase-orders', 'Purchase Orders'],
            ['/inventory/purchase-orders/create', 'Create PO'],
            
            // Notifications
            ['/notifications', 'Notifications Index'],
        ];
    }
}
