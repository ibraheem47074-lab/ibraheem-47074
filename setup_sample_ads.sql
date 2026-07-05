-- Create advertisements table if it doesn't exist
CREATE TABLE IF NOT EXISTS advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    position ENUM('header', 'sidebar', 'footer', 'all') DEFAULT 'sidebar',
    image VARCHAR(500),
    redirect_url VARCHAR(500),
    code TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample advertisements
INSERT INTO advertisements (title, position, image, redirect_url, status, start_date, end_date) VALUES 
('Sample Business Ad - Sidebar', 'sidebar', 'uploads/ads/69adaaa0ab59c.jpg', 'https://example-business.com', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('Tech Store Promotion', 'header', 'uploads/ads/69adaaa0ab59c.jpg', 'https://techstore.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('Local Services Ad', 'footer', 'uploads/ads/69adaaa0ab59c.jpg', 'https://localservices.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('Restaurant Special Offer', 'sidebar', 'uploads/ads/69adaaa0ab59c.jpg', 'https://restaurant.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('E-commerce Banner', 'all', 'uploads/ads/69adaaa0ab59c.jpg', 'https://shop.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY));
