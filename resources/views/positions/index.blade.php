@extends('layouts.app')
@section('title', 'Positions')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Positions</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage organizational positions and their permissions</p>
    </div>
    @if($canWrite)
    <div class="flex items-center gap-2">
        <a href="{{ route('positions.create') }}" class="btn btn-primary">+ Add Position</a>
    </div>
    @endif
</div>

{{-- Search --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('positions.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search positions..." class="input-text flex-1">
            <button type="submit" class="btn btn-secondary">Search</button>
            @if(request('search'))
                <a href="{{ route('positions.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>All Positions ({{ $positions->total() }})</span>
    </div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Position Title</th>
                    <th>Department</th>
                    <th>Base Salary</th>
                    <th>Employees</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $pos)
                <tr>
                    <td class="font-medium text-slate-900">{{ $pos->name }}</td>
                    <td>{{ $pos->department->name ?? '—' }}</td>
                    <td>{{ $pos->base_salary ? '₱' . number_format($pos->base_salary, 2) : '—' }}</td>
                    <td>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700">{{ $pos->employees_count ?? 0 }}</span>
                    </td>
                    <td>
                        <div class="action-links">
                            @if($canWrite)
                                <a href="{{ route('positions.edit', $pos) }}">Edit</a>
                                <a href="{{ route('positions.permissions', $pos) }}">Permissions</a>
                                <form method="POST" action="{{ route('positions.destroy', $pos) }}" class="inline" data-confirm="Delete position &quot;{{ $pos->name }}&quot;? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                </form>
                            @else
                                <a href="{{ route('positions.permissions', $pos) }}">View Permissions</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-8">No positions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($positions->hasPages())
<div class="mt-4">{{ $positions->withQueryString()->links() }}</div>
@endif
@endsection
