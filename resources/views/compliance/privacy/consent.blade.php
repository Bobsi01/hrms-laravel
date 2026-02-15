@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Data Privacy Notice</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage your data privacy consents under the Data Privacy Act of 2012 (RA 10173)</p>
    </div>
</div>

{{-- Privacy Policy Notice --}}
<div class="card mb-6">
    <div class="card-header">
        <span>Privacy Notice — Republic Act No. 10173</span>
    </div>
    <div class="card-body prose prose-sm max-w-none text-slate-700 space-y-3">
        <p>
            In compliance with the <strong>Data Privacy Act of 2012 (RA 10173)</strong> and its implementing rules and regulations,
            we are committed to protecting your personal information. This notice explains how we collect, use, and protect your data.
        </p>
        <h4 class="text-sm font-semibold text-slate-900 mt-4">What We Collect</h4>
        <ul class="list-disc pl-5 text-sm space-y-1">
            <li>Personal information (name, address, contact details, date of birth)</li>
            <li>Employment information (position, department, hire date, salary)</li>
            <li>Government identification numbers (TIN, SSS, PhilHealth, Pag-IBIG)</li>
            <li>Attendance and leave records</li>
            <li>Payroll and compensation data</li>
        </ul>
        <h4 class="text-sm font-semibold text-slate-900 mt-4">How We Use Your Data</h4>
        <ul class="list-disc pl-5 text-sm space-y-1">
            <li>Processing payroll and statutory contributions</li>
            <li>Compliance with BIR, SSS, PhilHealth, and Pag-IBIG requirements</li>
            <li>Human resource management and performance evaluation</li>
            <li>Internal communications and company administration</li>
        </ul>
        <h4 class="text-sm font-semibold text-slate-900 mt-4">Your Rights</h4>
        <ul class="list-disc pl-5 text-sm space-y-1">
            <li><strong>Right to Access</strong> — You may request access to your personal data</li>
            <li><strong>Right to Rectification</strong> — You may request correction of inaccurate data via <a href="{{ route('corrections.index') }}" class="text-indigo-600 hover:underline">Data Correction Requests</a></li>
            <li><strong>Right to Erasure</strong> — Subject to legal retention requirements (e.g., 10-year payroll retention per BIR regulations)</li>
            <li><strong>Right to Object</strong> — You may withdraw optional consents at any time</li>
        </ul>
        <h4 class="text-sm font-semibold text-slate-900 mt-4">Data Retention</h4>
        <p class="text-sm">
            Payroll and tax records are retained for a minimum of <strong>{{ config('hrms.retention.payroll_records', 10) }} years</strong> as required by the Bureau of Internal Revenue (BIR).
            Employment records are retained for the duration of employment plus <strong>{{ config('hrms.retention.employment_records', 5) }} years</strong>. You may request erasure of non-mandatory data.
        </p>
    </div>
</div>

{{-- Consent Form --}}
<div class="card">
    <div class="card-header">
        <span>Your Consent Preferences</span>
    </div>
    <div class="card-body">
        @if($allRequired)
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 mb-5 flex items-start gap-2">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-emerald-700">All required consents have been given. You may update your preferences at any time.</span>
        </div>
        @else
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-5 flex items-start gap-2">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <span class="text-sm text-amber-700">Please review and provide the required consents below.</span>
        </div>
        @endif

        <form method="POST" action="{{ route('privacy.consent.store') }}" class="space-y-4">
            @csrf

            @foreach($consentTypes as $type => $config)
            <div class="border border-slate-200 rounded-lg p-4 {{ $config['required'] ? 'bg-slate-50' : '' }}">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="consents[]" value="{{ $type }}"
                        class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        {{ ($existingConsents[$type] ?? false) ? 'checked' : '' }}
                        {{ $config['required'] ? 'required' : '' }}>
                    <div>
                        <div class="text-sm font-medium text-slate-900">
                            {{ $config['label'] }}
                            @if($config['required'])
                            <span class="text-red-500 text-xs ml-1">Required</span>
                            @else
                            <span class="text-slate-400 text-xs ml-1">Optional</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 mt-1">{{ $config['description'] }}</p>
                        @if(isset($existingConsents[$type]))
                        <p class="text-xs text-slate-400 mt-1">
                            Status: {{ ($existingConsents[$type] ?? false) ? 'Consented' : 'Withdrawn' }}
                        </p>
                        @endif
                    </div>
                </label>
            </div>
            @endforeach

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn btn-primary">Save Preferences</button>
            </div>
        </form>
    </div>
</div>
@endsection
