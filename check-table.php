<?php
require_once 'config/database.php';

// Check if table exists and show structure
$result = mysqli_query($conn, 'DESCRIBE advertisements');
if ($result) {
    echo 'Advertisements table structure:' . PHP_EOL;
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
    }
} else {
    echo 'Table does not exist or error: ' . mysqli_error($conn) . PHP_EOL;
}
?>
