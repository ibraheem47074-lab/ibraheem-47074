-- Create poll_options table
CREATE TABLE poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    votes INT DEFAULT 0,
    order_position INT DEFAULT 0,
    INDEX (poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
