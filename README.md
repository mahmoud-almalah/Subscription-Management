# Subscription Management System

A multi-tenant SaaS backend for managing subscriptions, billing, and financial reporting — built with Laravel 12, PostgreSQL, and a proper double-entry accounting engine.

> Built as a technical assessment. Designed with ERP-grade financial principles in mind.

---

## Live Demo

**Base URL:** `https://your-deployment.railway.app/api`

> Seed data is available — use the credentials below to test immediately.

| Role  | Email                   | Password    |
|-------|-------------------------|-------------|
| Admin | acmecorp@gmail.com      | password123 |
| Admin | techstart@gmail.com     | password123 |
| Admin | saudi-digital@gmail.com | password123 |

These are two separate tenants — their data is fully isolated from each other.

---

## Tech Stack

| Layer        | Choice                          |
|--------------|---------------------------------|
| Framework    | Laravel 12 (PHP 8.3)            |
| Database     | PostgreSQL                      |
| Auth         | Laravel Sanctum (API tokens)    |
| ORM          | Eloquent with Global Scopes     |
| API Docs     | Scribe                          |
| Deployment   | Railway                         |

---

## Running Locally

**Requirements:** PHP 8.3+, Composer, PostgreSQL

```bash
git clone https://github.com/your-username/subscription-management.git
cd subscription-management

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your PostgreSQL credentials in .env
# DB_DATABASE=subscription_management
# DB_USERNAME=your_user
# DB_PASSWORD=your_password

# Run migrations and seed demo data
php artisan migrate
php artisan db:seed

# Start the server
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

---

## API Documentation

Full interactive docs are available at `/docs` (powered by Scribe).

### Authentication

```
POST /api/auth/register    Register a new tenant + admin user
POST /api/auth/login       Get a Sanctum API token
POST /api/auth/logout      Revoke the current token
```

All protected routes require:
```
Authorization: Bearer {token}
```

### Plans

```
GET    /api/plans
POST   /api/plans
GET    /api/plans/{id}
PUT    /api/plans/{id}
DELETE /api/plans/{id}
```

### Customers

```
GET    /api/customers
POST   /api/customers
GET    /api/customers/{id}
PUT    /api/customers/{id}
DELETE /api/customers/{id}
```

### Subscriptions

```
GET    /api/subscriptions
POST   /api/subscriptions
GET    /api/subscriptions/{id}
PUT    /api/subscriptions/{id}
DELETE /api/subscriptions/{id}
```

### Invoices

```
GET  /api/invoices
GET  /api/invoices/{id}
GET  /api/invoices/generate-monthly    Simulate a billing cron job
```

### Payments

```
GET  /api/payments
GET  /api/payments/{id}
POST /api/payments              Record a payment against an invoice
```

### Accounting

```
GET  /api/accounting/journal            View all journal entries
GET  /api/accounting/recognize-revenue  Simulate month-end revenue recognition
```

### Reports

```
GET /api/reports/income-statement?from=2025-01-01&to=2025-01-31
GET /api/reports/balance-sheet
```

---

## Architecture

The project is organized around business domains rather than technical layers. Each domain owns its models, services, actions, and enums.

```
app/Domain/
├── Tenant/         Tenant registration, users, customers
├── Subscription/   Plans and subscriptions
├── Billing/        Invoices and payments
├── Accounting/     Journal entries, accounts, revenue recognition
└── Shared/         Cross-cutting concerns (HasTenant trait)
```

This structure makes it easy to reason about each domain in isolation, and mirrors how ERP systems are typically designed internally.

---

## Design Decisions

### Multi-Tenancy via Global Scopes

Every model that belongs to a tenant uses a `HasTenant` trait. This trait does two things automatically:

1. Appends `WHERE tenant_id = ?` to every Eloquent query
2. Sets `tenant_id` on every new record at creation time

This means it is structurally impossible to forget tenant filtering — the isolation is enforced at the ORM level, not by convention. A second middleware layer (`EnsureTenantMiddleware`) validates that the authenticated user's tenant is active before any request is processed.

### Double-Entry Bookkeeping

Every financial event generates a balanced journal entry with two lines (debit and credit). The system validates that debits equal credits before persisting anything. If the check fails, the transaction is rolled back.

This gives us a complete, tamper-evident audit trail. Account balances are always derived from journal lines — never stored as a running total that could drift out of sync.

### Deferred Revenue (IFRS 15)

Revenue is not recognized when an invoice is issued or when payment is received. It is recognized when the service period ends. This follows the core principle of IFRS 15: revenue is earned by satisfying a performance obligation, not by collecting cash.

The three-step accounting flow:

```
1. Invoice issued
   DR  Accounts Receivable    100.00
   CR  Deferred Revenue        100.00

2. Payment received
   DR  Cash                   100.00
   CR  Accounts Receivable     100.00

3. Month-end — service delivered
   DR  Deferred Revenue       100.00
   CR  Subscription Revenue    100.00
```

After all three steps: Cash is up 100, Revenue is up 100, and both interim accounts are at zero. The books balance.

### Database Transactions on Every Financial Operation

Invoice generation, payment recording, and revenue recognition each wrap their database writes in a single transaction. If any step fails — including the accounting entry — nothing is committed. There are no half-created invoices with missing journal entries.

### ULIDs over Auto-Increment IDs

All primary keys are ULIDs. They are sortable by time, safe to expose in URLs, and avoid leaking record counts to clients.

---

## Database Schema

```
tenants
  └── users           (admin accounts)
  └── customers
      └── subscriptions
          └── invoices
              └── payments
  └── plans
  └── accounts        (chart of accounts — 4 system accounts per tenant)
  └── journal_entries
      └── journal_lines
```

The four system accounts created automatically for each tenant:

| Code | Name                  | Type      | Normal Balance |
|------|-----------------------|-----------|----------------|
| 1001 | Cash                  | Asset     | Debit          |
| 1002 | Accounts Receivable   | Asset     | Debit          |
| 2001 | Deferred Revenue      | Liability | Credit         |
| 4001 | Subscription Revenue  | Revenue   | Credit         |

---

## Trade-offs and Known Limitations

**Partial payments are not supported.** An invoice is either unpaid or fully paid. Supporting partial payments would require tracking remaining balances per invoice and splitting journal entries accordingly — a reasonable next step.

**Revenue recognition is all-or-nothing per invoice.** In reality, a subscription spanning multiple months might need pro-rated recognition. The current model recognizes the full invoice amount at period end.

**No role-based access control beyond admin/user.** The system distinguishes between admin and regular users but does not enforce per-resource permissions. A production system would need a proper RBAC layer.

**Cron simulation via HTTP endpoints.** `generate-monthly` and `recognize-revenue` are exposed as GET endpoints for demo purposes. In production these would be scheduled commands (`php artisan schedule:run`) and would not be publicly accessible.
