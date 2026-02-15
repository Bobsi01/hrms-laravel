<?php

namespace App\Http\Controllers\Departments;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees', 'positions')
            ->orderBy('name')
            ->paginate(20);

        return view('departments.index', compact('departments'));
    }
}
