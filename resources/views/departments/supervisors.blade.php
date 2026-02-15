@extends('layouts.app')
@section('title', 'Department Supervisors — ' . $department->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('departments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Departments</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">{{ $department->name }} — Supervisors</h1>
    <p class="text-sm text-slate-500 mt-0.5">Manage supervisors for this department</p>
</div>

{{-- Add Supervisor --}}
<div class="card mb-6 max-w-2xl">
    <div class="card-header">Add Supervisor</div>
    <div class="card-body">
        <form method="POST" action="{{ route('departments.supervisors.add', $department) }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <select name="supervisor_user_id" class="input-text flex-1 @error('supervisor_user_id') input-error @enderror" required>
                <option value="">Select a user…</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Add Supervisor</button>
        </form>
        @error('supervisor_user_id') <p class="field-error mt-1">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Current Supervisors --}}
<div class="card max-w-2xl">
    <div class="card-header">Current Supervisors ({{ $department->supervisors->count() }})</div>
    <div class="card-body">
        @if($department->supervisors->isEmpty())
            <p class="text-slate-500 text-sm py-4 text-center">No supervisors assigned yet.</p>
        @else
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->supervisors as $sup)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $sup->supervisor->full_name ?? 'Unknown' }}</td>
                        <td class="text-slate-500 text-sm">{{ $sup->supervisor->email ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('departments.supervisors.remove', [$department, $sup]) }}" class="inline" data-confirm="Remove this supervisor?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
