-- Database schema for Fuji Cafe Digital Menu (PostgreSQL)
-- Run this script to create the required tables

CREATE TABLE IF NOT EXISTS menu_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_items (
    id SERIAL PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    is_active SMALLINT DEFAULT 1,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
);

-- Sample data (optional)
INSERT INTO menu_categories (name, position) VALUES
('Coffee & Espresso', 1),
('Tea & Matcha', 2),
('Pastries & Sweets', 3),
('Sandwiches', 4)
ON CONFLICT DO NOTHING;

INSERT INTO menu_items (category_id, name, price, description, is_active, position) VALUES
(1, 'Cappuccino', 4.50, 'Classic Italian espresso with steamed milk and foam', 1, 1),
(1, 'Latte', 4.75, 'Smooth espresso with steamed milk', 1, 2),
(1, 'Cold Brew', 4.25, 'Smooth, refreshing cold-brewed coffee', 1, 3),
(2, 'Matcha Latte', 5.25, 'Premium Japanese matcha with steamed milk', 1, 1),
(2, 'Green Tea', 3.50, 'Traditional Japanese sencha green tea', 1, 2),
(3, 'Croissant', 3.75, 'Buttery, flaky French croissant', 1, 1),
(3, 'Chocolate Muffin', 3.25, 'Rich chocolate muffin with chocolate chips', 1, 2),
(4, 'Turkey Club', 8.95, 'Turkey, bacon, lettuce, tomato on toasted bread', 1, 1)
ON CONFLICT DO NOTHING;
