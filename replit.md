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
│   ├── items.php            # Menu items CRUD
│   └── settings.php         # Restaurant settings & logo upload
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
- **menu_item_reviews**: id, item_id, customer_name, rating, comment, created_at
- **restaurant_settings**: id, logo_url, restaurant_name, restaurant_subtitle, created_at, updated_at

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
- Modern dark-themed responsive UI with smooth animations
- Slide-out sidebar navigation with hamburger menu
- Smooth page sections: Menu, Feedback, Contact Us, and Reviews
- Category management with position ordering
- Menu item management with image upload
- Customer reviews and ratings system
- Active/inactive item toggle
- Search and category filtering
- Social media links (Facebook, Instagram, TikTok)
- Keyboard navigation support (Escape key closes sidebar)
- Restaurant branding customization:
  - Logo upload with secure validation
  - Restaurant name and subtitle configuration
  - Automatic fallback to initials when no logo is uploaded
- CSRF protection on all forms
- Server-side validation
- Engaging animations:
  - Fade-in animations for menu cards
  - Slide-in animations for category pills
  - Smooth scrolling between sections
  - Hover effects on all interactive elements

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
- File upload validation:
  - getimagesize() validation to verify actual image content
  - Extension whitelisting (jpg, png, gif, webp only)
  - Forced safe extensions based on image type, not user input
  - File size limits (5MB for logos)
  - .htaccess protection to prevent PHP execution in uploads directory
- htmlspecialchars for output escaping

## Recent Changes
- 2025-11-09: Logo upload and restaurant branding feature
  - Created restaurant_settings table for branding customization
  - Added admin/settings.php page with logo upload functionality
  - Implemented secure file upload validation using getimagesize()
  - Added .htaccess protection in uploads directory to prevent PHP execution
  - Logo displays in hero section and sidebar with fallback to initials
  - Restaurant name and subtitle now configurable from admin panel
  - Fixed critical security vulnerability in file upload (RCE prevention)

- 2025-11-09: Initial setup with complete CRUD functionality
  - PostgreSQL database setup with schema_postgres.sql
  - Created menu_categories, menu_items, and menu_item_reviews tables
  - Modern dark UI implementation
  - Admin panel with category and item management
  - Image upload capability for menu items
  - Slide-out sidebar navigation with hamburger menu
  - Added Feedback, Contact Us, and Review sections
  - Smooth animations throughout the site (fade-in, slide-in, hover effects)
  - Social media integration (Facebook, Instagram, TikTok)
  - Customer reviews and ratings system
  - Keyboard navigation support (Escape key closes sidebar)
