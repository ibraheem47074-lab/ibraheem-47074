<?php
require_once 'config/database.php';

echo "Checking news table structure...\n";
$result = mysqli_query($conn, "DESCRIBE news");
echo "Field - Type\n";
echo "-----------\n";
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>
