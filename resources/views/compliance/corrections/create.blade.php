@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">File Data Correction Request</h1>
        <p class="text-sm text-slate-500 mt-0.5">Request a correction to your personal or employment records</p>
    </div>
</div>

<div class="card max-w-2xl">
    <div class="card-body">
        <form method="POST" action="{{ route('corrections.store') }}" class="space-y-5">
            @csrf

            {{-- Category --}}
            <div>
                <label for="category" class="block text-sm font-medium text-slate-700 mb-1.5 required">Category</label>
                <select id="category" name="category" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm">
                    <option value="">Select category...</option>
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Field --}}
            <div>
                <label for="field_name" class="block text-sm font-medium text-slate-700 mb-1.5 required">Field to Correct</label>
                <select id="field_name" name="field_name" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm">
                    <option value="">Select a category first...</option>
                </select>
                @error('field_name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Current Value (display only) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Current Value</label>
                <div id="current_value_display" class="px-3.5 py-2.5 border border-slate-100 rounded-lg text-sm bg-slate-50 text-slate-500 min-h-[42px]">
                    Select a field to see the current value
                </div>
            </div>

            {{-- Requested Value --}}
            <div>
                <label for="requested_value" class="block text-sm font-medium text-slate-700 mb-1.5 required">Correct Value</label>
                <input type="text" id="requested_value" name="requested_value" required value="{{ old('requested_value') }}"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm"
                    placeholder="Enter the correct value...">
                @error('requested_value')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Reason --}}
            <div>
                <label for="reason" class="block text-sm font-medium text-slate-700 mb-1.5 required">Reason for Correction</label>
                <textarea id="reason" name="reason" rows="3" required
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm"
                    placeholder="Explain why this data is incorrect...">{{ old('reason') }}</textarea>
                @error('reason')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="{{ route('corrections.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const fields = @json($fields);
    const employeeData = @json([
        'first_name' => $employee->first_name ?? '',
        'last_name' => $employee->last_name ?? '',
        'email' => $employee->email ?? '',
        'phone' => $employee->phone ?? '',
        'address' => $employee->address ?? '',
        'employee_code' => $employee->employee_code ?? '',
        'hire_date' => $employee->hire_date ?? '',
        'employment_type' => $employee->employment_type ?? '',
        'tin' => $employee->tin ? '••••' . substr($employee->tin, -4) : '',
        'sss_number' => $employee->sss_number ? '••••' . substr($employee->sss_number, -4) : '',
        'philhealth_number' => $employee->philhealth_number ? '••••' . substr($employee->philhealth_number, -4) : '',
        'pagibig_number' => $employee->pagibig_number ? '••••' . substr($employee->pagibig_number, -4) : '',
        'salary' => '(Contact HR)',
        'bank_name' => $employee->bank_name ?? '',
        'bank_account_number' => $employee->bank_account_number ? '••••' . substr($employee->bank_account_number, -4) : '',
        'department_id' => $employee->department?->name ?? '',
        'position_id' => $employee->position?->title ?? '',
        'branch_id' => $employee->branch?->name ?? '',
    ]);

    const categoryEl = document.getElementById('category');
    const fieldEl = document.getElementById('field_name');
    const currentValueEl = document.getElementById('current_value_display');

    categoryEl.addEventListener('change', function() {
        const cat = this.value;
        fieldEl.innerHTML = '<option value="">Select field...</option>';
        currentValueEl.textContent = 'Select a field to see the current value';
        if (cat && fields[cat]) {
            Object.entries(fields[cat]).forEach(([key, label]) => {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = label;
                fieldEl.appendChild(opt);
            });
        }
    });

    fieldEl.addEventListener('change', function() {
        const field = this.value;
        if (field && employeeData[field] !== undefined) {
            currentValueEl.textContent = employeeData[field] || '(empty)';
        } else {
            currentValueEl.textContent = 'Select a field to see the current value';
        }
    });

    // Restore old values on validation failure
    @if(old('category'))
    categoryEl.value = '{{ old('category') }}';
    categoryEl.dispatchEvent(new Event('change'));
    setTimeout(() => {
        fieldEl.value = '{{ old('field_name') }}';
        fieldEl.dispatchEvent(new Event('change'));
    }, 50);
    @endif
</script>
@endpush
@endsection
