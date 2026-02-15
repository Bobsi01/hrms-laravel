@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to Users</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Edit User: {{ $user->full_name }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">Update account details, password, and employee linkage.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Edit Form --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header"><span>Account Details</span></div>
            <div class="card-body">
                @if($isSuperadmin)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-sm text-amber-700">
                    This is the superadmin account. Some fields cannot be modified.
                </div>
                @endif

                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">Full Name</label>
                            <input type="text" name="full_name" class="input-text mt-1 @error('full_name') input-error @enderror"
                                value="{{ old('full_name', $user->full_name) }}" required {{ $isSuperadmin ? 'disabled' : '' }}>
                            @error('full_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">Email Address</label>
                            <input type="email" name="email" class="input-text mt-1 @error('email') input-error @enderror"
                                value="{{ old('email', $user->email) }}" required {{ $isSuperadmin ? 'disabled' : '' }}>
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Branch</label>
                            <select name="branch_id" class="input-text mt-1">
                                <option value="">No Branch</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Linked Employee</label>
                            <select name="employee_id" class="input-text mt-1">
                                <option value="">None</option>
                                @if($user->employee)
                                <option value="{{ $user->employee_id }}" selected>{{ $user->employee->last_name }}, {{ $user->employee->first_name }} (current)</option>
                                @endif
                                @foreach($unlinkedEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Status</label>
                            <select name="status" class="input-text mt-1" {{ $isSuperadmin ? 'disabled' : '' }}>
                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        @if(!$isSuperadmin)
                        <div>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_system_admin" value="1" {{ $user->is_system_admin ? 'checked' : '' }} class="rounded border-slate-300">
                                <span class="text-slate-700">System Administrator</span>
                            </label>
                        </div>
                        @endif

                        <div class="flex items-center gap-2 pt-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar: Password Reset & Info --}}
    <div>
        <div class="card mb-6">
            <div class="card-header"><span>Account Info</span></div>
            <div class="card-body text-sm space-y-2">
                <div class="flex justify-between"><span class="text-slate-500">User ID</span><span class="font-medium">{{ $user->id }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Created</span><span>{{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Last Login</span><span>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, h:i A') : 'Never' }}</span></div>
            </div>
        </div>

        @if(!$isSuperadmin)
        <div class="card mb-6">
            <div class="card-header"><span>Reset Password</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" data-confirm="Reset this user's password?">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">New Password</label>
                            <input type="password" name="password" class="input-text mt-1" required minlength="8">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">Confirm</label>
                            <input type="password" name="password_confirmation" class="input-text mt-1" required>
                        </div>
                        <button type="submit" class="btn btn-warning text-sm w-full">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span>Danger Zone</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Permanently delete this user account? This cannot be undone.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger text-sm w-full">Delete Account</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
