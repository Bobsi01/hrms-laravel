@extends('layouts.app')
@section('title', 'Departments')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Departments</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage organizational departments</p>
    </div>
    @if($canWrite)
    <div class="flex items-center gap-2">
        <a href="{{ route('departments.create') }}" class="btn btn-primary">+ Add Department</a>
    </div>
    @endif
</div>

{{-- Search --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('departments.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search departments..." class="input-text flex-1">
            <button type="submit" class="btn btn-secondary">Search</button>
            @if(request('search'))
                <a href="{{ route('departments.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>All Departments ({{ $departments->total() }})</span>
    </div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Description</th>
                    <th>Employees</th>
                    <th>Supervisors</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                <tr>
                    <td class="font-medium text-slate-900">{{ $dept->name }}</td>
                    <td class="text-slate-500 text-sm">{{ Str::limit($dept->description, 60) ?: '—' }}</td>
                    <td>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700">{{ $dept->employees_count ?? 0 }}</span>
                    </td>
                    <td>{{ $dept->supervisors_count ?? 0 }}</td>
                    <td>
                        <div class="action-links">
                            @if($canWrite)
                                <a href="{{ route('departments.edit', $dept) }}">Edit</a>
                                <a href="{{ route('departments.supervisors', $dept) }}">Supervisors</a>
                                <form method="POST" action="{{ route('departments.destroy', $dept) }}" class="inline" data-confirm="Delete department &quot;{{ $dept->name }}&quot;? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                </form>
                            @else
                                <span class="text-slate-400 text-sm">View only</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-8">No departments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($departments->hasPages())
<div class="mt-4">{{ $departments->withQueryString()->links() }}</div>
@endif
@endsection
