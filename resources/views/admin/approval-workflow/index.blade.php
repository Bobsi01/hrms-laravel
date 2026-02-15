@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Payroll Approval Workflow</h1>
        <p class="text-sm text-slate-500 mt-0.5">Configure the sequential approval chain for payroll batches.</p>
    </div>
    <div>
        <button onclick="document.getElementById('addApproverModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ Add Approver</button>
    </div>
</div>

<div class="card">
    <div class="card-header"><span>Approval Chain</span></div>
    <div class="card-body">
        @if($approvers->isEmpty())
            <p class="text-sm text-slate-400 py-4 text-center">No approvers configured. Add approvers to set up the approval chain.</p>
        @else
            <div class="space-y-3" id="approverList">
                @foreach($approvers as $idx => $approver)
                <div class="flex items-center gap-4 border border-slate-200 rounded-lg p-4 bg-white" data-id="{{ $approver->id }}">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        {{ $approver->step_order }}
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-slate-800">{{ $approver->user_name ?? 'Unknown User' }}</div>
                        <div class="text-xs text-slate-400">
                            Scope: {{ ucfirst($approver->scope ?? 'all') }}
                            @if($approver->scope_branch_id) · Branch #{{ $approver->scope_branch_id }} @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!$loop->first)
                        <form method="POST" action="{{ route('admin.approval-workflow.reorder') }}" class="inline">
                            @csrf
                            <input type="hidden" name="order" value="{{ json_encode($approvers->pluck('id')->values()->toArray()) }}">
                            <button type="button" onclick="moveApprover({{ $approver->id }}, 'up')" class="btn-icon text-slate-400 hover:text-indigo-600" title="Move Up">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                        </form>
                        @endif
                        @if(!$loop->last)
                        <button type="button" onclick="moveApprover({{ $approver->id }}, 'down')" class="btn-icon text-slate-400 hover:text-indigo-600" title="Move Down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        @endif
                        <form method="POST" action="{{ route('admin.approval-workflow.destroy', $approver->id) }}" class="inline" data-confirm="Remove this approver from the chain?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon text-red-400 hover:text-red-600" title="Remove">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Add Approver Modal --}}
<div id="addApproverModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Add Approver</h3>
        <form method="POST" action="{{ route('admin.approval-workflow.store') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">User</label>
                    <select name="user_id" class="input-text mt-1" required>
                        <option value="">Select user…</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->full_name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Scope</label>
                    <select name="scope" class="input-text mt-1">
                        <option value="all">All Branches</option>
                        <option value="branch">Specific Branch</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Branch (if scoped)</label>
                    <select name="scope_branch_id" class="input-text mt-1">
                        <option value="">N/A</option>
                        @foreach($branches as $br)
                        <option value="{{ $br->id }}">{{ $br->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Step Order</label>
                    <input type="number" name="step_order" class="input-text mt-1" value="{{ ($approvers->max('step_order') ?? 0) + 1 }}" min="1">
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Add Approver</button>
                    <button type="button" onclick="document.getElementById('addApproverModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function moveApprover(id, direction) {
    const items = [...document.querySelectorAll('#approverList > div')];
    const ids = items.map(el => parseInt(el.dataset.id));
    const idx = ids.indexOf(id);
    if (direction === 'up' && idx > 0) { [ids[idx], ids[idx-1]] = [ids[idx-1], ids[idx]]; }
    if (direction === 'down' && idx < ids.length - 1) { [ids[idx], ids[idx+1]] = [ids[idx+1], ids[idx]]; }
    // Submit reorder via form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.approval-workflow.reorder") }}';
    form.innerHTML = '@csrf' + '<input type="hidden" name="order" value=\'' + JSON.stringify(ids) + '\'>';
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
