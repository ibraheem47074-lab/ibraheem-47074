<?php
// Simple Environment Test - Uses PHP's built-in parse_ini_file
echo "<h1>Simple Environment Test</h1>";

$env_file = __DIR__ . '/.env';

echo "<h2>1. File Check</h2>";
if (file_exists($env_file)) {
    echo "✓ .env file exists<br>";
    echo "File size: " . filesize($env_file) . " bytes<br>";
} else {
    echo "✗ .env file not found<br>";
    exit;
}

echo "<h2>2. Using parse_ini_file()</h2>";
$env_data = parse_ini_file($env_file);
if ($env_data !== false) {
    echo "✓ Successfully parsed with parse_ini_file()<br>";
    echo "<pre>" . htmlspecialchars(print_r($env_data, true)) . "</pre>";
    
    echo "<h3>Key Variables:</h3>";
    echo "DB_HOST: " . ($env_data['DB_HOST'] ?? 'Not set') . "<br>";
    echo "DB_USER: " . ($env_data['DB_USER'] ?? 'Not set') . "<br>";
    echo "DB_PASS: " . ($env_data['DB_PASS'] ?? 'Not set') . "<br>";
    echo "DB_NAME: " . ($env_data['DB_NAME'] ?? 'Not set') . "<br>";
    echo "SITE_URL: " . ($env_data['SITE_URL'] ?? 'Not set') . "<br>";
} else {
    echo "✗ parse_ini_file() failed<br>";
}

echo "<h2>3. Manual Line Parsing</h2>";
$content = file_get_contents($env_file);
$lines = explode("\n", $content);
$manual_env = [];

foreach ($lines as $line_num => $line) {
    $line = trim($line);
    $line_num++; // 1-based for display
    
    if (empty($line) || strpos($line, '#') === 0) {
        continue;
    }
    
    echo "Line $line_num: " . htmlspecialchars($line) . "<br>";
    
    if (strpos($line, '=') !== false) {
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            
            echo "  Key: '$key', Value: '$value'<br>";
            
            // Handle quoted values
            if ((strlen($value) >= 2) && 
                (($value[0] === '"' && $value[-1] === '"') || 
                 ($value[0] === "'" && $value[-1] === "'"))) {
                $value = substr($value, 1, -1);
                echo "  Unquoted value: '$value'<br>";
            }
            
            $manual_env[$key] = $value;
            echo "  ✓ Parsed successfully<br>";
        } else {
            echo "  ✗ Invalid format<br>";
        }
    } else {
        echo "  ✗ No equals sign found<br>";
    }
    echo "<br>";
}

echo "<h2>4. Manual Parse Results</h2>";
echo "<pre>" . htmlspecialchars(print_r($manual_env, true)) . "</pre>";

echo "<h2>5. Test Database Connection with Manual Env</h2>";
if (isset($manual_env['DB_HOST'])) {
    try {
        $conn = mysqli_connect(
            $manual_env['DB_HOST'],
            $manual_env['DB_USER'] ?? '',
            $manual_env['DB_PASS'] ?? '',
            $manual_env['DB_NAME'] ?? ''
        );
        
        if ($conn) {
            echo "✓ Database connection successful<br>";
            mysqli_close($conn);
        } else {
            echo "✗ Database connection failed<br>";
        }
    } catch (Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ DB_HOST not found in environment<br>";
}

echo "<h2>Test Complete</h2>";
?>
