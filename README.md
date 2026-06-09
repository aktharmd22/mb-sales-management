# Malayznbeat — Sales Management System

A production-ready field-sales management app for **Malayznbeat** (Malaysia). Salespeople log client visits from their phone; managers see the whole team's pipeline, targets and reports live. Currency is **Malaysian Ringgit (RM)** throughout. Replaces a pile of monthly Excel sheets with one shared, living system.

## Stack

- Laravel 12 · PHP 8.2 · Livewire 3 + Alpine.js
- Tailwind CSS 3 (custom design tokens) · **DM Sans** everywhere · ApexCharts · SortableJS
- spatie/laravel-permission (roles) · maatwebsite/excel (import/export)
- MySQL (XAMPP) — database `mb_sales`

## Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run build          # or: npm run dev
php artisan serve
```

Make sure XAMPP MySQL is running and a `mb_sales` database exists:
`CREATE DATABASE mb_sales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`

## Demo logins (password: `password`)

| Role        | Email                      |
|-------------|----------------------------|
| Admin       | admin@malayznbeat.com      |
| Salesperson | daniel@malayznbeat.com     |
| Salesperson | nurul@malayznbeat.com      |
| Salesperson | arjun@malayznbeat.com      |
| Salesperson | siti@malayznbeat.com       |

The seeder creates 1 admin, 4 salespeople, 40 clients with several months of visits, follow-ups (overdue / due today / upcoming), deals (won/lost) and monthly targets.

## Features

- **Two roles** — Admin sees the whole team; salespeople see only their own data (enforced via policies + middleware).
- **Clients** are lasting records; every visit attaches to the same business with full history.
- **One-minute Log Visit** flow — quick-add a client, set the stage, tap Yes/No outcomes, enter RM potential, optional photo, schedule a follow-up.
- **Pipeline**: Cold → Warm → Qualified → Opportunity → Proposal → Won/Lost, with a funnel view and a drag-and-drop kanban board.
- **Dashboards** with a Daily / Weekly / Monthly / Yearly toggle driving every KPI and chart, target-progress rings, leaderboard and activity feed.
- **Reports** with per-salesperson breakdown and potential-vs-actual revenue.
- **Follow-ups** with overdue/due-today flags and a nav badge.
- **Excel** export (reports & clients) and import of existing spreadsheets (a template is provided in-app).
- **Smart touches**: duplicate-client detection, global search, filters/sorting, and an auto **dormant-lead flag** (no visit in 30 days) refreshed nightly by `php artisan clients:flag-dormant`.

## Tests

```bash
php artisan test
```

## KPI definitions (match the original Excel)

- Businesses visited = count of visits
- Decision makers met / Interested / Follow-ups done = count where the flag is Yes
- Proposals sent = visits at the "Proposal Stage"
- Revenue potential = sum of `revenue_potential`
