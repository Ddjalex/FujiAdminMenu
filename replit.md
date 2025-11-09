# Fuji Cafe - Digital Menu System

## Overview
Modern PHP-based digital menu application for Fuji Cafe with dark-themed UI and complete admin panel for managing categories and menu items.

## Project Structure
```
public_html/fujicafe/
├── index.php                 # Public-facing menu page
├── includes/
│   ├── db.php               # Database connection (DO NOT MODIFY)
│   ├── boot.php             # Bootstrap & session management (DO NOT MODIFY)
│   └── helpers.php          # Helper functions
├── admin/
│   ├── index.php            # Admin dashboard
│   ├── categories.php       # Category CRUD
│   └── items.php            # Menu items CRUD
├── assets/
│   ├── css/
│   │   ├── menu.css        # Public menu styles
│   │   └── app.css         # Admin panel styles
│   ├── js/
│   │   └── app.js          # Frontend JavaScript
│   └── uploads/            # Uploaded images
└── schema.sql              # Database schema

## Database Schema
- **menu_categories**: id, name, position, created_at
- **menu_items**: id, category_id, name, price, description, image_url, is_active, position, created_at, updated_at

## Setup Instructions

### 1. Database Setup
Set environment variables for database connection:
- DB_HOST (default: localhost)
- DB_NAME (default: fujicafe)
- DB_USER (default: root)
- DB_PASS (default: empty)

Run the schema:
```bash
mysql -u root -p fujicafe < public_html/fujicafe/schema.sql
```

### 2. Access Points
- **Public Menu**: http://localhost:5000/fujicafe/
- **Admin Panel**: http://localhost:5000/fujicafe/admin/

### 3. Features
- Modern dark-themed responsive UI
- Category management with position ordering
- Menu item management with image upload
- Active/inactive item toggle
- Search and category filtering
- CSRF protection on all forms
- Server-side validation

## Development Notes
- PHP 8.3+
- No external dependencies required
- Vanilla JavaScript (no frameworks)
- Custom CSS with CSS variables
- Session-based CSRF tokens
- PDO with prepared statements

## Security
- CSRF tokens on all POST requests
- Server-side input validation
- Prepared SQL statements (SQL injection prevention)
- File upload validation (type and size)
- htmlspecialchars for output escaping

## Recent Changes
- 2025-11-09: Initial setup with complete CRUD functionality
- Modern dark UI implementation
- Admin panel with category and item management
- Image upload capability for menu items
