
# File: /wemart/readme.md
```markdown
# Wemart E-Commerce Website

## Overview
This is a PHP-based e-commerce website inspired by Walmart, featuring a user-facing storefront and an admin panel. It supports product browsing, filtering, shopping cart, user registration/login, PDF invoice generation, and admin CRUD operations for products and users.

## Requirements
- PHP 7.4+
- MySQL (via phpMyAdmin)
- Composer (for DOMPDF)
- OpenWeatherMap API key (for weather-based suggestions)

## Setup Instructions
1. **Database Setup**:
   - Create a MySQL database named `wemart`.
   - Import the SQL below to create tables and sample data.
2. **File Setup**:
   - Clone this project to your web server (e.g., `/var/www/html/wemart`).
   - Create the directory `assets/images/products/` and ensure it is writable.
3. **Dependencies**:
   - Run `composer require dompdf/dompdf` in the project root to install DOMPDF.
4. **Configuration**:
   - Update `includes/config.php` with your database credentials and OpenWeatherMap API key.
5. **Run**:
   - Access the site at `http://localhost/wemart/`.
   - Admin login: Create a user with `role = 'admin'` via the database.


   curl -X POST http://localhost/wemart/api/check_email.php -H "Content-Type: application/json" -d '{"email":"test@example.com"}'
