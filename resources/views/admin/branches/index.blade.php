@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Branch Directory</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage company branches and locations.</p>
    </div>
    <a href="{{ route('admin.index') }}" class="btn btn-outline text-sm">← Back to Admin</a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $branches->count() }}</div>
            <div class="text-xs text-slate-500">Total Branches</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $branches->where('id', $defaultBranchId)->first()?->name ?? '—' }}</div>
            <div class="text-xs text-slate-500">Default Branch</div>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-[2fr,1fr] gap-6">
    {{-- Branch List --}}
    <div class="card">
        <div class="card-header"><span>Branches</span></div>
        <div class="card-body">
            @if($branches->isEmpty())
                <p class="text-sm text-slate-500 py-4 text-center">No branches found.</p>
            @else
                <table class="table-basic">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Employees</th>
                            <th>Accounts</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                        <tr>
                            <td><span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $branch->code }}</span></td>
                            <td class="font-medium">{{ $branch->name }}</td>
                            <td>{{ $branch->employees_count }}</td>
                            <td>{{ $branch->users_count }}</td>
                            <td class="text-sm text-slate-500">{{ Str::limit($branch->address, 40) }}</td>
                            <td>
                                <div class="action-links">
                                    <a href="{{ route('admin.branches.index', ['edit' => $branch->id]) }}">Edit</a>
                                    @if($branch->id !== $defaultBranchId && $branch->employees_count === 0 && $branch->users_count === 0)
                                    <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="inline" data-confirm="Delete this branch?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Create/Edit Form --}}
    <div class="card">
        <div class="card-header">
            <span>{{ $editBranch ? 'Edit Branch' : 'Add Branch' }}</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $editBranch ? route('admin.branches.update', $editBranch) : route('admin.branches.store') }}">
                @csrf
                @if($editBranch) @method('PUT') @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Code</label>
                        <input type="text" name="code" value="{{ old('code', $editBranch?->code) }}" class="input-text mt-1" required maxlength="20" placeholder="e.g. MAIN">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Name</label>
                        <input type="text" name="name" value="{{ old('name', $editBranch?->name) }}" class="input-text mt-1" required maxlength="255">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Address</label>
                        <textarea name="address" class="input-text mt-1" rows="3">{{ old('address', $editBranch?->address) }}</textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn btn-primary">{{ $editBranch ? 'Update' : 'Create' }} Branch</button>
                        @if($editBranch)
                        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline">Cancel</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
