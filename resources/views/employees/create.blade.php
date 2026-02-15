@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Add Employee</h1>
        <p class="text-sm text-slate-500 mt-0.5">Create a new employee record</p>
    </div>
</div>

<div class="card max-w-4xl">
    <div class="card-body">
        <form method="POST" action="{{ route('employees.store') }}" class="space-y-6">
            @csrf

            {{-- Personal Info --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-3">Personal Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="employee_code" class="block text-sm font-medium text-slate-700 mb-1 required">Employee Code</label>
                        <input type="text" id="employee_code" name="employee_code" value="{{ old('employee_code') }}" required class="input-text w-full @error('employee_code') input-error @enderror">
                        @error('employee_code') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div></div>
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1 required">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="input-text w-full @error('first_name') input-error @enderror">
                        @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1 required">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="input-text w-full @error('last_name') input-error @enderror">
                        @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1 required">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="input-text w-full @error('email') input-error @enderror">
                        @error('email') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="input-text w-full">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                        <textarea id="address" name="address" rows="2" class="input-text w-full">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Employment Details --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-3">Employment Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-slate-700 mb-1 required">Department</label>
                        <select id="department_id" name="department_id" required class="input-text w-full">
                            <option value="">Select department...</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="position_id" class="block text-sm font-medium text-slate-700 mb-1 required">Position</label>
                        <select id="position_id" name="position_id" required class="input-text w-full">
                            <option value="">Select position...</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}" {{ old('position_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}{{ $p->department ? ' ('.$p->department->name.')' : '' }}</option>
                            @endforeach
                        </select>
                        @error('position_id') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-slate-700 mb-1">Branch</label>
                        <select id="branch_id" name="branch_id" class="input-text w-full">
                            <option value="">Select branch...</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="hire_date" class="block text-sm font-medium text-slate-700 mb-1 required">Hire Date</label>
                        <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required class="input-text w-full">
                        @error('hire_date') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="employment_type" class="block text-sm font-medium text-slate-700 mb-1 required">Employment Type</label>
                        <select id="employment_type" name="employment_type" required class="input-text w-full">
                            <option value="regular" {{ old('employment_type') === 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="probationary" {{ old('employment_type') === 'probationary' ? 'selected' : '' }}>Probationary</option>
                            <option value="contract" {{ old('employment_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="part-time" {{ old('employment_type') === 'part-time' ? 'selected' : '' }}>Part-time</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1 required">Status</label>
                        <select id="status" name="status" required class="input-text w-full">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="probationary" {{ old('status') === 'probationary' ? 'selected' : '' }}>Probationary</option>
                            <option value="on-leave" {{ old('status') === 'on-leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="terminated" {{ old('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                            <option value="resigned" {{ old('status') === 'resigned' ? 'selected' : '' }}>Resigned</option>
                        </select>
                    </div>
                    <div>
                        <label for="salary" class="block text-sm font-medium text-slate-700 mb-1">Monthly Salary</label>
                        <input type="number" id="salary" name="salary" value="{{ old('salary') }}" step="0.01" min="0" class="input-text w-full">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary">Create Employee</button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
