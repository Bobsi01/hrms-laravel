@php
    $user = Auth::user();
    $uid = $user?->id ?? 0;
    $role = strtolower($user?->role ?? '');
    $isAdminRole = $role === 'admin';
    $isHrRole = $role === 'hr';
    $isEmployeeRole = $role === 'employee';
    $showEmployeePortal = $user && $isEmployeeRole;

    // Permission checks
    $perms = $userPermissions ?? [];
    $isSA = $isSystemAdmin ?? false;
    $canAttendanceMgmt = $isSA || ($perms['attendance.attendance_records'] ?? 'none') !== 'none';
    $canOvertimeMgmt = $isSA || ($perms['payroll.overtime'] ?? 'none') !== 'none';
    $leaveApproval = $perms['leave.leave_approval'] ?? 'none';
    $leaveBalances = $perms['leave.leave_balances'] ?? 'none';
    $leaveConfig = $perms['leave.leave_config'] ?? 'none';
    $canLeaveMgmt = $isSA || ($leaveApproval !== 'none' || $leaveBalances !== 'none' || $leaveConfig !== 'none');
    $canPayrollMgmt = $isSA || ($perms['payroll.payroll_runs'] ?? 'none') !== 'none';
    $canEmployees = $isSA || ($perms['hr_core.employees'] ?? 'none') !== 'none';
    $canDepartments = $isSA || ($perms['hr_core.departments'] ?? 'none') !== 'none';
    $canPositions = $isSA || ($perms['hr_core.positions'] ?? 'none') !== 'none';
    $canDocuments = $isSA || ($perms['documents.memos'] ?? 'none') !== 'none' || ($perms['documents.documents'] ?? 'none') !== 'none';
    $canRecruitment = $isSA || ($perms['hr_core.recruitment'] ?? 'none') !== 'none';
    $canAudit = $isSA || ($perms['system.audit_logs'] ?? 'none') !== 'none';
    $canInventoryItems = $isSA || ($perms['inventory.inventory_items'] ?? 'none') !== 'none';
    $canPOS = $isSA || ($perms['inventory.pos_transactions'] ?? 'none') !== 'none';
    $canInventoryReports = $isSA || ($perms['inventory.inventory_reports'] ?? 'none') !== 'none';

    $hasTimeAttendance = ($canAttendanceMgmt || $canOvertimeMgmt || $canLeaveMgmt || $canPayrollMgmt);
    $hasPeopleOrg = ($canEmployees || $canDepartments || $canPositions || $canDocuments || $canRecruitment);
    $hasInventory = ($canInventoryItems || $canInventoryReports);
    $hasSalesPOS = $canPOS;
    $hasAdminTools = ($isAdminRole || $isHrRole || $canAudit || $isSA);
@endphp

<aside id="sidebar" class="sidebar w-64 hidden md:flex md:flex-col transition-all duration-300">
    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="flex items-center gap-2 flex-1 min-w-0">
            <div class="brand-icon">H</div>
            <div class="min-w-0 sidebar-label">
                <div class="brand-text">HRMS</div>
                <div class="brand-sub">{{ config('hrms.company.name', 'Company') }}</div>
            </div>
        </div>
        <button id="btnCollapse" class="sidebar-collapse-btn" title="Collapse sidebar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
    </div>

    <nav class="px-2 py-3 space-y-0.5 text-sm sidebar-nav">
        {{-- ═══ Employee Portal ═══ --}}
        @if($showEmployeePortal)
        <div class="nav-group" data-group="my-workspace">
            <button type="button" class="group-label px-3 py-1 w-full text-[10px] uppercase tracking-wide text-gray-400 flex items-center justify-between" data-group-toggle="my-workspace" aria-expanded="true">
                <span>My Workspace</span>
                <svg class="w-3 h-3 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
            </button>
            <div class="group-sep"></div>
            <div class="group-content space-y-1">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tip="Home">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 14V9m0 10h6a2 2 0 002-2v-5m-8 7H7a2 2 0 01-2-2v-5"/></svg></span>
                    <span class="sidebar-label">Home</span>
                </a>
                <a href="{{ route('attendance.my') }}" class="nav-item {{ request()->routeIs('attendance.my') ? 'active' : '' }}" data-tip="My Attendance">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm4-7l2 2 4-4"/></svg></span>
                    <span class="sidebar-label">My Attendance</span>
                </a>
                <a href="{{ route('payroll.my-payslips') }}" class="nav-item {{ request()->routeIs('payroll.my-payslips') ? 'active' : '' }}" data-tip="My Payslips">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3 0-5 1.5-5 4s2 4 5 4 5-1.5 5-4-2-4-5-4zm0-5v5m0 8v5"/></svg></span>
                    <span class="sidebar-label">My Payslips</span>
                </a>
                <a href="{{ route('leave.index') }}" class="nav-item {{ request()->routeIs('leave.index', 'leave.create') ? 'active' : '' }}" data-tip="Leaves">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 7.5L3 10l7.5 2.5L21 12l-10.5 4.5V21l3-3m-3-10.5V3"/></svg></span>
                    <span class="sidebar-label">Leaves</span>
                </a>
                <a href="{{ route('overtime.index') }}" class="nav-item {{ request()->routeIs('overtime.index', 'overtime.create') ? 'active' : '' }}" data-tip="Overtime Request">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                    <span class="sidebar-label">Overtime Request</span>
                </a>
            </div>
        </div>
        <div class="nav-group" data-group="docs-comms">
            <button type="button" class="group-label px-3 py-1 mt-2 w-full text-[10px] uppercase tracking-wide text-gray-400 flex items-center justify-between" data-group-toggle="docs-comms" aria-expanded="true">
                <span>Documents & Comms</span>
                <svg class="w-3 h-3 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
            </button>
            <div class="group-sep"></div>
            <div class="group-content space-y-1">
                <a href="{{ route('documents.index') }}" class="nav-item {{ request()->routeIs('documents.index') ? 'active' : '' }}" data-tip="Personal Documents">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h7l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg></span>
                    <span class="sidebar-label">Personal Documents</span>
                </a>
                <a href="{{ route('memos.index') }}" class="nav-item {{ request()->routeIs('memos.index', 'memos.show') ? 'active' : '' }}" data-tip="Memos">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 5h10M7 9h10M7 13h6m-7 6h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                    <span class="sidebar-label">Memos</span>
                </a>
            </div>
        </div>
        @endif

        {{-- ═══ Non-Employee Dashboard ═══ --}}
        @if($user && !$showEmployeePortal)
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tip="Home">
            <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 14V9m0 10h6a2 2 0 002-2v-5m-8 7H7a2 2 0 01-2-2v-5"/></svg></span>
            <span class="sidebar-label">Home</span>
        </a>
        @endif

        {{-- ═══ Time, Attendance & Payroll ═══ --}}
        @if($hasTimeAttendance)
        <div class="nav-group" data-group="time-attendance">
            <button type="button" class="group-label px-3 py-1 mt-2 w-full text-[10px] uppercase tracking-wide text-gray-400 flex items-center justify-between" data-group-toggle="time-attendance" aria-expanded="true">
                <span>Time, Attendance & Payroll</span>
                <svg class="w-3 h-3 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
            </button>
            <div class="group-sep"></div>
            <div class="group-content space-y-1">
                @if($canAttendanceMgmt)
                <a href="{{ route('attendance.index') }}" class="nav-item {{ request()->routeIs('attendance.index') ? 'active' : '' }}" data-tip="Attendance Management">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm4-7l2 2 4-4"/></svg></span>
                    <span class="sidebar-label">Attendance Management</span>
                </a>
                @endif
                @if($canOvertimeMgmt)
                <a href="{{ route('overtime.admin') }}" class="nav-item {{ request()->routeIs('overtime.admin') ? 'active' : '' }}" data-tip="Overtime Management">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                    <span class="sidebar-label">Overtime Management</span>
                </a>
                @endif
                @if($canLeaveMgmt)
                <a href="{{ route('leave.admin') }}" class="nav-item {{ request()->routeIs('leave.admin') ? 'active' : '' }}" data-tip="Leave Management">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 7.5L3 10l7.5 2.5L21 12l-10.5 4.5V21l3-3m-3-10.5V3"/></svg></span>
                    <span class="sidebar-label">Leave Management</span>
                </a>
                @endif
                @if($canPayrollMgmt)
                <a href="{{ route('payroll.index') }}" class="nav-item {{ request()->routeIs('payroll.index') ? 'active' : '' }}" data-tip="Payroll Management">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 10-10 0v2M5 9h14l1 10H4L5 9zm5 5h4"/></svg></span>
                    <span class="sidebar-label">Payroll Management</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══ People & Organization ═══ --}}
        @if($hasPeopleOrg)
        <div class="nav-group" data-group="company">
            <button type="button" class="group-label px-3 py-1 mt-2 w-full text-[10px] uppercase tracking-wide text-gray-400 flex items-center justify-between" data-group-toggle="company" aria-expanded="true">
                <span>People & Organization</span>
                <svg class="w-3 h-3 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
            </button>
            <div class="group-sep"></div>
            <div class="group-content space-y-1">
                @if($canEmployees)
                <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}" data-tip="Employees">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 11a4 4 0 10-6 0m6 0a4 4 0 11-6 0"/></svg></span>
                    <span class="sidebar-label">Employees</span>
                </a>
                @endif
                @if($canRecruitment)
                <a href="{{ route('recruitment.index') }}" class="nav-item {{ request()->routeIs('recruitment.*') ? 'active' : '' }}" data-tip="Recruitment">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19a7 7 0 0114 0"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 8v6m3-3h-6"/></svg></span>
                    <span class="sidebar-label">Recruitment</span>
                </a>
                @endif
                @if($canDepartments)
                <a href="{{ route('departments.index') }}" class="nav-item {{ request()->routeIs('departments.*') ? 'active' : '' }}" data-tip="Departments">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M9 8h6M8 21V5a2 2 0 012-2h4a2 2 0 012 2v16"/></svg></span>
                    <span class="sidebar-label">Departments</span>
                </a>
                @endif
                @if($canPositions)
                <a href="{{ route('positions.index') }}" class="nav-item {{ request()->routeIs('positions.*') ? 'active' : '' }}" data-tip="Positions">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 12h4M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7zm6-4h4a2 2 0 012 2v1H8V5a2 2 0 012-2z"/></svg></span>
                    <span class="sidebar-label">Positions</span>
                </a>
                @endif
                @if($canDocuments)
                <a href="{{ route('memos.admin') }}" class="nav-item {{ request()->routeIs('memos.admin', 'memos.create', 'memos.edit') ? 'active' : '' }}" data-tip="Memos">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 5h10M7 9h10M7 13h6m-7 6h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
                    <span class="sidebar-label">Memos</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══ Inventory ═══ --}}
        @if($hasInventory)
        <div class="nav-group" data-group="inventory">
            <button type="button" class="group-label px-3 py-1 mt-2 w-full text-[10px] uppercase tracking-wide text-gray-400 flex items-center justify-between" data-group-toggle="inventory" aria-expanded="true">
                <span>Inventory</span>
                <svg class="w-3 h-3 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
            </button>
            <div class="group-sep"></div>
            <div class="group-content space-y-1">
                @if($canInventoryItems || $canInventoryReports)
                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.index') ? 'active' : '' }}" data-tip="Dashboard">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></span>
                    <span class="sidebar-label">Dashboard</span>
                </a>
                @endif
                @if($canInventoryItems)
                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.index', 'inventory.show', 'inventory.create', 'inventory.edit') ? 'active' : '' }}" data-tip="Items">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></span>
                    <span class="sidebar-label">Items</span>
                </a>
                @endif
                @if($canInventoryReports)
                <a href="{{ route('inventory.reports') }}" class="nav-item {{ request()->routeIs('inventory.reports') ? 'active' : '' }}" data-tip="Reports">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></span>
                    <span class="sidebar-label">Reports</span>
                </a>
                @endif
                @if($canInventoryItems)
                <a href="{{ route('inventory.purchase-orders') }}" class="nav-item {{ request()->routeIs('inventory.purchase-orders*') ? 'active' : '' }}" data-tip="Order Supplies">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                    <span class="sidebar-label">Order Supplies</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══ Sales & POS ═══ --}}
        @if($hasSalesPOS)
        <div class="nav-group" data-group="sales-pos">
            <button type="button" class="group-label px-3 py-1 mt-2 w-full text-[10px] uppercase tracking-wide text-gray-400 flex items-center justify-between" data-group-toggle="sales-pos" aria-expanded="true">
                <span>Sales & POS</span>
                <svg class="w-3 h-3 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
            </button>
            <div class="group-sep"></div>
            <div class="group-content space-y-1">
                <a href="{{ route('inventory.pos') }}" class="nav-item {{ request()->routeIs('inventory.pos*') ? 'active' : '' }}" data-tip="POS Terminal">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg></span>
                    <span class="sidebar-label">POS Terminal</span>
                </a>
                <a href="{{ route('inventory.transactions') }}" class="nav-item {{ request()->routeIs('inventory.transactions*') ? 'active' : '' }}" data-tip="Transactions">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></span>
                    <span class="sidebar-label">Transactions</span>
                </a>
            </div>
        </div>
        @endif

        {{-- ═══ Administration ═══ --}}
        @if($hasAdminTools)
        <div class="nav-group" data-group="administration">
            <button type="button" class="group-label px-3 py-1 mt-2 w-full text-[10px] uppercase tracking-wide text-gray-400 flex items-center justify-between" data-group-toggle="administration" aria-expanded="true">
                <span>Administration</span>
                <svg class="w-3 h-3 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
            </button>
            <div class="group-sep"></div>
            <div class="group-content space-y-1">
                @if($isAdminRole || $isHrRole || $isSA)
                <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}" data-tip="Management Hub">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></span>
                    <span class="sidebar-label">Management Hub</span>
                </a>
                @endif
                @if($isAdminRole || $isSA)
                <a href="{{ route('positions.index') }}" class="nav-item {{ request()->routeIs('positions.*') ? 'active' : '' }}" data-tip="Access Control">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
                    <span class="sidebar-label">Access Control</span>
                </a>
                @endif
                @if($isAdminRole || $canAudit || $isSA)
                <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}" data-tip="System Management">
                    <span class="nav-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                    <span class="sidebar-label">System Management</span>
                </a>
                @endif
                {{-- Print Server: route not yet implemented --}}
            </div>
        </div>
        @endif
    </nav>
</aside>
