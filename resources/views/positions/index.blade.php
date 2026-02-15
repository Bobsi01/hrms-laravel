@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Positions</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage organizational positions</p>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Employees</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $pos)
                <tr>
                    <td class="font-medium text-slate-900">{{ $pos->title }}</td>
                    <td>{{ $pos->department->name ?? '—' }}</td>
                    <td>{{ $pos->employees_count ?? 0 }}</td>
                    <td>
                        <div class="action-links">
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-slate-500 py-8">No positions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
