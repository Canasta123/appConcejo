# Concejo de Chía - API Documentation

## Database Setup

To set up the database for this project:

1. Log into your cPanel hosting account.
2. Open **phpMyAdmin**.
3. Create a new database named `concejo_chia` (or select your existing one if created via cPanel's MySQL Databases).
4. Go to the **Import** tab.
5. Upload the `api/database/schema.sql` file and click **Go**.
6. This will create all necessary tables and insert sample seed data for testing.

## Database Configuration

Update your database credentials to allow the API to connect:

1. Open `api/config/database.php` (create it if it doesn't exist).
2. Update the credentials with your specific cPanel database details:

```php
<?php
// Example configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_cpanel_db_user');
define('DB_PASS', 'your_cpanel_db_password');
define('DB_NAME', 'concejo_chia');
```

## API Endpoint Reference

| Endpoint | Method | Description |
| -------- | ------ | ----------- |
| `/api/noticias` | GET | Returns a list of active news articles |
| `/api/documentos` | GET | Returns documents, filterable by category |
| `/api/agenda` | GET | Returns upcoming events and sessions |
| `/api/auth/login` | POST | Authenticates an admin user and returns a token |
| `/api/admin/noticias` | POST/PUT/DELETE | Manage news articles (Requires Auth) |

> Note: More endpoints will be available in Phase 2.

## ⚠️ Important Production Note

The database seed data comes with default administrator accounts. 
**You MUST change these passwords in production:**

- Default Admin User: `admin`
- Default Password: `admin123`

When deploying to production, log in using these credentials and change the password immediately through the admin panel, or generate a new `bcrypt` hash and update it manually in the database.
