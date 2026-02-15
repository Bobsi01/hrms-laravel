@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Employees</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage employee records</p>
    </div>
    <div class="flex items-center gap-2">
        {{-- Add button will go here --}}
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                class="input-text flex-1">
            <select name="department_id" class="input-text">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td class="font-medium text-slate-900">{{ $emp->full_name }}</td>
                    <td class="text-slate-600">{{ $emp->email }}</td>
                    <td>{{ $emp->department->name ?? '—' }}</td>
                    <td>{{ $emp->position->title ?? '—' }}</td>
                    <td>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                            {{ $emp->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($emp->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">No employees found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($employees, 'links'))
<div class="mt-4">{{ $employees->withQueryString()->links() }}</div>
@endif
@endsection
