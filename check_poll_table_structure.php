<?php
require_once 'config/database.php';

echo "=== CHECKING POLL TABLES STRUCTURE ===\n\n";

// Check polls table
echo "1. POLLS TABLE:\n";
$result = mysqli_query($conn, "DESCRIBE polls");
while ($row = mysqli_fetch_assoc($result)) {
    echo "   - {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']}\n";
}

echo "\n2. POLL_OPTIONS TABLE:\n";
$result = mysqli_query($conn, "DESCRIBE poll_options");
while ($row = mysqli_fetch_assoc($result)) {
    echo "   - {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']}\n";
}

echo "\n3. POLL_VOTES TABLE:\n";
$result = mysqli_query($conn, "DESCRIBE poll_votes");
while ($row = mysqli_fetch_assoc($result)) {
    echo "   - {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']}\n";
}

echo "\n=== SAMPLE DATA ===\n";

// Check if there's any data in poll_votes
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM poll_votes");
$count = mysqli_fetch_assoc($result)['count'];
echo "Poll votes table has $count records\n";

if ($count > 0) {
    echo "Sample poll_votes data:\n";
    $result = mysqli_query($conn, "SELECT * FROM poll_votes LIMIT 3");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "   ID: {$row['id']}, Poll ID: {$row['poll_id']}, Option ID: {$row['option_id']}";
        if (isset($row['user_id'])) {
            echo ", User ID: {$row['user_id']}";
        }
        if (isset($row['ip_address'])) {
            echo ", IP: {$row['ip_address']}";
        }
        echo "\n";
    }
}

echo "\n=== FIXING TABLE STRUCTURE ===\n";

// Check if user_id column exists in poll_votes
$result = mysqli_query($conn, "SHOW COLUMNS FROM poll_votes LIKE 'user_id'");
if (mysqli_num_rows($result) == 0) {
    echo "Adding user_id column to poll_votes table...\n";
    $alter_query = "ALTER TABLE poll_votes ADD COLUMN user_id INT NULL AFTER option_id";
    if (mysqli_query($conn, $alter_query)) {
        echo "✅ user_id column added successfully\n";
    } else {
        echo "❌ Error adding user_id column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✅ user_id column already exists\n";
}

// Check if ip_address column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM poll_votes LIKE 'ip_address'");
if (mysqli_num_rows($result) == 0) {
    echo "Adding ip_address column to poll_votes table...\n";
    $alter_query = "ALTER TABLE poll_votes ADD COLUMN ip_address VARCHAR(45) NULL AFTER user_id";
    if (mysqli_query($conn, $alter_query)) {
        echo "✅ ip_address column added successfully\n";
    } else {
        echo "❌ Error adding ip_address column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✅ ip_address column already exists\n";
}

// Check if voted_at column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM poll_votes LIKE 'voted_at'");
if (mysqli_num_rows($result) == 0) {
    echo "Adding voted_at column to poll_votes table...\n";
    $alter_query = "ALTER TABLE poll_votes ADD COLUMN voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER ip_address";
    if (mysqli_query($conn, $alter_query)) {
        echo "✅ voted_at column added successfully\n";
    } else {
        echo "❌ Error adding voted_at column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✅ voted_at column already exists\n";
}

echo "\n=== FINAL TABLE STRUCTURE ===\n";
echo "POLL_VOTES TABLE (after fixes):\n";
$result = mysqli_query($conn, "DESCRIBE poll_votes");
while ($row = mysqli_fetch_assoc($result)) {
    echo "   - {$row['Field']} ({$row['Type']}) - {$row['Null']} - {$row['Key']}\n";
}

echo "\n✅ Table structure check and fixes completed!\n";
?>
