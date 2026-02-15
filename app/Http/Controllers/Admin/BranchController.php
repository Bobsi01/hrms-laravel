<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $branches = Branch::withCount(['employees', 'users'])->orderBy('name')->get();
        $editBranch = null;

        if ($request->has('edit')) {
            $editBranch = Branch::find($request->input('edit'));
        }

        $defaultBranchId = config('hrms.default_branch_id', 1);

        return view('admin.branches.index', compact('branches', 'editBranch', 'defaultBranchId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $validated['code'] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $validated['code']));

        // Check duplicate code
        $exists = Branch::whereRaw('LOWER(code) = ?', [strtolower($validated['code'])])->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'A branch with this code already exists.');
        }

        $branch = Branch::create($validated);

        $this->audit->actionLog('branches', 'create', 'success', [
            'branch_id' => $branch->id,
            'code' => $branch->code,
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $validated['code'] = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $validated['code']));

        // Check duplicate excluding self
        $exists = Branch::whereRaw('LOWER(code) = ?', [strtolower($validated['code'])])
            ->where('id', '!=', $branch->id)
            ->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'A branch with this code already exists.');
        }

        $branch->update($validated);

        $this->audit->actionLog('branches', 'update', 'success', [
            'branch_id' => $branch->id,
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $defaultBranchId = config('hrms.default_branch_id', 1);
        if ($branch->id === $defaultBranchId) {
            return back()->with('error', 'Cannot delete the default branch.');
        }

        $empCount = Employee::where('branch_id', $branch->id)->count();
        $userCount = User::where('branch_id', $branch->id)->count();

        if ($empCount > 0 || $userCount > 0) {
            return back()->with('error', 'Cannot delete branch with linked employees or users.');
        }

        $this->audit->actionLog('branches', 'delete', 'success', [
            'branch_id' => $branch->id,
            'code' => $branch->code,
        ]);

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.');
    }
}
