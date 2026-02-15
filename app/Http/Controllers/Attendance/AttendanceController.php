<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Employee's own attendance.
     */
    public function my(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('attendance.my', ['attendances' => collect()]);
        }

        $attendances = Attendance::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->paginate(20);

        return view('attendance.my', compact('attendances'));
    }

    /**
     * Admin attendance list.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('employee')
            ->orderByDesc('date');

        if ($date = $request->input('date')) {
            $query->where('date', $date);
        }

        $attendances = $query->paginate(50);

        return view('attendance.index', compact('attendances'));
    }
}
