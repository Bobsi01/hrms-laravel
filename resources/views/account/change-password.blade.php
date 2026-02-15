@extends('layouts.app')
@section('title', 'Change Password')

@section('content')
<div class="mb-6">
    <a href="{{ route('account.profile') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Profile</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Change Password</h1>
    <p class="text-sm text-slate-500 mt-0.5">Update your account password</p>
</div>

<div class="card max-w-lg">
    <div class="card-header">New Password</div>
    <div class="card-body">
        <form method="POST" action="{{ route('account.change-password.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700 required">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="input-text mt-1 @error('current_password') input-error @enderror" required>
                    @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-slate-700 required">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="input-text mt-1 @error('new_password') input-error @enderror" required minlength="8">
                    @error('new_password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-slate-700 required">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="input-text mt-1" required minlength="8">
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Update Password</button>
                <a href="{{ route('account.profile') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
