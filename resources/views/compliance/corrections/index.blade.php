@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">My Data Correction Requests</h1>
        <p class="text-sm text-slate-500 mt-0.5">Request corrections to your personal or employment records (RA 10173 — Right to Rectification)</p>
    </div>
    <div class="flex items-center gap-2">
        @if($employee)
        <a href="{{ route('corrections.create') }}" class="btn btn-primary">+ New Request</a>
        @endif
    </div>
</div>

@if(!$employee)
<div class="card card-body text-center py-12">
    <svg class="mx-auto w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
    <p class="text-slate-500">No employee record is linked to your account. Contact HR to link your account.</p>
</div>
@else
<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Field</th>
                    <th>Requested Value</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Review Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="font-medium text-slate-900">{{ $categories[$req->category] ?? ucfirst($req->category) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $req->field_name)) }}</td>
                    <td class="max-w-[200px] truncate">{{ $req->requested_value }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending'   => 'bg-amber-100 text-amber-700',
                                'approved'  => 'bg-blue-100 text-blue-700',
                                'rejected'  => 'bg-red-100 text-red-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$req->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($req->status) }}
                        </span>
                    </td>
                    <td class="text-slate-500 text-sm">{{ $req->created_at->format('M d, Y h:i A') }}</td>
                    <td class="text-sm text-slate-500 max-w-[200px] truncate">{{ $req->review_notes ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-500 py-8">
                        No correction requests found. Click "+ New Request" to file one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($requests, 'links'))
<div class="mt-4">{{ $requests->withQueryString()->links() }}</div>
@endif
@endif
@endsection
