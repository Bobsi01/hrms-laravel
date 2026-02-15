@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Employees</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage employee records</p>
    </div>
    <div class="flex items-center gap-2">
        @if($canWrite)
        <a href="{{ route('employees.create') }}" class="btn btn-primary">+ Add Employee</a>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</div>
            <div class="text-xs text-slate-500">Active</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-amber-600">{{ $stats['on_leave'] }}</div>
            <div class="text-xs text-slate-500">On Leave</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-400">{{ $stats['inactive'] }}</div>
            <div class="text-xs text-slate-500">Inactive</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code or email..."
                class="input-text flex-1">
            <select name="department_id" class="input-text">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
            <select name="status" class="input-text">
                <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="on-leave" {{ request('status') === 'on-leave' ? 'selected' : '' }}>On Leave</option>
                <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                <option value="resigned" {{ request('status') === 'resigned' ? 'selected' : '' }}>Resigned</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search', 'department_id', 'status']))
                <a href="{{ route('employees.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>Employee List</span>
        <span class="text-sm font-normal text-slate-500">{{ $employees->total() }} record(s)</span>
    </div>
    <div class="card-body overflow-x-auto p-0">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Employee</th>
                    <th>Email</th>
                    <th class="hidden md:table-cell">Department</th>
                    <th class="hidden md:table-cell">Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td class="text-slate-500 text-sm font-mono">{{ $emp->employee_code }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="user-avatar text-xs">{{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}</div>
                            <div>
                                <div class="font-medium text-slate-900">{{ $emp->full_name }}</div>
                                <div class="text-xs text-slate-400 md:hidden">{{ $emp->department->name ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-slate-600 text-sm">{{ $emp->email }}</td>
                    <td class="hidden md:table-cell">{{ $emp->department->name ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $emp->position->name ?? '—' }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'on-leave' => 'bg-amber-100 text-amber-700',
                                'terminated' => 'bg-red-100 text-red-700',
                                'resigned' => 'bg-slate-100 text-slate-600',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$emp->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($emp->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('employees.show', $emp) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                            @if($canWrite)
                            <a href="{{ route('employees.edit', $emp) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No employees found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($employees->hasPages())
<div class="mt-4">{{ $employees->withQueryString()->links() }}</div>
@endif
@endsection
