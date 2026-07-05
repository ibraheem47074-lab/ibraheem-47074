<?php
// Test Environment Loading
// This script tests if the .env file is being parsed correctly

echo "<h1>Environment Loading Test</h1>";

// Test 1: Check if .env file exists
echo "<h2>1. .env File Check</h2>";
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    echo "✓ .env file exists at: $env_path<br>";
    
    // Show raw content (first 10 lines)
    echo "<h3>Raw .env content (first 10 lines):</h3>";
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<pre>";
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo htmlspecialchars($lines[$i]) . "\n";
    }
    echo "</pre>";
} else {
    echo "✗ .env file not found at: $env_path<br>";
}

// Test 2: Try to load environment manually
echo "<h2>2. Manual Environment Loading</h2>";
try {
    require_once 'config/env.php';
    EnvLoader::load();
    echo "✓ EnvLoader loaded successfully<br>";
} catch (Exception $e) {
    echo "✗ EnvLoader failed: " . $e->getMessage() . "<br>";
}

// Test 3: Check environment variables
echo "<h2>3. Environment Variables Check</h2>";
$required_vars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'SITE_URL'];

foreach ($required_vars as $var) {
    $env_value = getenv($var);
    $server_value = $_SERVER[$var] ?? 'Not set';
    $session_value = $_ENV[$var] ?? 'Not set';
    
    echo "<h4>$var:</h4>";
    echo "getenv(): " . ($env_value !== false ? $env_value : 'false') . "<br>";
    echo "\$_SERVER: $server_value<br>";
    echo "\$_ENV: $session_value<br>";
    
    if ($env_value !== false || $server_value !== 'Not set' || $session_value !== 'Not set') {
        echo "<span style='color: green;'>✓ Variable is set</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Variable not set</span><br>";
    }
    echo "<br>";
}

// Test 4: Parse .env manually line by line
echo "<h2>4. Manual Line-by-Line Parsing</h2>";
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line_num => $line) {
        $line_num++; // Make it 1-based for display
        
        if (strpos(trim($line), '#') === 0) {
            echo "Line $line_num: <em>Comment - " . htmlspecialchars($line) . "</em><br>";
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            echo "Line $line_num: <strong>$key</strong> = <code>" . htmlspecialchars($value) . "</code><br>";
            
            // Check for parsing issues
            if (empty($key)) {
                echo "  <span style='color: red;'>✗ Empty key</span><br>";
            }
            if ($value === '""' || $value === "''") {
                echo "  <span style='color: orange;'>⚠ Empty quoted value</span><br>";
            }
        } else {
            echo "Line $line_num: <span style='color: red;'>✗ Invalid line - " . htmlspecialchars($line) . "</span><br>";
        }
    }
}

// Test 5: Try alternative parsing method
echo "<h2>5. Alternative Parsing Method</h2>";
function parse_env_file($path) {
    $result = [];
    
    if (!file_exists($path)) {
        return $result;
    }
    
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Handle quoted values
                if (($value[0] === '"' && $value[-1] === '"') || 
                    ($value[0] === "'" && $value[-1] === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                $result[$key] = $value;
            }
        }
    }
    
    return $result;
}

$env_data = parse_env_file($env_path);
echo "<h3>Parsed environment data:</h3>";
echo "<pre>" . htmlspecialchars(print_r($env_data, true)) . "</pre>";

echo "<h2>Test Complete</h2>";
?>
