Step 1 — MySQL Database Schema and Sample Data

Files created:
- `db/schema.sql` — full schema with tables, indexes, triggers
- `db/sample_data.sql` — sample routes, buses, drivers, schedules and users

How to import on XAMPP/WAMP (MySQL):
1. Copy the `db` folder into your project workspace.
2. From command line or phpMyAdmin, import `db/schema.sql` first, then `db/sample_data.sql`.

Create the default administrator credentials (recommended):
- Email: admin@example.com
- Password: Admin@123

To securely set the admin password (generate PHP password_hash):
Run this PHP one-liner in a terminal with PHP CLI installed:

```bash
php -r "echo password_hash('Admin@123', PASSWORD_DEFAULT) . PHP_EOL;"
```

This prints a bcrypt (or default) hash. Copy the output and then run in MySQL (replace HASH_HERE):

```sql
USE smart_bus;
UPDATE users SET password = 'HASH_HERE' WHERE email = 'admin@example.com';
```

Notes and next steps:
- The triggers reduce/increase `schedules.available_seats` when bookings are inserted/deleted. Application-level checks are still required to prevent double-booking and conflicting schedules.
- Step 2 will create the PHP database connection, configuration, and a setup script to finalize admin creation automatically.

If you want, I can now proceed to Step 2: create the PHP DB connection and setup script to auto-create the admin user with the hashed password.
