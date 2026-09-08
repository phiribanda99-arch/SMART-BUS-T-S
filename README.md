# Smart Bus Transport System

A PHP and MySQL bus booking and transport management application.

## Project layout

```text
.
├── assets/                 Shared CSS
├── config/                 Application and database configuration
├── database/               Compatible schema and seed data
├── includes/               Database, authentication, and shared helpers
├── setup/                  CLI setup scripts
├── modules/admin/          Admin management modules
├── dashboard.php           Admin dashboard
├── index.php               Login and passenger registration
├── passenger-dashboard.php Passenger booking dashboard
└── logout.php              Session logout
```

## Setup

1. Create a MySQL database named `smartbus` and import `database/smart_bus.sql` into it.
2. Set the database values in the environment before running the app, or update `config/config.php` for local development.

Example environment variables:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=smartbus
export DB_USER=root
export DB_PASS=""
export DB_CHARSET=utf8mb4
export APP_BASE_URL=/SMART-BUS-T-S
```

3. From the project directory, create or reset an administrator:

```text
php setup/create_admin.php --email=admin@example.com --password=Admin@123
```

4. Serve the project through Apache/XAMPP or PHP's development server:

```text
php -S localhost:8000
```

5. Open `http://localhost:8000/` and sign in.

After signing in as an administrator or operator, open **Management Modules** from the dashboard. It contains user status, bus fleet, routes, schedules, bookings/tickets, and payment tracking views.

The SQL file contains the schema and compatible sample records. Import it only once into a new database. Change the example administrator password before using the application in a real environment.
