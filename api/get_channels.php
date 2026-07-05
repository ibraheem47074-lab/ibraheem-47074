<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? $_GET['category'] : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build query
$query = "SELECT * FROM channels";
$params = [];
$types = '';

if ($category) {
    $query .= " WHERE category = ?";
    $params[] = $category;
    $types .= 's';
}

$query .= " ORDER BY sort_order ASC, is_featured DESC, name ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$channels = [];
while ($row = mysqli_fetch_assoc($result)) {
    $channels[] = $row;
}

echo json_encode([
    'success' => true,
    'channels' => $channels,
    'total' => mysqli_num_rows($result)
]);
?>
