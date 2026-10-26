# SupportDesk — Multi-Tenant Helpdesk SaaS

A runnable Laravel API + React/TypeScript frontend starter focused on tenant isolation and core ticket workflows. This is a development foundation, not a claim of production certification. Email delivery, invitations, advanced SLA scheduling, and full analytics require further configuration/implementation.

## Requirements
- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8+ (or SQLite for quick local tests)
- Laravel-compatible PHP extensions

## 1. Install backend dependencies
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```
Create a MySQL database named `supportdesk`, then set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `backend/.env`.

For SQLite instead, create `database/database.sqlite`, then set `DB_CONNECTION=sqlite` and remove/comment the MySQL DB settings.

```bash
php artisan migrate --seed
php artisan serve
```
Backend API runs at `http://127.0.0.1:8000`.

## 2. Install frontend dependencies
In another terminal:
```bash
cd frontend
npm install
npm run dev
```
Frontend runs at the Vite URL shown in the terminal (usually `http://localhost:5173`).

## Demo account
Seeder creates:
- Email: `admin@acme.test`
- Password: `password`
- Company: Acme Support

Change these credentials immediately outside local development.

## Useful commands
```bash
# Backend automated tests
cd backend
php artisan test

# Check routes
php artisan route:list --path=api

# Frontend production build
cd ../frontend
npm run build
```

## API overview
- `POST /api/v1/register` — create company + initial company admin
- `POST /api/v1/login` — obtain Sanctum token
- `POST /api/v1/logout`
- `GET /api/v1/me`
- `GET /api/v1/dashboard`
- `GET|POST /api/v1/tickets`
- `GET|PATCH /api/v1/tickets/{ticket}`
- `POST /api/v1/tickets/{ticket}/replies`

Pass `Authorization: Bearer <token>` to protected API endpoints. Ticket queries are scoped to the authenticated user's company; customers can only see their own tickets. Internal notes are excluded from customer responses.

## Important notes
- Laravel Sanctum personal access tokens are used for API auth.
- Registration can create only a company and a `company_admin`; platform super-admin must be provisioned by trusted server-side administration.
- Tenant-owned ticket operations resolve company context from the authenticated user, never from client-supplied `company_id`.
- This project is an implementation starter. Before production, configure HTTPS, backups, mail, queue workers, logging/monitoring, password reset delivery, invitation lifecycle, attachment storage/scanning, SLA scheduler, and a broader security review.
