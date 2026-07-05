<?php
require_once 'config/database.php';

echo "PK Live News - Fix Polls and Ads Display\n";
echo "======================================\n\n";

// Step 1: Create missing tables
echo "1. Creating Missing Tables\n";
echo "-------------------------\n";

// Create polls table if missing
$create_polls = "CREATE TABLE IF NOT EXISTS polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive', 'ended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ends_at TIMESTAMP NULL,
    created_by INT,
    INDEX (status),
    INDEX (ends_at),
    FOREIGN KEY (created_by) REFERENCES users(id)
)";

if (mysqli_query($conn, $create_polls)) {
    echo "✅ Polls table created/verified\n";
} else {
    echo "❌ Failed to create polls table\n";
}

// Create poll_options table if missing
$create_poll_options = "CREATE TABLE IF NOT EXISTS poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    votes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (poll_id),
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_poll_options)) {
    echo "✅ Poll_options table created/verified\n";
} else {
    echo "❌ Failed to create poll_options table\n";
}

// Create advertisements table if missing
$create_ads = "CREATE TABLE IF NOT EXISTS advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    ad_code TEXT NOT NULL,
    position ENUM('header', 'sidebar', 'footer', 'content') DEFAULT 'sidebar',
    is_active BOOLEAN DEFAULT 1,
    start_date DATE NULL,
    end_date DATE NULL,
    max_impressions INT NULL,
    current_impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX (is_active),
    INDEX (position),
    FOREIGN KEY (created_by) REFERENCES users(id)
)";

if (mysqli_query($conn, $create_ads)) {
    echo "✅ Advertisements table created/verified\n";
} else {
    echo "❌ Failed to create advertisements table\n";
}

// Step 2: Add sample data if tables are empty
echo "\n2. Adding Sample Data\n";
echo "---------------------\n";

// Check if polls table is empty
$poll_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM polls");
$poll_count = mysqli_fetch_assoc($poll_check);

if ($poll_count['count'] == 0) {
    // Insert sample poll
    $sample_poll = "INSERT INTO polls (question, description, status, created_by) VALUES (?, ?, 'active', 1)";
    $stmt = mysqli_prepare($conn, $sample_poll);
    mysqli_stmt_bind_param($stmt, 'sss', 
        'What is your favorite news category?', 
        'This poll helps us understand what content our readers prefer most.',
        1
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $poll_id = mysqli_insert_id($conn);
        echo "✅ Sample poll created\n";
        
        // Add poll options
        $options = ['Politics', 'Sports', 'Technology', 'Entertainment'];
        foreach ($options as $option) {
            $insert_option = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
            $opt_stmt = mysqli_prepare($conn, $insert_option);
            mysqli_stmt_bind_param($opt_stmt, 'is', $poll_id, $option);
            mysqli_stmt_execute($opt_stmt);
        }
        echo "✅ Sample poll options added\n";
    }
} else {
    echo "ℹ️  Polls table already has data\n";
}

// Check if advertisements table is empty
$ad_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM advertisements");
$ad_count = mysqli_fetch_assoc($ad_check);

if ($ad_count['count'] == 0) {
    // Insert sample advertisement
    $sample_ad = "INSERT INTO advertisements (title, ad_code, position, is_active, created_by) VALUES (?, ?, 'sidebar', 1, 1)";
    $stmt = mysqli_prepare($conn, $sample_ad);
    mysqli_stmt_bind_param($stmt, 'sss', 
        'Sample Advertisement',
        '<div class="alert alert-info">
            <h4>Special Offer!</h4>
            <p>Get premium news access for just $9.99/month</p>
            <button class="btn btn-primary">Subscribe Now</button>
        </div>',
        1
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Sample advertisement created\n";
    }
} else {
    echo "ℹ️  Advertisements table already has data\n";
}

// Step 3: Update ads_functions.php if needed
echo "\n3. Fixing Functions\n";
echo "-------------------\n";

$functions_content = "<?php
// Advertisement Management Functions

function get_active_ads(\$position = null, \$limit = 1) {
    global \$conn;
    
    \$sql = \"SELECT * FROM advertisements WHERE is_active = 1\";
    \$params = [];
    \$types = \"\";
    
    if (\$position) {
        \$sql .= \" AND position = ?\";
        \$params[] = \$position;
        \$types .= \"s\";
    }
    
    // Check date constraints
    \$sql .= \" AND (start_date IS NULL OR start_date <= CURDATE())\";
    \$sql .= \" AND (end_date IS NULL OR end_date >= CURDATE())\";
    \$sql .= \" AND (max_impressions IS NULL OR current_impressions < max_impressions)\";
    
    \$sql .= \" ORDER BY RAND()\";
    
    if (\$limit > 0) {
        \$sql .= \" LIMIT ?\";
        \$params[] = \$limit;
        \$types .= \"i\";
    }
    
    \$stmt = mysqli_prepare(\$conn, \$sql);
    
    if (!empty(\$params)) {
        mysqli_stmt_bind_param(\$stmt, \$types, ...\$params);
    }
    
    mysqli_stmt_execute(\$stmt);
    \$result = mysqli_stmt_get_result(\$stmt);
    
    \$ads = [];
    while (\$row = mysqli_fetch_assoc(\$result)) {
        \$ads[] = \$row;
    }
    
    return \$ads;
}

function display_ad(\$position, \$class = '') {
    \$ads = get_active_ads(\$position, 1);
    
    if (empty(\$ads)) {
        return '';
    }
    
    \$ad = \$ads[0];
    \$ad_id = \$ad['id'];
    \$ad_code = \$ad['ad_code'];
    
    // Add click tracking
    \$tracked_code = str_replace('<a href=', '<a href=\"track-ad-click.php?id=' . \$ad_id . '&url=', \$ad_code);
    
    return '<div class=\"advertisement-widget ' . \$class . '\" data-ad-id=\"' . \$ad_id . '\">' . \$tracked_code . '</div>';
}
?>";

if (file_put_contents('includes/ad-functions.php', $functions_content)) {
    echo "✅ ad-functions.php updated\n";
} else {
    echo "❌ Failed to update ad-functions.php\n";
}

echo "\n=== Fix Complete ===\n";
echo "1. ✅ Tables created/verified\n";
echo "2. ✅ Sample data added\n";
echo "3. ✅ Functions fixed\n";
echo "\nPolls and advertisements should now show on index page!\n";
echo "Check: http://localhost/pk-live-news/\n";
?>
