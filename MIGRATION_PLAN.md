# HRIS Laravel Migration Plan

> Created: 2026-02-15 | Status: In Progress

## Migration Strategy: **Incremental Module-by-Module**

We migrate the plain PHP HRIS to Laravel 11.x within `laravel-version/` in the same repo. The existing app continues running untouched while we build the Laravel version alongside it.

### Key Decisions:
- **Laravel 11.x** (latest LTS-track)
- **Same PostgreSQL database** — no schema migration; use Eloquent models mapped to existing tables
- **Tailwind CSS v4** (via Vite) — preserve the same design tokens
- **Inertia.js + Vue 3** (or Blade) — TBD per phase; Phase 1 uses Blade for parity
- **Breeze** for auth scaffolding, customized to match existing auth flow
- **Spatie Laravel-Permission** adapted to use existing position-based permission tables

---

## Phase 1: Foundation (Current Sprint)
> Goal: Bootable Laravel app with auth, permissions, and base layout

| # | Task | Maps To |
|---|---|---|
| 1.1 | `composer create-project laravel/laravel` in `laravel-version/` | — |
| 1.2 | Configure `.env` for existing PostgreSQL DB | `includes/db.php` |
| 1.3 | Create Eloquent models for all core tables (no migrations — existing DB) | `database/schema_postgre.sql` |
| 1.4 | Auth system: login, logout, remember-me, session config | `includes/auth.php`, `includes/session.php` |
| 1.5 | Position-based permission middleware & service | `includes/permissions.php`, `includes/permissions_catalog.php` |
| 1.6 | Blade layout: sidebar, top bar, footer (port design tokens) | `includes/header.php`, `includes/footer.php`, `assets/css/app.css` |
| 1.7 | Dashboard page (first authenticated page) | `index.php` |
| 1.8 | Flash messages, CSRF (built-in), audit service | `includes/utils.php`, `includes/auth.php` |

## Phase 2: HR Core Modules
> Goal: Employee, Department, Position, Branch CRUD

| # | Task | Maps To |
|---|---|---|
| 2.1 | Employee CRUD (list, create, edit, view, PDF, CSV) | `modules/employees/` |
| 2.2 | Department CRUD + supervisors | `modules/departments/` |
| 2.3 | Position CRUD + permission assignment | `modules/positions/` |
| 2.4 | Branch management | `modules/admin/branches/` |
| 2.5 | User account management | `modules/account/` |

## Phase 3: Leave, Attendance, Overtime
| # | Task | Maps To |
|---|---|---|
| 3.1 | Leave filing, approval, balances | `modules/leave/` |
| 3.2 | Attendance tracking, DTR import | `modules/attendance/` |
| 3.3 | Overtime requests & approval | `modules/overtime/` |
| 3.4 | Work schedules | `includes/work_schedules.php`, `modules/admin/work-schedules/` |

## Phase 4: Payroll
| # | Task | Maps To |
|---|---|---|
| 4.1 | Payroll runs, batches, payslips | `modules/payroll/`, `includes/payroll.php` |
| 4.2 | Cutoff periods, compensation templates | `modules/admin/cutoff-periods/`, `modules/admin/compensation/` |
| 4.3 | Statutory contributions (SSS, PhilHealth, Pag-IBIG) | DB tables |
| 4.4 | DTR upload & integration | `modules/payroll/dtr_upload.php` |

## Phase 5: Supporting Modules
| # | Task | Maps To |
|---|---|---|
| 5.1 | Memos & announcements | `modules/memos/` |
| 5.2 | Documents management | `modules/documents/` |
| 5.3 | Notifications | `modules/notifications/` |
| 5.4 | Performance reviews | `modules/performance/` |
| 5.5 | Recruitment pipeline | `modules/recruitment/` |

## Phase 6: Inventory & POS
| # | Task | Maps To |
|---|---|---|
| 6.1 | Inventory items, categories, locations | `modules/inventory/` |
| 6.2 | POS interface | `modules/inventory/pos.php` |
| 6.3 | Purchase orders, suppliers | `modules/inventory/purchase_orders.php` |
| 6.4 | Inventory reports | `modules/inventory/reports.php` |

## Phase 7: Admin & System
| # | Task | Maps To |
|---|---|---|
| 7.1 | Audit trail viewer | `modules/audit/` |
| 7.2 | System logs | `modules/admin/system_log.php` |
| 7.3 | Access control (devices, IP rules) | `modules/admin/access-control/` |
| 7.4 | Backup & restore | `modules/admin/backup.php` |
| 7.5 | Approval workflows config | `modules/admin/approval-workflow/` |
| 7.6 | PDF template config | `modules/admin/pdf/` |

## Phase 8: Polish & Cutover
| # | Task |
|---|---|
| 8.1 | API endpoints (JSON) for SPA-style features |
| 8.2 | Full test suite (Feature + Unit) |
| 8.3 | Performance optimization, caching |
| 8.4 | Production deployment config |
| 8.5 | Data validation & cutover checklist |

---

## Architecture Mapping

### PHP Include → Laravel Equivalent
| Old File | Laravel Component |
|---|---|
| `includes/config.php` | `config/app.php`, `config/hris.php`, `.env` |
| `includes/db.php` | `config/database.php` (Eloquent) |
| `includes/auth.php` | `App\Services\AuthService`, Guards, Middleware |
| `includes/session.php` | `config/session.php` |
| `includes/permissions.php` | `App\Services\PermissionService`, Middleware, Policies |
| `includes/permissions_catalog.php` | `App\Services\PermissionCatalog` |
| `includes/utils.php` | `App\Helpers`, `App\Services\AuditService`, built-in CSRF |
| `includes/payroll.php` | `App\Services\PayrollService` |
| `includes/header.php` | `resources/views/layouts/app.blade.php` |
| `includes/footer.php` | Blade layout closing |
| `includes/pdf.php` | `App\Services\PdfService` (using FPDF or DomPDF) |
| `includes/work_schedules.php` | `App\Services\WorkScheduleService` |

### Module → Laravel MVC
Each `modules/X/` maps to:
- `App\Http\Controllers\X\*Controller`
- `App\Models\*` (shared)
- `resources/views/X/*.blade.php`
- `routes/web.php` route groups
