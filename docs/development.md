# Development setup

## Prerequisites

- PHP 8.2+ with PDO PostgreSQL (`pdo_pgsql`) and cURL enabled
- Composer 2
- Node.js 20.19+ and npm
- PostgreSQL server

## Standalone PHP and PostgreSQL on Windows

1. Install a standalone x64 PHP ZIP build from [PHP for Windows](https://windows.php.net/download/). PHP 8.3 NTS x64 is suitable for Laravel's CLI development server. Extract it to a separate folder such as `C:\tools\php` and add that folder to your Windows user `PATH`.
2. Copy `php.ini-development` to `php.ini`. In `php.ini`, set `extension_dir="ext"` and enable these extensions by removing their leading semicolons: `curl`, `mbstring`, `openssl`, `pdo_pgsql`, `fileinfo`, and `zip`. Open a new terminal and confirm `where.exe php` points to `C:\tools\php\php.exe` and `php -m` lists `pdo_pgsql`.
3. Install Composer using its Windows installer and point it at the standalone `C:\tools\php\php.exe` when prompted. Confirm `composer --version` works.
4. Install PostgreSQL and pgAdmin using the [official PostgreSQL Windows download page](https://www.postgresql.org/download/windows/). During setup, keep port `5432` and set a password for the `postgres` administrator.
5. In pgAdmin, connect to the local server and create a database named `buildlab` (or use `CREATE DATABASE buildlab;` in the Query Tool).
6. Set `DB_PASSWORD` in `backend/.env` to the password chosen during PostgreSQL setup. Keep `.env` private and out of Git.

The current `php` on this machine resolves to XAMPP's PHP 8.2.12. Use the new terminal/PATH selection above so BuildLab uses the standalone PHP installation instead.

## Backend

```powershell
cd backend
Copy-Item .env.example .env
php artisan key:generate
# Configure DB_* in backend/.env for your local PostgreSQL server
php artisan db:show --database=pgsql
php artisan migrate
composer run dev
```

## Frontend

```powershell
cd frontend
npm install
npm run dev
```

The frontend will call the versioned API through a development proxy once the API routes are added. Do not commit `.env` or enter secrets in Vite environment variables.

