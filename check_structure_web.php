<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Structure Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h2>Database Structure Check</h2>
            </div>
            <div class="card-body">
                <h4>News Table Columns:</h4>
                <pre class="bg-light p-3 border"><?php
                $result = mysqli_query($conn, "DESCRIBE news");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo $row['Field'] . " - " . $row['Type'] . "\n";
                }
                ?></pre>
                
                <h4 class="mt-4">Sample Data:</h4>
                <pre class="bg-light p-3 border"><?php
                $result = mysqli_query($conn, "SELECT * FROM news LIMIT 1");
                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    print_r($row);
                } else {
                    echo "No data found";
                }
                ?></pre>
            </div>
        </div>
    </div>
</body>
</html>
