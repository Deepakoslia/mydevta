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
   - **Username:** `Devtaknowledge`
   - **Password:** `Nico@871`

## Hostinger Shared Hosting Setup

Full guide: **[HOSTINGER-SETUP.md](HOSTINGER-SETUP.md)**

Quick steps:
1. Upload full project to `public_html`
2. hPanel → MySQL Databases → create DB + user
3. Open `https://yourdomain.com/install.php` and paste DB details
4. Test `https://yourdomain.com/backend/ping.php`
5. Delete `install.php`
6. Admin: `/admin/` → `Devtaknowledge` / `Nico@871`

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
Username: Devtaknowledge
Password: Nico@871
```

Generate a new hash anytime:

```bash
php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
```

Then update the `users` table in phpMyAdmin.
