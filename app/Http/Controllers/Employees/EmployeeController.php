<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'branch'])
            ->where('status', 'active');

        // Search
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('first_name', 'ilike', "%{$escaped}%")
                  ->orWhere('last_name', 'ilike', "%{$escaped}%")
                  ->orWhere('employee_code', 'ilike', "%{$escaped}%")
                  ->orWhere('email', 'ilike', "%{$escaped}%");
            });
        }

        // Filter by department
        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        $employees = $query->orderBy('last_name')->paginate(20);

        return view('employees.index', compact('employees'));
    }
}
