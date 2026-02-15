@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Payroll Runs</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage payroll processing</p>
    </div>
</div>

<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Run #</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Batches</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                <tr>
                    <td class="font-medium text-slate-900">#{{ $run->id }}</td>
                    <td>{{ $run->cutoffPeriod->label ?? ($run->pay_period_start . ' — ' . $run->pay_period_end) }}</td>
                    <td>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                            {{ $run->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($run->status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ucfirst($run->status) }}
                        </span>
                    </td>
                    <td>{{ $run->batches_count ?? 0 }}</td>
                    <td class="text-slate-500 text-sm">{{ \Carbon\Carbon::parse($run->created_at)->format('M d, Y h:i A') }}</td>
                    <td>
                        <div class="action-links">
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">No payroll runs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($runs, 'links'))
<div class="mt-4">{{ $runs->withQueryString()->links() }}</div>
@endif
@endsection
