@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Data Correction Requests</h1>
        <p class="text-sm text-slate-500 mt-0.5">Review and process employee data correction requests (RA 10173 compliance)</p>
    </div>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['pending'] }}</div>
            <div class="text-xs text-slate-500">Pending</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['approved'] }}</div>
            <div class="text-xs text-slate-500">Approved</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['completed'] }}</div>
            <div class="text-xs text-slate-500">Completed</div>
        </div>
    </div>
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['rejected'] }}</div>
            <div class="text-xs text-slate-500">Rejected</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header flex items-center justify-between">
        <span>Correction Requests</span>
        {{-- Status filter --}}
        <div class="flex items-center gap-2">
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'completed' => 'Completed', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.corrections.index', ['status' => $key]) }}"
                class="px-3 py-1 text-xs font-medium rounded-full {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>
    <div class="card-body overflow-x-auto">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Category</th>
                    <th>Field</th>
                    <th>Current Value</th>
                    <th>Requested Value</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="font-medium text-slate-900">
                        {{ $req->employee?->last_name ?? '—' }}, {{ $req->employee?->first_name ?? '' }}
                    </td>
                    <td>{{ $categories[$req->category] ?? ucfirst($req->category) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $req->field_name)) }}</td>
                    <td class="text-sm text-slate-500 max-w-[140px] truncate">{{ $req->current_value ?? '(empty)' }}</td>
                    <td class="text-sm font-medium max-w-[140px] truncate">{{ $req->requested_value }}</td>
                    <td class="text-sm text-slate-500 max-w-[180px] truncate" title="{{ $req->reason }}">{{ $req->reason }}</td>
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
                    <td class="text-slate-500 text-sm whitespace-nowrap">{{ $req->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($req->status === 'pending')
                        <div class="flex items-center gap-1">
                            {{-- Approve --}}
                            <form method="POST" action="{{ route('admin.corrections.approve', $req) }}" data-confirm="Approve this correction request? The employee's record will be updated automatically.">
                                @csrf
                                <input type="hidden" name="review_notes" value="">
                                <button type="submit" class="btn-icon text-emerald-600 hover:bg-emerald-50" title="Approve & Apply">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            {{-- Reject --}}
                            <button type="button" class="btn-icon text-red-600 hover:bg-red-50" title="Reject"
                                onclick="showRejectModal({{ $req->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @elseif($req->reviewer)
                        <span class="text-xs text-slate-400">by {{ $req->reviewer->name ?? 'Admin' }}</span>
                        @else
                        <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-slate-500 py-8">No correction requests found for this filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(method_exists($requests, 'links'))
<div class="mt-4">{{ $requests->withQueryString()->links() }}</div>
@endif

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Reject Correction Request</h3>
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label for="reject_notes" class="block text-sm font-medium text-slate-700 mb-1.5 required">Reason for Rejection</label>
                <textarea id="reject_notes" name="review_notes" rows="3" required
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm"
                    placeholder="Explain why this correction is being rejected..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="btn btn-outline" onclick="hideRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function showRejectModal(id) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = '/admin/corrections/' + id + '/reject';
        document.getElementById('reject_notes').value = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function hideRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    // Close on backdrop click
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) hideRejectModal();
    });
</script>
@endpush
@endsection
