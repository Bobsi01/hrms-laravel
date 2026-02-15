<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CutoffPeriod;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CutoffPeriodController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $periods = CutoffPeriod::orderByDesc('start_date')->paginate(20);

        $stats = [
            'total' => CutoffPeriod::count(),
            'active' => CutoffPeriod::where('status', 'active')->count(),
        ];

        // Get next cutoff date
        $nextCutoff = CutoffPeriod::where('status', 'active')
            ->where('cutoff_date', '>=', now()->toDateString())
            ->orderBy('cutoff_date')
            ->value('cutoff_date');

        return view('admin.cutoff-periods.index', compact('periods', 'stats', 'nextCutoff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'cutoff_date' => 'required|date',
            'pay_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = 'active';
        $validated['is_locked'] = false;
        $validated['created_by'] = auth()->id();

        $period = CutoffPeriod::create($validated);

        $this->audit->actionLog('cutoff_periods', 'create', 'success', [
            'period_id' => $period->id,
        ]);

        return redirect()->route('admin.cutoff-periods.index')
            ->with('success', 'Cutoff period created.');
    }

    public function populate(Request $request)
    {
        $validated = $request->validate([
            'start_year' => 'required|integer|min:2020|max:2040',
            'start_month' => 'required|integer|min:1|max:12',
            'months_count' => 'required|integer|min:1|max:24',
        ]);

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            $start = Carbon::create($validated['start_year'], $validated['start_month'], 1);

            for ($i = 0; $i < $validated['months_count']; $i++) {
                $month = $start->copy()->addMonths($i);

                // Cutoff 1: 6th to 20th, pay 30th
                $name1 = $month->format('F Y') . ' - Cutoff 1';
                if (!CutoffPeriod::where('period_name', $name1)->exists()) {
                    CutoffPeriod::create([
                        'period_name' => $name1,
                        'start_date' => $month->copy()->day(6)->toDateString(),
                        'end_date' => $month->copy()->day(20)->toDateString(),
                        'cutoff_date' => $month->copy()->day(20)->toDateString(),
                        'pay_date' => $month->copy()->endOfMonth()->toDateString(),
                        'status' => 'active',
                        'is_locked' => false,
                        'created_by' => auth()->id(),
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }

                // Cutoff 2: 21st to 5th next month, pay 15th next month
                $nextMonth = $month->copy()->addMonth();
                $name2 = $month->format('F Y') . ' - Cutoff 2';
                if (!CutoffPeriod::where('period_name', $name2)->exists()) {
                    CutoffPeriod::create([
                        'period_name' => $name2,
                        'start_date' => $month->copy()->day(21)->toDateString(),
                        'end_date' => $nextMonth->copy()->day(5)->toDateString(),
                        'cutoff_date' => $nextMonth->copy()->day(5)->toDateString(),
                        'pay_date' => $nextMonth->copy()->day(15)->toDateString(),
                        'status' => 'active',
                        'is_locked' => false,
                        'created_by' => auth()->id(),
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            $this->audit->actionLog('cutoff_periods', 'populate', 'success', [
                'created' => $created,
                'skipped' => $skipped,
            ]);

            return redirect()->route('admin.cutoff-periods.index')
                ->with('success', "Auto-populated {$created} cutoff periods. {$skipped} skipped (duplicates).");

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Cutoff period populate failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to populate cutoff periods.');
        }
    }

    public function close(CutoffPeriod $cutoffPeriod)
    {
        $cutoffPeriod->update(['status' => 'closed', 'is_locked' => true]);

        $this->audit->actionLog('cutoff_periods', 'close', 'success', [
            'period_id' => $cutoffPeriod->id,
        ]);

        return back()->with('success', 'Cutoff period closed.');
    }

    public function toggleLock(CutoffPeriod $cutoffPeriod)
    {
        $cutoffPeriod->update(['is_locked' => !$cutoffPeriod->is_locked]);

        $state = $cutoffPeriod->is_locked ? 'locked' : 'unlocked';
        $this->audit->actionLog('cutoff_periods', 'toggle_lock', 'success', [
            'period_id' => $cutoffPeriod->id,
            'state' => $state,
        ]);

        return back()->with('success', "Period {$state}.");
    }

    public function cancel(CutoffPeriod $cutoffPeriod)
    {
        $cutoffPeriod->update(['status' => 'cancelled']);

        $this->audit->actionLog('cutoff_periods', 'cancel', 'success', [
            'period_id' => $cutoffPeriod->id,
        ]);

        return back()->with('success', 'Period cancelled.');
    }

    public function destroy(CutoffPeriod $cutoffPeriod)
    {
        // Check if any payroll runs reference this
        $payrollCount = DB::table('payroll')->where('cutoff_period_id', $cutoffPeriod->id)->count();
        if ($payrollCount > 0) {
            return back()->with('error', 'Cannot delete period with associated payroll runs.');
        }

        $this->audit->actionLog('cutoff_periods', 'delete', 'success', [
            'period_id' => $cutoffPeriod->id,
        ]);

        $cutoffPeriod->delete();

        return redirect()->route('admin.cutoff-periods.index')->with('success', 'Period deleted.');
    }
}
