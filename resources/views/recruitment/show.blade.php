@extends('layouts.app')

@section('title', $recruitment->full_name)

@php
    $statusColors = [
        'new' => 'bg-blue-100 text-blue-700',
        'shortlist' => 'bg-amber-100 text-amber-700',
        'interviewed' => 'bg-indigo-100 text-indigo-700',
        'hired' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];
    $statusLabels = [
        'new' => 'Pending',
        'shortlist' => 'For Final Interview',
        'interviewed' => 'Interviewed',
        'hired' => 'Hired',
        'rejected' => 'Rejected',
    ];
@endphp

@section('content')
<div class="mb-6">
    <a href="{{ route('recruitment.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Pipeline</a>
</div>

{{-- Profile Header --}}
<div class="card mb-6">
    <div class="card-body">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="user-avatar text-lg">{{ strtoupper(substr($recruitment->full_name, 0, 2)) }}</div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $recruitment->full_name }}</h1>
                    <p class="text-sm text-slate-500">{{ $recruitment->position_applied ?? 'No position specified' }}</p>
                    <div class="flex items-center gap-3 mt-1 text-sm text-slate-500">
                        @if($recruitment->email)
                            <span>{{ $recruitment->email }}</span>
                        @endif
                        @if($recruitment->phone)
                            <span>{{ $recruitment->phone }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$recruitment->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $statusLabels[$recruitment->status] ?? ucfirst($recruitment->status) }}
                </span>
                @if($isConverted)
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-emerald-100 text-emerald-700">
                        Employee #{{ $recruitment->converted_employee_id }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Edit Profile Form --}}
        @if($canWrite && !$isConverted)
        <div class="card">
            <div class="card-header">Edit Profile</div>
            <div class="card-body">
                <form method="POST" action="{{ route('recruitment.update', $recruitment) }}">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1 required">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $recruitment->full_name) }}" class="input-text w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $recruitment->email) }}" class="input-text w-full">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone', $recruitment->phone) }}" class="input-text w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Position Applied</label>
                            <input type="text" name="position_applied" value="{{ old('position_applied', $recruitment->position_applied) }}" class="input-text w-full">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Template</label>
                            <select name="template_id" class="input-text w-full">
                                <option value="">No template</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" {{ old('template_id', $recruitment->template_id) == $tmpl->id ? 'selected' : '' }}>{{ $tmpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1 required">Status</label>
                            <select name="status" class="input-text w-full" required>
                                @foreach(['new' => 'Pending', 'shortlist' => 'For Final Interview', 'interviewed' => 'Interviewed', 'rejected' => 'Rejected'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('status', $recruitment->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="input-text w-full">{{ old('notes', $recruitment->notes) }}</textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Files Section --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <span>Files ({{ $recruitment->files->count() }})</span>
            </div>
            <div class="card-body">
                @if($missingFiles->isNotEmpty())
                    <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800">
                        <strong>Missing required files:</strong>
                        <ul class="list-disc list-inside mt-1">
                            @foreach($missingFiles as $label)
                                <li>{{ $label }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($recruitment->files->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-4">No files uploaded yet.</p>
                @else
                    <div class="space-y-2">
                        @foreach($recruitment->files as $file)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $file->label }}</div>
                                    <div class="text-xs text-slate-400">{{ basename($file->file_path) }}</div>
                                </div>
                            </div>
                            <a href="{{ Storage::disk('public')->url($file->file_path) }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Download</a>
                        </div>
                        @endforeach
                    </div>
                @endif

                @if($canWrite && !$isConverted)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <form method="POST" action="{{ route('recruitment.upload-file', $recruitment) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <input type="text" name="label" placeholder="File label (e.g. Resume)" class="input-text flex-1 @error('label') input-error @enderror" required>
                        <input type="file" name="file" class="input-text @error('file') input-error @enderror" required>
                        <button type="submit" class="btn btn-outline">Upload</button>
                    </form>
                    @error('label') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    @error('file') <p class="field-error mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Info --}}
        <div class="card">
            <div class="card-header">Details</div>
            <div class="card-body space-y-3">
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase">Template</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">{{ $recruitment->template?->name ?? 'None' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase">Applied</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">{{ $recruitment->created_at?->format('M d, Y h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase">Last Updated</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">{{ $recruitment->updated_at?->format('M d, Y h:i A') }}</dd>
                </div>
                @if($recruitment->notes)
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase">Notes</dt>
                    <dd class="mt-0.5 text-sm text-slate-700">{{ $recruitment->notes }}</dd>
                </div>
                @endif
            </div>
        </div>

        {{-- Transition to Employee --}}
        @if($canManage && !$isConverted)
        <div class="card border-emerald-200">
            <div class="card-header bg-emerald-50 text-emerald-800">Transition to Employee</div>
            <div class="card-body">
                @if($missingFiles->isNotEmpty())
                    <div class="p-3 rounded-lg bg-amber-50 text-sm text-amber-700 mb-3">
                        Cannot transition: missing required files. Please upload all required files first.
                    </div>
                @else
                <form method="POST" action="{{ route('recruitment.transition', $recruitment) }}" data-confirm="Are you sure you want to transition this applicant to an employee?">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1 required">Employee Code</label>
                            <input type="text" name="employee_code" value="{{ old('employee_code') }}" class="input-text w-full text-sm" required>
                            @error('employee_code') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1 required">First Name</label>
                                @php
                                    $nameParts = explode(' ', $recruitment->full_name, 2);
                                @endphp
                                <input type="text" name="first_name" value="{{ old('first_name', $nameParts[0] ?? '') }}" class="input-text w-full text-sm" required>
                                @error('first_name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1 required">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $nameParts[1] ?? '') }}" class="input-text w-full text-sm" required>
                                @error('last_name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1 required">Email</label>
                            <input type="email" name="emp_email" value="{{ old('emp_email', $recruitment->email) }}" class="input-text w-full text-sm" required>
                            @error('emp_email') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Phone</label>
                            <input type="text" name="emp_phone" value="{{ old('emp_phone', $recruitment->phone) }}" class="input-text w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Department</label>
                            <select name="department_id" class="input-text w-full text-sm">
                                <option value="">Select...</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Position</label>
                            <select name="position_id" class="input-text w-full text-sm">
                                <option value="">Select...</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-full mt-2">Transition to Employee</button>
                    </div>
                </form>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
