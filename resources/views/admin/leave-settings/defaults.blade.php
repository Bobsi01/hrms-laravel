@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Leave Default Settings</h1>
        <p class="text-sm text-slate-500 mt-0.5">Configure global and department-level leave entitlement defaults.</p>
    </div>
    <div>
        <a href="{{ route('admin.leave-entitlements') }}" class="btn btn-outline text-sm">Balances &amp; Policies →</a>
    </div>
</div>

{{-- Global Defaults --}}
<div class="card mb-6">
    <div class="card-header"><span>Global Leave Defaults (days per year)</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.leave-defaults.save-globals') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($leaveTypes as $type => $label)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
                    <input type="number" name="defaults[{{ $type }}]" class="input-text"
                        value="{{ $globalDefaults[$type] ?? config('hrms.leave_entitlements.' . $type, 0) }}"
                        min="0" step="0.5">
                    <p class="text-xs text-slate-400 mt-0.5">System default: {{ config('hrms.leave_entitlements.' . $type, 0) }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Global Defaults</button>
            </div>
        </form>
    </div>
</div>

{{-- Department Overrides --}}
<div class="card">
    <div class="card-header"><span>Department Overrides</span></div>
    <div class="card-body">
        <p class="text-sm text-slate-500 mb-4">Override global defaults for specific departments. Leave blank to inherit global value.</p>

        @if($departments->isEmpty())
            <p class="text-sm text-slate-400 py-4 text-center">No departments found.</p>
        @else
            @foreach($departments as $dept)
            <div class="border border-slate-200 rounded-lg p-4 mb-4">
                <form method="POST" action="{{ route('admin.leave-defaults.save-department') }}">
                    @csrf
                    <input type="hidden" name="department_id" value="{{ $dept->id }}">

                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-slate-800">{{ $dept->name }}</h4>
                        <span class="text-xs text-slate-400">{{ $dept->employees_count ?? 0 }} employees</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($leaveTypes as $type => $label)
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">{{ $label }}</label>
                            <input type="number" name="overrides[{{ $type }}]" class="input-text text-sm"
                                value="{{ $deptOverrides[$dept->id][$type] ?? '' }}"
                                placeholder="{{ $globalDefaults[$type] ?? config('hrms.leave_entitlements.' . $type, 0) }}"
                                min="0" step="0.5">
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline text-xs">Save {{ $dept->name }} Overrides</button>
                    </div>
                </form>
            </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
