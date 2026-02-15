@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $employee->full_name }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Employee Profile — {{ $employee->employee_code }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('employees.index') }}" class="btn btn-outline">Back to List</a>
        @if($canWrite)
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">Edit Employee</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Profile Card --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="card card-body text-center">
            <div class="mx-auto w-20 h-20 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold mb-3">
                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
            </div>
            <h2 class="text-lg font-bold text-slate-900">{{ $employee->full_name }}</h2>
            <p class="text-sm text-slate-500">{{ $employee->position->name ?? 'No Position' }}</p>
            <p class="text-xs text-slate-400">{{ $employee->department->name ?? 'No Department' }}</p>
            <div class="mt-3">
                @php
                    $statusColors = [
                        'active' => 'bg-emerald-100 text-emerald-700',
                        'on-leave' => 'bg-amber-100 text-amber-700',
                        'terminated' => 'bg-red-100 text-red-700',
                        'resigned' => 'bg-slate-100 text-slate-600',
                    ];
                @endphp
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$employee->status] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ ucfirst($employee->status) }}
                </span>
            </div>
        </div>

        {{-- Portal Account --}}
        <div class="card">
            <div class="card-header"><span>Portal Account</span></div>
            <div class="card-body">
                @if($employee->user)
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Linked</span>
                        <span class="text-sm text-slate-600">{{ $employee->user->email }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Account status: {{ ucfirst($employee->user->status ?? 'active') }}</p>
                @else
                    <p class="text-sm text-slate-500">No portal account linked.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Details --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Personal Info --}}
        <div class="card">
            <div class="card-header"><span>Personal Information</span></div>
            <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Full Name</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Employee Code</dt>
                        <dd class="text-sm text-slate-900 mt-0.5 font-mono">{{ $employee->employee_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Email</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Phone</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->phone ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-slate-400 uppercase">Address</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->address ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Employment Details --}}
        <div class="card">
            <div class="card-header"><span>Employment Details</span></div>
            <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Department</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->department->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Position</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->position->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Branch</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->branch->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Employment Type</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ ucfirst($employee->employment_type ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Hire Date</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 uppercase">Monthly Salary</dt>
                        <dd class="text-sm text-slate-900 mt-0.5">
                            @if($employee->salary && $employee->salary > 0)
                                ₱{{ number_format($employee->salary, 2) }}
                            @elseif($employee->position && $employee->position->base_salary > 0)
                                ₱{{ number_format($employee->position->base_salary, 2) }} <span class="text-xs text-slate-400">(position rate)</span>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Danger Zone --}}
        @if($canWrite)
        <div class="card border-red-200">
            <div class="card-header text-red-600"><span>Danger Zone</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('employees.destroy', $employee) }}" data-confirm="Are you sure you want to delete {{ $employee->full_name }}? This action cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Employee</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
