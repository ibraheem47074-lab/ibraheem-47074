<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>System Status Verification</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <h2 class='text-center mb-4'>System Status Verification</h2>";

// Test database connection
echo "<div class='card mb-4'>
        <div class='card-header bg-primary text-white'>
            <h4>â Database Connection</h4>
        </div>
        <div class='card-body'>";
        
if (isset($conn) && $conn) {
    echo "<div class='alert alert-success'>â Database connection successful</div>";
    
    // Get all tables
    $result = mysqli_query($conn, "SHOW TABLES");
    $tables = [];
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    
    echo "<p><strong>Total Tables:</strong> " . count($tables) . "</p>";
    
    // Check critical tables
    $critical_tables = ['news', 'articles', 'channels', 'users', 'categories', 'admin'];
    $missing_tables = array_diff($critical_tables, $tables);
    
    if (empty($missing_tables)) {
        echo "<div class='alert alert-success'>â All critical tables exist</div>";
    } else {
        echo "<div class='alert alert-danger'>
                â Missing critical tables: " . implode(', ', $missing_tables) . "
              </div>";
    }
    
    // Test news table specifically
    if (in_array('news', $tables)) {
        $news_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
        $count = mysqli_fetch_assoc($news_count)['count'];
        echo "<p><strong>News Records:</strong> " . number_format($count) . "</p>";
        
        if ($count > 0) {
            echo "<div class='alert alert-success'>â News table has data</div>";
        } else {
            echo "<div class='alert alert-warning'>â News table is empty</div>";
        }
    }
    
} else {
    echo "<div class='alert alert-danger'>â Database connection failed</div>";
}

echo "</div></div>";

// Test key files
echo "<div class='card mb-4'>
        <div class='card-header bg-info text-white'>
            <h4>â Key Files Check</h4>
        </div>
        <div class='card-body'>";

$key_files = [
    'config/database.php' => 'Database Configuration',
    'admin/website_control.php' => 'Admin Control Panel',
    'live.php' => 'Live TV Page',
    'index.php' => 'Home Page',
    'includes/header.php' => 'Header Component',
    'includes/footer.php' => 'Footer Component'
];

foreach ($key_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='alert alert-success'>â $description - Found</div>";
    } else {
        echo "<div class='alert alert-danger'>â $description - Missing</div>";
    }
}

echo "</div></div>";

// Test live channels
echo "<div class='card mb-4'>
        <div class='card-header bg-success text-white'>
            <h4>â Live Channels Status</h4>
        </div>
        <div class='card-body'>";

if (isset($conn) && $conn) {
    $channels_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM channels");
    if ($channels_result) {
        $channels_count = mysqli_fetch_assoc($channels_result)['count'];
        echo "<p><strong>Total Channels:</strong> " . number_format($channels_count) . "</p>";
        
        if ($channels_count > 0) {
            echo "<div class='alert alert-success'>â Live channels available</div>";
            
            // Get featured channels
            $featured_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM channels WHERE is_featured = 1");
            $featured_count = mysqli_fetch_assoc($featured_result)['count'];
            echo "<p><strong>Featured Channels:</strong> " . number_format($featured_count) . "</p>";
            
            // Get live channels
            $live_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM channels WHERE status = 'live'");
            $live_count = mysqli_fetch_assoc($live_result)['count'];
            echo "<p><strong>Live Now:</strong> " . number_format($live_count) . "</p>";
            
        } else {
            echo "<div class='alert alert-warning'>â No channels found</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>â Channels table query failed</div>";
    }
} else {
    echo "<div class='alert alert-danger'>â Cannot check channels - no database connection</div>";
}

echo "</div></div>";

// Test admin functionality
echo "<div class='card mb-4'>
        <div class='card-header bg-warning text-dark'>
            <h4>â Admin Functionality Test</h4>
        </div>
        <div class='card-body'>";

// Test the specific query that was failing
if (isset($conn) && $conn) {
    $test_query = "SELECT COUNT(*) as count FROM news";
    $result = mysqli_query($conn, $test_query);
    
    if ($result) {
        $count = mysqli_fetch_assoc($result)['count'];
        echo "<div class='alert alert-success'>
                â Admin query successful - News count: " . number_format($count) . "
              </div>";
    } else {
        echo "<div class='alert alert-danger'>
                â Admin query failed: " . mysqli_error($conn) . "
              </div>";
    }
    
    // Test articles table
    $articles_check = mysqli_query($conn, "SHOW TABLES LIKE 'articles'");
    if (mysqli_num_rows($articles_check) > 0) {
        $articles_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM articles");
        $articles_num = mysqli_fetch_assoc($articles_count)['count'];
        echo "<p><strong>Articles:</strong> " . number_format($articles_num) . "</p>";
    } else {
        echo "<div class='alert alert-warning'>â Articles table not found</div>";
    }
    
} else {
    echo "<div class='alert alert-danger'>â Cannot test admin functionality - no database connection</div>";
}

echo "</div></div>";

// Overall status
echo "<div class='card mb-4'>
        <div class='card-header bg-dark text-white'>
            <h4>â Overall System Status</h4>
        </div>
        <div class='card-body text-center'>";

$issues = [];

if (!isset($conn) || !$conn) $issues[] = "Database connection";
if (isset($conn) && $conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
    if (!mysqli_num_rows($result)) $issues[] = "News table";
}

if (empty($issues)) {
    echo "<div class='alert alert-success'>
            <h4>â System is Fully Operational!</h4>
            <p>All critical components are working properly.</p>
          </div>";
} else {
    echo "<div class='alert alert-warning'>
            <h4>â System Issues Detected</h4>
            <p>Issues found: " . implode(', ', $issues) . "</p>
          </div>";
}

echo "</div></div>";

// Action buttons
echo "<div class='text-center mt-4'>
        <a href='admin/website_control.php' class='btn btn-success btn-lg me-2'>Admin Control Panel</a>
        <a href='live.php' class='btn btn-danger btn-lg me-2'>Live TV</a>
        <a href='index.php' class='btn btn-primary btn-lg me-2'>Home Page</a>
        <a href='check_database_status.php' class='btn btn-info btn-lg'>Database Status</a>
      </div>";

echo "</div></body></html>";
?>
