<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    /**
     * Employee's own attendance (self-service).
     */
    public function my(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('attendance.my', ['attendances' => collect(), 'employee' => null]);
        }

        $query = Attendance::where('employee_id', $employee->id);

        if ($from = $request->input('from')) {
            $query->where('date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('date', '<=', $to);
        }

        $attendances = $query->orderByDesc('date')->paginate(20);

        return view('attendance.my', compact('attendances', 'employee'));
    }

    /**
     * Admin attendance management.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // Search
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->whereHas('employee', function ($q) use ($escaped) {
                $q->where('first_name', 'ilike', "%{$escaped}%")
                  ->orWhere('last_name', 'ilike', "%{$escaped}%")
                  ->orWhere('employee_code', 'ilike', "%{$escaped}%");
            });
        }

        // Date range filter (default: current month)
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());
        $query->whereBetween('date', [$from, $to]);

        // Stats
        $statsQuery = (clone $query);
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'present' => (clone $statsQuery)->where('status', 'present')->count(),
            'late' => (clone $statsQuery)->where('status', 'late')->count(),
            'absent' => (clone $statsQuery)->where('status', 'absent')->count(),
            'on_leave' => (clone $statsQuery)->where('status', 'on-leave')->count(),
        ];

        $canWrite = $this->permissions->userCan('attendance', 'attendance_admin', 'write');

        $attendances = $query->orderByDesc('date')->paginate(50);

        return view('attendance.index', compact('attendances', 'stats', 'from', 'to', 'canWrite'));
    }

    /**
     * Create attendance record form.
     */
    public function create()
    {
        $employees = Employee::where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('attendance.create', compact('employees'));
    }

    /**
     * Store a new attendance record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'overtime_minutes' => 'nullable|integer|min:0',
            'status' => 'required|in:present,late,absent,on-leave,holiday',
        ]);

        $attendance = Attendance::create($validated);

        $this->audit->actionLog('attendance', 'create', 'success', [
            'attendance_id' => $attendance->id,
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Import attendance from CSV.
     */
    public function import(Request $request)
    {
        return view('attendance.import');
    }

    /**
     * Process CSV import.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);
        $imported = 0;
        $errors = [];

        while ($row = fgetcsv($handle)) {
            if (count($row) < 4) continue;

            $data = array_combine($header, $row);
            $employeeCode = $data['employee_code'] ?? $data['Employee Code'] ?? null;
            $date = $data['date'] ?? $data['Date'] ?? null;
            $timeIn = $data['time_in'] ?? $data['Time In'] ?? null;
            $timeOut = $data['time_out'] ?? $data['Time Out'] ?? null;

            if (!$employeeCode || !$date) {
                $errors[] = "Row missing employee_code or date.";
                continue;
            }

            $employee = Employee::where('employee_code', $employeeCode)->first();
            if (!$employee) {
                $errors[] = "Employee code '{$employeeCode}' not found.";
                continue;
            }

            Attendance::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $date],
                [
                    'time_in' => $timeIn ?: null,
                    'time_out' => $timeOut ?: null,
                    'status' => ($timeIn && $timeOut) ? 'present' : 'absent',
                ]
            );
            $imported++;
        }
        fclose($handle);

        $this->audit->actionLog('attendance', 'import', 'success', [
            'imported' => $imported,
            'errors' => count($errors),
        ]);

        $message = "Imported {$imported} record(s).";
        if ($errors) {
            $message .= ' ' . count($errors) . ' error(s) skipped.';
        }

        return redirect()->route('attendance.index')->with('success', $message);
    }
}
