# DEVTA — Business Website

Modern black + neon-green business website inspired by [mydevta.com](https://mydevta.com), with a SaaS glassmorphism dashboard hero, PHP/MySQL contact backend, and secure admin panel.

## Tech Stack

| Layer    | Technology                          |
|----------|-------------------------------------|
| Frontend | HTML5, CSS3, Vanilla JavaScript     |
| Icons    | Lucide (CDN)                        |
| Backend  | Core PHP (PDO, sessions)            |
| Database | MySQL                               |

## Folder Structure

```
Mydevta/
├── index.php                 # Redirects to frontend
├── .htaccess                 # Security & caching
├── frontend/
│   ├── index.html            # Home + SaaS dashboard hero
│   ├── about.html
│   ├── services.html
│   ├── contact.html
│   └── logo.html             # Standalone logo showcase
├── backend/
│   ├── config.php            # DB + session helpers
│   ├── contact.php           # Contact form API
│   └── auth.php              # Login helpers
├── admin/
│   ├── index.php             # Admin login
│   ├── dashboard.php         # View / manage messages
│   ├── delete.php
│   └── logout.php
├── assets/
│   ├── css/                  # style, logo, dashboard-hero, admin
│   ├── js/main.js
│   └── images/logo.svg
└── database/schema.sql
```

## Local Setup (XAMPP / WAMP / Laragon)

1. Copy the `Mydevta` folder into your web root (`htdocs` / `www`).
2. Start Apache + MySQL.
3. Create a database named `mydevta` in phpMyAdmin.
4. Import `database/schema.sql`.
5. Edit `backend/config.php` if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'mydevta');
define('DB_USER', 'root');
define('DB_PASS', '');
```

6. Open `http://localhost/Mydevta/` (or your virtual host).
7. Admin: `http://localhost/Mydevta/admin/`  
   - **Username:** `admin`  
   - **Password:** `Admin@123`

## Hostinger Shared Hosting Setup

### 1. Upload files

1. Log in to **hPanel → File Manager**.
2. Open `public_html` (or your domain’s document root).
3. Upload the entire project contents so the structure looks like:

```
public_html/
  index.php
  frontend/
  backend/
  admin/
  assets/
  database/
  .htaccess
```

> Tip: Zip the project locally, upload the zip, then Extract in File Manager.

### 2. Create MySQL database

1. Go to **hPanel → Databases → MySQL Databases**.
2. Create a database (e.g. `u123456789_mydevta`).
3. Create a database user and password; assign the user **All Privileges** to that database.
4. Note the full DB name, username, password, and host (usually `localhost`).

### 3. Import schema

1. Open **phpMyAdmin** from hPanel.
2. Select your new database.
3. Click **Import** → choose `database/schema.sql` → Go.

### 4. Configure PHP

Edit `backend/config.php` with Hostinger credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456789_mydevta');  // your full DB name
define('DB_USER', 'u123456789_admin');    // your DB user
define('DB_PASS', 'your_strong_password');
```

### 5. Test

| URL | Purpose |
|-----|---------|
| `https://yourdomain.com/` | Homepage |
| `https://yourdomain.com/frontend/contact.html` | Contact form |
| `https://yourdomain.com/admin/` | Admin login |

### 6. Harden production

1. Change the default admin password (update the `users` table with a new `password_hash()` value).
2. Prefer HTTPS (Hostinger SSL is free via hPanel).
3. Keep `backend/config.php` credentials private; do not commit real passwords to public repos.
4. Optionally move `database/schema.sql` out of `public_html` after import.

## Features

- Sticky navbar, smooth scroll, loading animation  
- SaaS glassmorphism dashboard hero with floating widgets & analytics bars  
- Service cards with `scale(1.05)` + neon glow hover  
- **Get Quote modal** per service → `service_requests` table (name, email, phone, service)  
- Scroll reveal animations, button ripple + glow  
- Contact form → MySQL (PDO prepared statements)  
- Admin: Messages + Quotes dashboards with delete (CSRF)  
- Session-based admin login with bcrypt passwords  
- Responsive DEVTA logo with hover glow  

### Import quote table (existing DB)

If you already imported an older schema, also run:

`database/service_requests.sql`

## Default Admin

```
Username: admin
Password: Admin@123
```

Generate a new hash anytime:

```bash
php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
```

Then update the `users` table in phpMyAdmin.
