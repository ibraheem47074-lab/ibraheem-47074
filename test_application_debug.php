<?php
// Simple test to check if API endpoint exists and is accessible
echo "<h2>API Endpoint Test</h2>";

$api_file = 'api/submit_role_application.php';
if (file_exists($api_file)) {
    echo "✅ API file exists: $api_file<br>";
    
    // Check if file is readable
    if (is_readable($api_file)) {
        echo "✅ API file is readable<br>";
    } else {
        echo "❌ API file is not readable<br>";
    }
    
    // Check file size
    $size = filesize($api_file);
    echo "📄 File size: " . number_format($size) . " bytes<br>";
    
} else {
    echo "❌ API file not found: $api_file<br>";
}

// Test database connection
echo "<h3>Database Connection Test</h3>";
try {
    require_once 'config/database.php';
    if ($conn && mysqli_ping($conn)) {
        echo "✅ Database connection is working<br>";
        
        // Check if role_applications table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'role_applications'");
        if (mysqli_num_rows($table_check) > 0) {
            echo "✅ role_applications table exists<br>";
        } else {
            echo "❌ role_applications table missing<br>";
        }
        
        // Check users table structure
        $columns_check = mysqli_query($conn, "DESCRIBE users");
        $columns = [];
        while ($row = mysqli_fetch_assoc($columns_check)) {
            $columns[] = $row['Field'];
        }
        
        if (in_array('application_status', $columns)) {
            echo "✅ users table has application_status column<br>";
        } else {
            echo "❌ users table missing application_status column<br>";
        }
        
        if (in_array('applied_role', $columns)) {
            echo "✅ users table has applied_role column<br>";
        } else {
            echo "❌ users table missing applied_role column<br>";
        }
        
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test session
echo "<h3>Session Test</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    echo "✅ User is logged in (ID: " . $_SESSION['user_id'] . ")<br>";
} else {
    echo "ℹ️ User not logged in - this is expected for direct API testing<br>";
}

// Test file uploads directory
echo "<h3>Upload Directory Test</h3>";
$upload_dir = 'uploads/cv';
if (is_dir($upload_dir)) {
    echo "✅ Upload directory exists: $upload_dir<br>";
    if (is_writable($upload_dir)) {
        echo "✅ Upload directory is writable<br>";
    } else {
        echo "❌ Upload directory is not writable<br>";
    }
} else {
    echo "❌ Upload directory missing: $upload_dir<br>";
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Created upload directory<br>";
    } else {
        echo "❌ Failed to create upload directory<br>";
    }
}

echo "<h3>Next Steps</h3>";
echo "<p>1. Open browser developer tools (F12)<br>";
echo "2. Go to Console tab<br>";
echo "3. Try submitting an application form<br>";
echo "4. Check for JavaScript errors and console logs<br>";
echo "5. Check Network tab for API requests<br></p>";

echo "<p><a href='profile.php'>Go to Profile Page</a> to test the application form.</p>";
?>
