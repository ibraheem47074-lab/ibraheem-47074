<?php
// Test script to verify htmlspecialchars fixes
require_once 'config/database.php';

echo "<h2>Testing htmlspecialchars Fixes</h2>";

// Get a test user
$query = "SELECT * FROM users LIMIT 1";
$result = mysqli_query($conn, $query);
$test_user = mysqli_fetch_assoc($result);

echo "<h3>Test User Data:</h3>";
echo "<pre>";
print_r($test_user);
echo "</pre>";

echo "<h3>Testing htmlspecialchars with null values:</h3>";

// Test each field that might be null
$fields_to_test = [
    'phone' => $test_user['phone'] ?? null,
    'bio' => $test_user['bio'] ?? null,
    'social_links' => $test_user['social_links'] ?? null,
    'specialization' => $test_user['specialization'] ?? null,
    'skills' => $test_user['skills'] ?? null,
    'working_hours' => $test_user['working_hours'] ?? null
];

foreach ($fields_to_test as $field => $value) {
    echo "<p><strong>$field:</strong> ";
    if ($value === null) {
        echo "NULL - ";
        try {
            $result = htmlspecialchars($value);
            echo "htmlspecialchars() failed with warning";
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    } else {
        echo "'$value' - ";
        try {
            $result = htmlspecialchars($value);
            echo "htmlspecialchars() worked: '$result'";
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }
    echo "</p>";
}

echo "<h3>Testing with null coalescing:</h3>";
foreach ($fields_to_test as $field => $value) {
    echo "<p><strong>$field:</strong> ";
    try {
        $result = htmlspecialchars($value ?? '');
        echo "htmlspecialchars(\$value ?? '') worked: '$result'";
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }
    echo "</p>";
}

echo "<h3>Safe htmlspecialchars function:</h3>";
function safe_htmlspecialchars($value) {
    return htmlspecialchars($value ?? '');
}

foreach ($fields_to_test as $field => $value) {
    echo "<p><strong>$field:</strong> ";
    $result = safe_htmlspecialchars($value);
    echo "safe_htmlspecialchars(): '$result'";
    echo "</p>";
}

echo "<hr>";
echo "<p><a href='admin/advanced-profile.php'>Test Advanced Profile</a></p>";
?>
