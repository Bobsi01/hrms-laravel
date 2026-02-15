@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">User Accounts</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage user accounts, credentials, and employee linkage.</p>
    </div>
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary text-sm">+ Create User</a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-500">Total Users</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['active'] }}</div>
            <div class="text-xs text-slate-500">Active</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['inactive'] }}</div>
            <div class="text-xs text-slate-500">Inactive</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['admins'] }}</div>
            <div class="text-xs text-slate-500">System Admins</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="input-text text-sm flex-1">
            <select name="status" class="input-text text-sm w-40">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-outline text-sm">Filter</button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header"><span>User Accounts</span></div>
    <div class="card-body">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Employee</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Admin</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="font-medium">{{ $user->full_name }}</td>
                    <td class="text-sm text-slate-500">{{ $user->email }}</td>
                    <td class="text-sm">
                        @if($user->employee)
                            {{ $user->employee->last_name }}, {{ $user->employee->first_name }}
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="text-sm">{{ $user->branch->name ?? '—' }}</td>
                    <td>
                        @if($user->status === 'active')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Active</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_system_admin)
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Admin</span>
                        @endif
                    </td>
                    <td class="text-xs text-slate-400">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, h:i A') : 'Never' }}</td>
                    <td>
                        <div class="action-links">
                            <a href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            @if($user->id !== config('hrms.superadmin_id', 1))
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" data-confirm="Delete this user account?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-slate-400 py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $users->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
