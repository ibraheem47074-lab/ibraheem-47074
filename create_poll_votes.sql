-- Create poll_votes table
CREATE TABLE poll_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_id INT NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (poll_id),
    INDEX (option_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
