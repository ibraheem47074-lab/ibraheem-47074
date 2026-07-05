<?php
require_once 'config/database.php';

$result = mysqli_query($conn, "SHOW COLUMNS FROM news_sources");
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . "\n";
}
?>
