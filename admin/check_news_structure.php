<?php
require_once '../config/database.php';

echo "<h2>News Table Structure</h2>";
$result = mysqli_query($conn, 'DESCRIBE news');

echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . $row['Key'] . "</td><td>" . $row['Default'] . "</td></tr>";
}
echo "</table>";
?>
