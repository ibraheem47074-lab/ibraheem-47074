<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || !is_admin()) {
    die('Unauthorized access');
}

// Build query based on filters
$where_conditions = [];
$params = [];
$types = '';

$filter_status = $_GET['filter_status'] ?? 'all';
$filter_role = $_GET['filter_role'] ?? 'all';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

if ($filter_status !== 'all') {
    $where_conditions[] = "ra.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if ($filter_role !== 'all') {
    $where_conditions[] = "ra.applied_role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($date_from)) {
    $where_conditions[] = "ra.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "ra.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";

// Get submissions for export
$query = "SELECT ra.id, u.name, u.email, ra.applied_role, ra.status, ra.created_at, ra.reviewed_at,
          ra.cv_file_name, ra.cv_file_size, ra.admin_notes
          FROM role_applications ra 
          JOIN users u ON ra.user_id = u.id 
          $where_clause
          ORDER BY ra.created_at DESC";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="role_applications_' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID',
    'Applicant Name',
    'Email',
    'Applied Role',
    'Status',
    'Application Date',
    'Review Date',
    'CV File',
    'CV Size',
    'Admin Notes'
]);

// Add data rows
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['email'],
        ucfirst($row['applied_role']),
        ucfirst($row['status']),
        date('Y-m-d H:i:s', strtotime($row['created_at'])),
        $row['reviewed_at'] ? date('Y-m-d H:i:s', strtotime($row['reviewed_at'])) : 'Not reviewed',
        $row['cv_file_name'] ?? 'No CV',
        $row['cv_file_size'] ? round($row['cv_file_size'] / 1024, 2) . ' KB' : 'N/A',
        $row['admin_notes'] ?? ''
    ]);
}

// Close the output stream
fclose($output);
exit();
?>
