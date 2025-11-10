# Fuji Cafe Digital Menu

A modern, multilingual digital menu system for restaurants with admin panel and customer review functionality.

## Overview
Fuji Cafe is a PHP-based digital menu application that allows restaurants to:
- Display menu items with categories, pricing, and images
- Support bilingual content (English and Amharic)
- Collect customer reviews and ratings
- Manage menu items, categories, and settings via admin panel

## Project Structure
```
public_html/fujicafe/
├── admin/               # Admin panel
│   ├── index.php       # Dashboard
│   ├── login.php       # Admin authentication
│   ├── categories.php  # Category management
│   ├── items.php       # Menu item management
│   ├── reviews_mgmt.php # Review management
│   └── settings.php    # Restaurant settings
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── uploads/       # Uploaded images
├── includes/
│   ├── boot.php       # Bootstrap and configuration
│   ├── db.php         # Database connection
│   └── helpers.php    # Helper functions
├── index.php          # Public menu page
├── reviews.php        # Review API endpoint
└── schema_postgres.sql # Database schema
```

## Database Schema
PostgreSQL database with the following tables:
- `menu_categories` - Menu categories with multilingual support
- `menu_items` - Menu items with pricing, descriptions, and images
- `menu_item_reviews` - Customer reviews and ratings
- `restaurant_settings` - Restaurant name, subtitle, and logo

## Features
- **Public Menu**: Beautiful, responsive menu with category filtering and search
- **Multilingual**: Support for English and Amharic languages
- **Customer Reviews**: Customers can rate and review menu items
- **Admin Panel**: Manage categories, items, reviews, and restaurant settings
- **Image Upload**: Upload and manage menu item images and restaurant logo
- **Responsive Design**: Works on desktop, tablet, and mobile devices

## Admin Access
Default credentials (should be changed via environment variables):
- Username: `admin`
- Password: `admin123`

Set custom credentials using environment variables:
- `ADMIN_USERNAME` - Custom admin username
- `ADMIN_PASSWORD_HASH` - bcrypt hash of admin password

## Recent Changes (November 10, 2025)
1. **Database Setup**: Created PostgreSQL database with complete schema
   - Added all required tables: menu_categories, menu_items, menu_item_reviews, restaurant_settings
   - Populated with sample data
   
2. **Fixed CSRF Functions**: Added missing `csrf_field()` and `csrf_validate()` functions to helpers.php
   
3. **Updated Branding**: Changed "Powered by" footer
   - Updated company name to "Neo Digital Solution"
   - Changed link to https://neodigitalsolutions.com
   - Updated logo alt text

4. **Asset Setup**: Added Neo Digital Solution logo to uploads directory

## Environment
- PHP 8.2.23
- PostgreSQL database (Neon-backed)
- Running on port 5000

## URLs
- Public Menu: `/fujicafe/`
- Admin Panel: `/fujicafe/admin/`
- Admin Login: `/fujicafe/admin/login.php`

## Notes
- The application uses environment-based authentication (no database table for admin users)
- Database connection details are stored in environment variables
- File uploads are stored in `assets/uploads/` directory
- The application supports both light and dark themes
