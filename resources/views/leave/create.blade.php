@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">File Leave Request</h1>
        <p class="text-sm text-slate-500 mt-0.5">Submit a new leave request for approval</p>
    </div>
</div>

<div class="card max-w-2xl">
    <div class="card-body">
        <form method="POST" action="{{ route('leave.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="leave_type" class="block text-sm font-medium text-slate-700 mb-1.5 required">Leave Type</label>
                <select id="leave_type" name="leave_type" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm">
                    <option value="">Select type...</option>
                    <option value="sick">Sick Leave</option>
                    <option value="vacation">Vacation Leave</option>
                    <option value="emergency">Emergency Leave</option>
                    <option value="unpaid">Unpaid Leave</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1.5 required">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1.5 required">End Date</label>
                    <input type="date" id="end_date" name="end_date" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm">
                </div>
            </div>
            <div>
                <label for="reason" class="block text-sm font-medium text-slate-700 mb-1.5 required">Reason</label>
                <textarea id="reason" name="reason" rows="3" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm" placeholder="Brief reason for leave..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="{{ route('leave.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
