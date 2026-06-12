# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 11 **modular monolithic** backend for a dormitory/boarding house (wisma) management system, using `nwidart/laravel-modules`. Active modules: Room, Schedule, Finance, Maintenance, Inventory, Guest, Notification, Auth, Setting.

> Refactor selesai (Fase 0–11). Arsitektur sudah event-driven modular. Modul Rental dan Resident telah **dihapus** — semua logika sewa kamar sekarang ada di modul **Schedule**. Lihat `CATATAN_ARSITEKTUR.md` untuk detail arsitektur.

## Key Documents

| File | Isi |
|---|---|
| [`CATATAN_ARSITEKTUR.md`](CATATAN_ARSITEKTUR.md) | Arsitektur final, daftar event, dan aturan modul |
| [`ROADMAP_REFACTOR.md`](ROADMAP_REFACTOR.md) | Riwayat refactor fase 0–11 (semua selesai) |

## Common Commands

```bash
# Development
composer dev                        # Start server, queue, pail logs, and Vite concurrently
php artisan serve                   # Web server only (port 8000)

# Database
php artisan migrate                 # Run pending migrations
php artisan migrate:fresh --seed    # Drop all tables, re-run, and seed

# New module
php artisan module:make FeatureName # Scaffold a new module under Modules/

# Testing (uses actual SQLite DB, not in-memory — see phpunit.xml)
php artisan test                    # All tests (Unit + Feature + Modules)
php artisan test --filter=TestName  # Single test by name
php artisan test --testsuite=Modules # Module tests only

# Code style
./vendor/bin/pint                   # Format PHP (PSR-12, auto-fix)
./vendor/bin/pint --test            # Check without fixing

# Architecture validation
./vendor/bin/deptrac analyse        # Enforce layer dependency rules (deptrac.yaml)

# Production / Docker
docker compose up -d
docker compose exec app php artisan migrate
```

## Architecture

### Module Structure

Every feature lives in `Modules/<Name>/` with this layered structure:

```
Http/Controllers/   ← request/response only, calls Service
Http/Requests/      ← FormRequest validation
Services/           ← all business logic, calls Repository
Repositories/
  Contracts/        ← interfaces (Dependency Inversion)
  Eloquent/         ← concrete Eloquent implementations
Models/             ← Eloquent models and relations
Transformers/       ← API Resources (JSON presentation layer)
Enums/              ← typed status constants
Listeners/          ← handlers for events fired by other modules
Providers/
  EventServiceProvider.php       ← registers this module's listeners
  <Name>ServiceProvider.php      ← binds interfaces to implementations
database/           ← module-specific migrations and seeders
routes/api.php      ← module API routes
```

Repository interfaces are bound to their Eloquent implementations inside each module's `<Name>ServiceProvider`.

### Request Flow

```
HTTP → Nginx → routes/api.php → Controller (validate via FormRequest)
    → Service (business logic, DB::transaction for multi-step ops)
    → Repository (Eloquent queries)
    → Transformer (Resource)
    → ApiResponse trait → JSON
```

### Three-Tier Module Hierarchy

Enforced by `deptrac.yaml` — dependency direction is top-down only:

```
INFRASTRUCTURE (always on)   Auth, Setting
CORE (always on)             Room, Schedule
BUSINESS MODULES (optional)  Finance, Maintenance, Guest, Inventory, Notification
```

Business modules may not import from each other directly. All cross-module communication goes through Laravel Events.

### Event-Driven Communication

Events live in `app/Events/` (global, owned by no single module):

| Namespace | Events |
|---|---|
| `app/Events/Jadwal/` | `JadwalDibuat`, `JadwalSewaAktif`, `JadwalSewaSelesai`, `JadwalBatal`, `StatusKamarBerubah` |
| `app/Events/Finance/` | `PembayaranDiterima`, `PembayaranDiverifikasi`, `PembayaranDibatalkan` |
| `app/Events/Inventory/` | `InventariBaru`, `InventarisDiperbarui`, `InventarisDihapus` |
| `app/Events/Maintenance/` | `LaporanKerusakanMasuk` |

The global `app/Providers/EventServiceProvider.php` declares the event catalog (no listeners). Each module registers its own listeners in its own `Providers/EventServiceProvider.php`.

### API Response Convention

Always use the `App\Traits\ApiResponse` trait in controllers:

```php
$this->apiSuccess($data, $message, $statusCode);
$this->apiError($message, $statusCode, $errors);
```

### Auth & Permissions

- **Laravel Sanctum** — token-based API auth
- **Spatie Permission** — RBAC stored in global DB tables
- Middleware: `->middleware('permission:create-room')`
- Super-admin bypasses all permission checks via Gate
- Always use `Modules\Auth\Models\User` (not `App\Models\User`) inside modules

### Module Activation

`modules_statuses.json` controls which modules load. Set a module to `false` to disable its routes and providers without deleting code. `Rental` and `Resident` are already set to `false` (replaced by `Schedule`).

## Key Integrations

- **Midtrans** — payment gateway in `Modules/Finance`. Env: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`.
- **Fonnte** — SMS/WhatsApp notifications via `FONNTE_TOKEN`.
- **Scramble** — auto-generates OpenAPI docs from type-hints/doc-blocks at `/docs/api`.
- **Intervention Image** — centralized in `App\Services\ImageService`.

## Environment

Copy `.env.example` to `.env` and set:
- Local dev uses **SQLite** by default (`database/database.sqlite`).
- Production uses **MariaDB** (`DB_CONNECTION=mysql`, `DB_HOST=db`).
- Force HTTPS is enabled automatically when `APP_ENV=production` or `staging`.

## Database Migrations

- Global: `database/migrations/` — users, permissions, tokens, jobs
- Per-module: `Modules/<Name>/database/migrations/`

`php artisan migrate` discovers module migrations automatically when the module is active.

## Architectural Rules

1. **No direct cross-module calls** — business modules must communicate only via Laravel Events, never by injecting another module's Service or Repository.
2. **Additive DB changes** — add columns/tables first, migrate data, then drop old ones. Never destructive-first.
3. **API shapes are stable** — response structure consumed by Flutter clients must not change without coordinating a frontend update.
4. **Run tests after every change** — `php artisan test`.
5. **Run deptrac after structural changes** — `./vendor/bin/deptrac analyse` to verify no layer violations were introduced.
6. **One branch per feature/phase** — never mix unrelated changes in the same branch.
