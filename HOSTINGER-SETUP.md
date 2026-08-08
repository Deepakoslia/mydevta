# Hostinger par DEVTA Database Setup

## 1) Files upload
File Manager / FTP se yeh structure `public_html` mein rakho:

```
public_html/
  install.php
  frontend/
  backend/
  admin/
  assets/
  database/
```

## 2) MySQL banayein (hPanel)
1. **hPanel → Databases → MySQL Databases**
2. Database create karo (example: `u123456789_mydevta`)
3. User create karo + strong password
4. User ko database par **All Privileges** do
5. Host usually: `localhost`

## 3) One-click setup
Browser mein kholo:

`https://yourdomain.com/install.php`

Form mein Hostinger DB details paste karo → **Connect & Create Tables**

Yeh automatically:
- MySQL connect karega
- `users`, `contacts`, `service_requests` tables banayega
- `backend/config.local.php` save karega
- Admin user banayega

## 4) Test
`https://yourdomain.com/backend/ping.php`

`"ok": true` aana chahiye.

## 5) Security
1. **`install.php` delete** kar do
2. Admin login: `https://yourdomain.com/admin/`
   - Username: `Devtaknowledge`
   - Password: `Nico@871`
3. Password jaldi change karo

## Data kahan dikhega?
| Form | Table | Admin page |
|------|--------|------------|
| Request Callback | `service_requests` | Admin → Quotes |
| Contact form | `contacts` | Admin → Messages |

phpMyAdmin se bhi yeh tables dekh sakte ho.
