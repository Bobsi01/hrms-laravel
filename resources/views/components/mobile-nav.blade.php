@php
    $user = Auth::user();
    $uid = $user?->id ?? 0;
    $role = strtolower($user?->role ?? '');
    $isAdminRole = $role === 'admin';
    $isHrRole = $role === 'hr';
    $isEmployeeRole = $role === 'employee';
    $showEmployeePortal = $user && $isEmployeeRole;

    $perms = $userPermissions ?? [];
    $canAttendanceMgmt = ($perms['attendance.attendance_records'] ?? 'none') !== 'none';
    $canOvertimeMgmt = ($perms['payroll.overtime'] ?? 'none') !== 'none';
    $canLeaveMgmt = (($perms['leave.leave_approval'] ?? 'none') !== 'none' || ($perms['leave.leave_balances'] ?? 'none') !== 'none' || ($perms['leave.leave_config'] ?? 'none') !== 'none');
    $canPayrollMgmt = ($perms['payroll.payroll_runs'] ?? 'none') !== 'none';
    $canEmployees = ($perms['hr_core.employees'] ?? 'none') !== 'none';
    $canDepartments = ($perms['hr_core.departments'] ?? 'none') !== 'none';
    $canPositions = ($perms['hr_core.positions'] ?? 'none') !== 'none';
    $canDocuments = ($perms['documents.memos'] ?? 'none') !== 'none';
    $canRecruitment = ($perms['hr_core.recruitment'] ?? 'none') !== 'none';
    $canAudit = ($perms['system.audit_logs'] ?? 'none') !== 'none';
    $canInventoryItems = ($perms['inventory.inventory_items'] ?? 'none') !== 'none';
    $canPOS = ($perms['inventory.pos_transactions'] ?? 'none') !== 'none';
    $canInventoryReports = ($perms['inventory.inventory_reports'] ?? 'none') !== 'none';
    $canPrintServer = ($perms['inventory.print_server'] ?? 'none') !== 'none';

    $hasTimeAttendance = ($canAttendanceMgmt || $canOvertimeMgmt || $canLeaveMgmt || $canPayrollMgmt);
    $hasPeopleOrg = ($canEmployees || $canDepartments || $canPositions || $canDocuments || $canRecruitment);
    $hasInventory = ($canInventoryItems || $canInventoryReports);
    $hasSalesPOS = $canPOS;
    $hasAdminTools = ($isAdminRole || $isHrRole || $canAudit || $canPrintServer);
@endphp

<div id="mnav" class="md:hidden hidden transition-all border-b border-slate-200 bg-white">
    <nav class="px-3 py-3 space-y-0.5 text-sm">
        @if($showEmployeePortal)
        <div class="px-3 pt-2 pb-1 text-[10px] uppercase tracking-widest text-slate-400 font-semibold">My Workspace</div>
        <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Home</a>
        <a href="{{ route('attendance.my') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">My Attendance</a>
        <a href="{{ route('payroll.my-payslips') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">My Payslips</a>
        <a href="{{ route('leave.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Leaves</a>
        <div class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Documents & Comms</div>
        <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Personal Documents</a>
        <a href="#" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Memos</a>
        @endif

        @if($user && !$showEmployeePortal)
        <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Dashboard</a>
        @endif

        @if($hasTimeAttendance)
        <div class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Time, Attendance & Payroll</div>
        @if($canAttendanceMgmt)
        <a href="{{ route('attendance.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Attendance Management</a>
        @endif
        @if($canLeaveMgmt)
        <a href="{{ route('leave.admin') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Leave Management</a>
        @endif
        @if($canPayrollMgmt)
        <a href="{{ route('payroll.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Payroll Management</a>
        @endif
        @endif

        @if($hasPeopleOrg)
        <div class="mobile-nav-divider"></div>
        <div class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-widest text-slate-400 font-semibold">People & Organization</div>
        @if($canEmployees)
        <a href="{{ route('employees.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Employees</a>
        @endif
        @if($canDepartments)
        <a href="{{ route('departments.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Departments</a>
        @endif
        @if($canPositions)
        <a href="{{ route('positions.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Positions</a>
        @endif
        @endif

        @if($hasAdminTools)
        <div class="mobile-nav-divider"></div>
        <div class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Administration</div>
        @if($isAdminRole || $isHrRole)
        <a href="{{ route('admin.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-50">Management Hub</a>
        @endif
        @endif
    </nav>
</div>
