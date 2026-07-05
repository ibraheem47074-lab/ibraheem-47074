<?php
require_once 'config/database.php';

echo "<h3>News Table Structure:</h3>";
$result = mysqli_query($conn, "DESCRIBE news");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
