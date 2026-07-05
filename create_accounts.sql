-- Create Reporter Account
INSERT INTO users (name, email, password, role, status, created_at) 
VALUES ('PK News Reporter', 'reporter@pklivenews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reporter', 'active', NOW())
ON DUPLICATE KEY UPDATE role = 'reporter', status = 'active';

-- Create Editor Account  
INSERT INTO users (name, email, password, role, status, created_at) 
VALUES ('PK News Editor', 'editor@pklivenews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor', 'active', NOW())
ON DUPLICATE KEY UPDATE role = 'editor', status = 'active';

-- Display created accounts
SELECT id, name, email, role, status, created_at FROM users WHERE email IN ('reporter@pklivenews.com', 'editor@pklivenews.com') ORDER BY created_at DESC;
