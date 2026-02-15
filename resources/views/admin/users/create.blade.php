@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to Users</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Create User Account</h1>
    <p class="text-sm text-slate-500 mt-0.5">Create a new user account and optionally link to an employee record.</p>
</div>

<div class="card max-w-2xl">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Full Name</label>
                    <input type="text" name="full_name" class="input-text mt-1 @error('full_name') input-error @enderror" value="{{ old('full_name') }}" required>
                    @error('full_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Email Address</label>
                    <input type="email" name="email" class="input-text mt-1 @error('email') input-error @enderror" value="{{ old('email') }}" required>
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Password</label>
                        <input type="password" name="password" class="input-text mt-1 @error('password') input-error @enderror" required minlength="8">
                        @error('password')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="input-text mt-1" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Branch</label>
                    <select name="branch_id" class="input-text mt-1">
                        <option value="">No Branch</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Link to Employee</label>
                    <select name="employee_id" class="input-text mt-1">
                        <option value="">None (standalone account)</option>
                        @foreach($unlinkedEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->last_name }}, {{ $emp->first_name }} — {{ $emp->department->name ?? 'No dept' }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-0.5">Only employees without existing user accounts are shown.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" class="input-text mt-1">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_system_admin" value="1" {{ old('is_system_admin') ? 'checked' : '' }} class="rounded border-slate-300">
                        <span class="text-slate-700">Grant System Administrator privileges</span>
                    </label>
                    <p class="text-xs text-slate-400 ml-6">System admins bypass all permission checks.</p>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
