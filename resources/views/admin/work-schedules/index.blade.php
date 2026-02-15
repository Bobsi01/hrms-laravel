@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Work Schedule Management</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage work schedule templates and assign them to employees.</p>
    </div>
    <div>
        <button onclick="document.getElementById('createTemplateModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ New Template</button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Templates --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header"><span>Schedule Templates</span></div>
            <div class="card-body">
                @if($templates->isEmpty())
                    <p class="text-sm text-slate-400 py-4 text-center">No templates defined yet.</p>
                @else
                    <table class="table-basic">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Break (min)</th>
                                <th>Work Days</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $tpl)
                            <tr>
                                <td class="font-medium">
                                    {{ $tpl->name }}
                                    @if($tpl->description)<div class="text-xs text-slate-400">{{ Str::limit($tpl->description, 40) }}</div>@endif
                                </td>
                                <td>{{ $tpl->time_in ? \Carbon\Carbon::parse($tpl->time_in)->format('h:i A') : '—' }}</td>
                                <td>{{ $tpl->time_out ? \Carbon\Carbon::parse($tpl->time_out)->format('h:i A') : '—' }}</td>
                                <td>{{ $tpl->break_minutes ?? '—' }}</td>
                                <td class="text-xs">{{ $tpl->work_days ?? '—' }}</td>
                                <td>
                                    @if($tpl->is_active)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-500">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-links">
                                        @if(!($editTemplate && $editTemplate->id === $tpl->id))
                                        <a href="{{ route('admin.work-schedules.index', ['edit' => $tpl->id]) }}">Edit</a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.work-schedules.destroy-template', $tpl) }}" class="inline" data-confirm="Deactivate this template?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600">Deactivate</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Active Assignments --}}
        <div class="card mt-6">
            <div class="card-header"><span>Active Employee Assignments</span></div>
            <div class="card-body">
                @if($assignments->isEmpty())
                    <p class="text-sm text-slate-400 py-4 text-center">No active assignments.</p>
                @else
                    <table class="table-basic">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Schedule</th>
                                <th>Effective From</th>
                                <th>Effective Until</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $asn)
                            <tr>
                                <td class="font-medium">{{ $asn->employee->last_name ?? '' }}, {{ $asn->employee->first_name ?? '' }}</td>
                                <td>{{ $asn->template->name ?? '—' }}</td>
                                <td>{{ $asn->effective_date ? \Carbon\Carbon::parse($asn->effective_date)->format('M d, Y') : '—' }}</td>
                                <td>{{ $asn->end_date ? \Carbon\Carbon::parse($asn->end_date)->format('M d, Y') : 'Ongoing' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar: Edit or Assign --}}
    <div>
        @if($editTemplate)
        <div class="card mb-6">
            <div class="card-header"><span>Edit Template</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.work-schedules.update-template', $editTemplate) }}">
                    @csrf @method('PUT')
                    @include('admin.work-schedules._template-form', ['tpl' => $editTemplate])
                    <div class="flex items-center gap-2 mt-3">
                        <button type="submit" class="btn btn-primary text-sm">Update</button>
                        <a href="{{ route('admin.work-schedules.index') }}" class="btn btn-outline text-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><span>Assign Schedule to Employee</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.work-schedules.assign') }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">Employee</label>
                            <select name="employee_id" class="input-text mt-1" required>
                                <option value="">Select employee…</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">Template</label>
                            <select name="template_id" class="input-text mt-1" required>
                                <option value="">Select schedule…</option>
                                @foreach($templates->where('is_active', true) as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 required">Effective Date</label>
                            <input type="date" name="effective_date" class="input-text mt-1" required value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">End Date</label>
                            <input type="date" name="end_date" class="input-text mt-1">
                            <p class="text-xs text-slate-400 mt-0.5">Leave blank for ongoing.</p>
                        </div>
                        <button type="submit" class="btn btn-primary text-sm w-full">Assign Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Create Template Modal --}}
<div id="createTemplateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">New Schedule Template</h3>
        <form method="POST" action="{{ route('admin.work-schedules.store-template') }}">
            @csrf
            @include('admin.work-schedules._template-form', ['tpl' => null])
            <div class="flex items-center gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Create Template</button>
                <button type="button" onclick="document.getElementById('createTemplateModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
