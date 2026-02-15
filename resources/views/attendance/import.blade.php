@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('attendance.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Attendance</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">Import Attendance (CSV)</h1>
    <p class="text-sm text-slate-500 mt-0.5">Upload a CSV file to bulk-import attendance records</p>
</div>

<div class="card max-w-2xl">
    <div class="card-header"><span>Upload CSV File</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('attendance.process-import') }}" enctype="multipart/form-data">
            @csrf

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5 text-sm text-blue-800">
                <p class="font-semibold mb-1">CSV Format Requirements:</p>
                <ul class="list-disc list-inside space-y-0.5 text-blue-700">
                    <li>Columns: <code class="text-xs bg-blue-100 px-1 rounded">employee_code, date, time_in, time_out, overtime_minutes, status</code></li>
                    <li>Date format: <code class="text-xs bg-blue-100 px-1 rounded">YYYY-MM-DD</code></li>
                    <li>Time format: <code class="text-xs bg-blue-100 px-1 rounded">HH:MM</code> (24-hour)</li>
                    <li>Status: <code class="text-xs bg-blue-100 px-1 rounded">present</code>, <code class="text-xs bg-blue-100 px-1 rounded">late</code>, <code class="text-xs bg-blue-100 px-1 rounded">absent</code>, <code class="text-xs bg-blue-100 px-1 rounded">on-leave</code>, <code class="text-xs bg-blue-100 px-1 rounded">holiday</code></li>
                    <li>First row must be column headers</li>
                    <li>Existing records for the same employee + date will be updated</li>
                </ul>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1 required">CSV File</label>
                <input type="file" name="csv_file" accept=".csv" class="input-text w-full @error('csv_file') input-error @enderror" required>
                @error('csv_file') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-200">
                <button type="submit" class="btn btn-primary">Upload & Import</button>
                <a href="{{ route('attendance.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
