---
applyTo: '**'
---
# HRIS Laravel — Copilot Agent Guide

> App: **HRIS** · Laravel 12.x (PHP 8.2+) · PostgreSQL · Tailwind v4 · Heroku
> Incremental migration of legacy plain-PHP HRIS (`hrms-sysfunda/`). Both apps share the **same PostgreSQL database**.

---

## 1. Key Constraints

- **Shared DB** — Eloquent models map to existing PostgreSQL tables. Do NOT create migrations for pre-existing tables; only for new Laravel tables (cache, jobs, sessions).
- **PostgreSQL only** — Use `ilike` (not `LIKE`), `::text`, `COALESCE`, `jsonb`. Never concatenate user input into queries.
- **Timezone** — `Asia/Manila`. Display format: `M d, Y h:i A` (12-hour).
- **Config** — App settings in `config/hrms.php`, accessed via `config('hrms.*')`.
- **Hosting** — Heroku with `heroku-php-nginx`. Procfile at project root.

---

## 2. Stack

| Layer | Tech |
|---|---|
| Framework | Laravel 12.x, PHP 8.2+ |
| Database | PostgreSQL (shared with legacy), Eloquent ORM only |
| Frontend | Tailwind CSS v4 (`@tailwindcss/vite`), Blade, vanilla JS |
| Build | Vite + `laravel-vite-plugin` + `@tailwindcss/vite` |
| Font | Inter (Google Fonts) |
| Auth | Laravel `Auth` facade, custom `password_hash` column |
| Permissions | Position-based: `PermissionService` + `PermissionCatalog` |
| Auditing | `AuditService` singleton |
| Hosting | Heroku (`Procfile` → `heroku-php-nginx`) |

---

## 3. Tooling & Debugging

### PostgreSQL Extension
Use the VS Code PostgreSQL extension to query the database directly for schema verification and debugging. Connection: `cd7f19r8oktbkp.cluster-czrs8kj4isg7.us-east-1.rds.amazonaws.com`, database `ddh3o0bnf6d62e`, user `u7rl3utlpou68u`.

### Heroku CLI
Use `heroku logs --tail` to check production logs. The app name is configured in the Heroku remote. Common commands:
```bash
heroku logs --tail              # Stream live logs
heroku logs -n 200              # Last 200 lines
heroku config                   # View env vars
heroku run php artisan tinker   # Remote REPL
heroku run php artisan migrate  # Run migrations on Heroku
```

### Local Dev
```bash
composer dev                    # All services (serve + queue + pail + vite)
php artisan serve               # PHP server only
npm run dev                     # Vite only
php artisan test                # PHPUnit
php artisan route:list          # Route inspection
```

---

## 4. Eloquent Models

All models map to pre-existing tables. Rules:

```php
class Employee extends Model
{
    protected $table = 'employees';       // Always explicit
    protected $fillable = [...];          // Not $guarded
    protected function casts(): array     // Laravel 12 method-based casts (not $casts property)
    {
        return ['hire_date' => 'date', 'salary' => 'decimal:2'];
    }
}
```

- **Always set `$table`** explicitly.
- Tables without `created_at`/`updated_at` → set `public $timestamps = false;`.
- **`User` model**: `getAuthPassword()` returns `$this->password_hash`. `getRememberToken()`/`setRememberToken()` are no-ops (no column).
- **Relationships** — explicit return types (`BelongsTo`, `HasMany`). Always `with()` eager-load in controllers.

**Existing models (36+):** `User`, `Employee`, `Department`, `DepartmentSupervisor`, `Position`, `PositionAccessPermission`, `Branch`, `Attendance`, `LeaveRequest`, `LeaveRequestAction`, `LeaveRequestAttachment`, `LeaveFilingPolicy`, `OvertimeRequest`, `PayrollRun`, `PayrollBatch`, `Payslip`, `PayslipItem`, `PayrollComplaint`, `CutoffPeriod`, `CompensationTemplate`, `Memo`, `MemoAttachment`, `MemoRecipient`, `Document`, `DocumentAssignment`, `Notification`, `PerformanceReview`, `Recruitment`, `RecruitmentFile`, `RecruitmentTemplate`, `WorkScheduleTemplate`, `EmployeeWorkSchedule`, `AuditLog`, `SystemLog`, `UserRememberToken`

---

## 5. Permissions (Position-Based)

Permissions are assigned to **positions** (not roles); users inherit via `employees.position_id`.

**Domains:** `system`, `hr_core`, `payroll`, `leave`, `attendance`, `documents`, `performance`, `notifications`, `user_management`, `inventory`, `reports`
**Levels:** `none` → `read` → `write` → `manage` (hierarchical)
**Self-service:** Resources with `'self_service' => true` auto-grant `read` to all authenticated users.

| Component | Purpose |
|---|---|
| `PermissionService` | Runtime checks, caching, resolution |
| `PermissionCatalog` | Static domain/resource catalog |
| `CheckModuleAccess` middleware | Route-level gating: `module.access:domain,resource,level` |
| `SharePermissionsToView` middleware | Shares `$userPermissions`, `$currentUser`, `$isSystemAdmin` to all views |

```php
// Controller
$this->permissions->userHasAccess($userId, 'hr_core', 'employees', 'write');
$this->permissions->userCan('hr_core', 'employees', 'manage'); // checks auth()->id()

// Route
Route::get('/employees', ...)->middleware('module.access:hr_core,employees,read');

// Blade
@can_access('hr_core', 'employees', 'read') ... @endcan_access
@sysadmin ... @endsysadmin
```

**Superadmin** (config/hrms.php): unlimited `manage`, cannot be edited/deleted.
**System Admin** (`users.is_system_admin = true`): bypasses all checks.

---

## 6. Controller & Routing Patterns

```php
class EmployeeController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'branch'])->where('status', 'active');
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('first_name', 'ilike', "%{$escaped}%")
                  ->orWhere('last_name', 'ilike', "%{$escaped}%");
            });
        }
        return view('employees.index', ['employees' => $query->orderBy('last_name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $employee = Employee::create($request->validate([...]));
        $this->audit->actionLog('employees', 'create', 'success', ['employee_id' => $employee->id]);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }
}
```

### Rules
- **Constructor inject** `PermissionService` + `AuditService` (singletons).
- **Eager-load** relationships. **`ilike`** for search. **Paginate** with `->paginate(20)`.
- **Validate** with `$request->validate()` or Form Requests.
- **PRG** — always `redirect()->with('success'|'error')` after POST. Never render inline.
- **Named routes** everywhere: `route('name')`, never hardcode URLs.
- **Route groups**: `prefix('module')` + `name('module.')` + `middleware('module.access:...')`.
- **Self-service** routes (leave, my-payslips, my-attendance) use `auth` only, no `module.access`.
- **Login throttling**: `throttle:5,1`.

---

## 7. UI & Design System

### Layout
```
layouts/app.blade.php           → All authenticated pages (sidebar + topbar + main)
layouts/guest.blade.php         → Login / public
components/sidebar.blade.php    → Nav sidebar (gated by @can_access)
components/mobile-nav.blade.php → Mobile nav
components/modals/*.blade.php   → Authorization + confirm modals
```

### Page Template
```blade
@extends('layouts.app')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Page Title</h1>
        <p class="text-sm text-slate-500 mt-0.5">Description</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('module.create') }}" class="btn btn-primary">+ Add New</a>
    </div>
</div>
<div class="card">
    <div class="card-header flex items-center justify-between"><span>Section</span></div>
    <div class="card-body">
        <table class="table-basic">...</table>
    </div>
</div>
@endsection
```

### Blade Rules
- `$pageTitle` → set in controller or `@section('title', '...')`. Used in `<title>` + top bar.
- `@csrf` in every form. `@method('PUT'|'DELETE')` for non-POST.
- `@push('scripts')` / `@push('head')` for page-specific assets.
- `data-confirm="message"` on destructive buttons/forms.
- Flash messages auto-render from `session('success')` / `session('error')`.

### CSS Classes (`resources/css/app.css`)

| Element | Class |
|---|---|
| Cards | `.card`, `.card-header`, `.card-body` |
| Buttons | `.btn` + `.btn-primary` / `.btn-secondary` / `.btn-accent` / `.btn-warning` / `.btn-danger` / `.btn-outline` |
| Icon buttons | `.btn-icon` |
| Form inputs | `.input-text`, `.input-error`, `.field-error` |
| Required | `label.required` (red asterisk `::after`) |
| Tables | `.table-basic` |
| Action links | `.action-links` (pipe-separated) |
| Dropdowns | `.dropdown`, `.dropdown-menu`, `.dropdown-item` |
| Avatar | `.user-avatar` (indigo-purple gradient) |
| Loaders | `.loader-spinner`, `.spinner-mini` |

### Color Palette — USE CONSISTENTLY

| Purpose | Tailwind |
|---|---|
| Primary / Active | `indigo-600` |
| Success | `emerald-600` |
| Warning | `amber-600` |
| Error / Destructive | `red-600` |
| Info | `blue-600` |
| Body text | `slate-900` |
| Secondary text | `slate-500` |
| Muted | `slate-400` |
| Borders | `slate-200` |
| Hover bg | `slate-50` |
| Page bg | `bg-slate-50` (on body) |

### Badges
```blade
<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Active</span>
<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Pending</span>
<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Rejected</span>
```

### Stat Cards
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card card-body flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600">...</svg>
        </div>
        <div>
            <div class="text-2xl font-bold text-slate-900">{{ $count }}</div>
            <div class="text-xs text-slate-500">Label</div>
        </div>
    </div>
</div>
```

### Tailwind v4 Config
CSS-first via `resources/css/app.css` `@theme` block. **Do NOT create `tailwind.config.js`**.

### Responsive
All layouts must be responsive: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`. Mobile-first.

---

## 8. JavaScript (`resources/js/app.js`)

Global features: sidebar collapse (localStorage), user dropdown, notification bell (async fetch `/notifications/feed`), confirm modal (`data-confirm`), flash auto-close (`data-autoclose` + `data-timeout`), header clock (Asia/Manila).

Global utility: `window.escapeHtml(str)`.

Page-specific JS via `@push('scripts')`.

---

## 9. Services

Register as **singletons** in `AppServiceProvider`. Inject via constructor — never use `app()`.

```php
// AuditService — audit EVERY user action
$this->audit->actionLog('employees', 'create', 'success', ['employee_id' => $id]);
$this->audit->log('login', json_encode(['ip' => $request->ip()]), ['user_id' => $userId]);
```

New services: create in `app/Services/`, register in `AppServiceProvider::register()`, keep stateless.

---

## 10. Security

| Concern | Implementation |
|---|---|
| CSRF | `@csrf` in forms |
| SQL Injection | Eloquent/query builder only — never concatenate |
| XSS | `{{ }}` auto-escapes; `{!! !!}` only for trusted HTML |
| Mass Assignment | `$fillable` on all models |
| Auth | Custom `getAuthPassword()` for `password_hash` column |
| Authorization | `CheckModuleAccess` middleware + `PermissionService` |
| Rate Limiting | `throttle:5,1` on login |
| PRG | Always redirect after POST — never render inline |

---

## 11. Middleware

Registered in `bootstrap/app.php`:
- **`SharePermissionsToView`** — global web group. Shares `$userPermissions`, `$currentUser`, `$isSystemAdmin` to all views.
- **`CheckModuleAccess`** — alias `module.access`. Usage: `->middleware('module.access:domain,resource,level')`.

New middleware: create in `app/Http/Middleware/`, register in `bootstrap/app.php`.

---

## 12. Logging

| Method | Use For |
|---|---|
| `AuditService::actionLog()` | Every CRUD action (create, update, delete, approve, reject) |
| `AuditService::log()` | Auth events, permission changes, structured audit |
| `Log::error()` / `Log::info()` | Exceptions, technical debugging |

Audit → `audit_logs` table. System → `storage/logs/laravel.log`. Never suppress logging calls.

---

## 13. Legacy Reference

Reference `hrms-sysfunda/` for business logic when building features:

| Legacy | Laravel |
|---|---|
| `includes/config.php` | `config/hrms.php` + `.env` |
| `includes/auth.php` | `Auth` facade, `LoginController` |
| `includes/permissions.php` | `PermissionService` |
| `includes/permissions_catalog.php` | `PermissionCatalog` |
| `includes/payroll.php` | `PayrollService` (to be created) |
| `includes/header.php` + `footer.php` | `layouts/app.blade.php` |
| `modules/{area}/*.php` | `Controllers/{Area}/` + `views/{area}/` |

---

## 14. New Feature Checklist

- [ ] Controller in `app/Http/Controllers/{Module}/`
- [ ] Eloquent model(s) in `app/Models/` (explicit `$table`, `$fillable`, method casts)
- [ ] Permission resource in `PermissionCatalog` if needed
- [ ] Routes in `web.php` with `name()`, `prefix()`, `middleware('module.access:...')`
- [ ] Blade views: `@extends('layouts.app')`, `@section('content')`, `@csrf`
- [ ] PRG: `redirect()->with('success'|'error')` after POST
- [ ] Audit: `$this->audit->actionLog(...)` on every mutation
- [ ] `data-confirm` on destructive actions
- [ ] Sidebar entry in `components/sidebar.blade.php` gated by `@can_access`
- [ ] Use design system: `.card`, `.btn-*`, `.table-basic`, badges, stat cards, color palette
- [ ] Responsive: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`
- [ ] Eager-load relationships, `ilike` for search, `paginate(20)`
- [ ] Feature test in `tests/Feature/`

---

## 15. Gotchas

| Issue | Fix |
|---|---|
| `password_hash` vs `password` | `User::getAuthPassword()` returns `$this->password_hash` |
| No `remember_token` column | `getRememberToken()`/`setRememberToken()` are no-ops |
| Table has no timestamps | `public $timestamps = false;` |
| Case-sensitive search | Use `ilike`, not `LIKE` |
| Tailwind not working | Config must be in `@theme` block in `app.css`, not `tailwind.config.js` |
| Permissions missing in Blade | `SharePermissionsToView` shares `$userPermissions` globally |
| Flash not showing | Must `redirect()`, not return view after POST |

---

## 16. Code Style

- **PHP 8.2+**: constructor promotion, named args, match, enums, readonly, union types, null-safe `?->`.
- **Laravel idioms**: `collect()`, `Str::`, `Arr::`, `Carbon`, `tap()`, `value()`.
- **No raw SQL** — Eloquent/Query Builder only.
- **Type hints** on all parameters and return types. **Strict comparison** (`===`).
- **PSR-12** via Laravel Pint (`./vendor/bin/pint`).
