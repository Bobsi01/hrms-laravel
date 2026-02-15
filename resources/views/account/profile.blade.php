@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">My Profile</h1>
    <p class="text-sm text-slate-500 mt-0.5">View your account information</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="card">
        <div class="card-body text-center py-8">
            <div class="user-avatar mx-auto mb-4" style="width:4rem;height:4rem;font-size:1.5rem;">
                {{ strtoupper(substr($user->full_name ?? $user->email, 0, 1)) }}
            </div>
            <h2 class="text-lg font-bold text-slate-900">{{ $user->full_name ?? $user->email }}</h2>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            @if($user->role)
                <span class="mt-2 inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700">{{ ucfirst($user->role) }}</span>
            @endif
            @if($user->is_system_admin)
                <span class="mt-1 inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">System Admin</span>
            @endif
        </div>
    </div>

    {{-- Account Details --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Full Name</dt>
                        <dd class="font-medium text-slate-900">{{ $user->full_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="font-medium text-slate-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Role</dt>
                        <dd class="font-medium text-slate-900">{{ ucfirst($user->role ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($user->status ?? 'unknown') }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last Login</dt>
                        <dd class="font-medium text-slate-900">{{ $user->last_login?->format('M d, Y h:i A') ?? 'Never' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Account Created</dt>
                        <dd class="font-medium text-slate-900">{{ $user->created_at?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($user->employee)
        <div class="card">
            <div class="card-header">Employment Information</div>
            <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Employee Code</dt>
                        <dd class="font-medium text-slate-900">{{ $user->employee->employee_code ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Name</dt>
                        <dd class="font-medium text-slate-900">{{ $user->employee->full_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Department</dt>
                        <dd class="font-medium text-slate-900">{{ $user->employee->department->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Position</dt>
                        <dd class="font-medium text-slate-900">{{ $user->employee->position->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Branch</dt>
                        <dd class="font-medium text-slate-900">{{ $user->employee->branch->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Hire Date</dt>
                        <dd class="font-medium text-slate-900">{{ $user->employee->hire_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="card">
            <div class="card-header">Security</div>
            <div class="card-body">
                <a href="{{ route('account.change-password') }}" class="btn btn-secondary">Change Password</a>
            </div>
        </div>
    </div>
</div>
@endsection
