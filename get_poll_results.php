<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Get poll ID from query parameter
$poll_id = isset($_GET['poll_id']) ? (int)$_GET['poll_id'] : 0;

if (empty($poll_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid poll ID']);
    exit;
}

// Get poll details and options with vote counts
$query = "SELECT 
            p.id, p.question, p.description, p.status, p.created_at,
            po.id as option_id, po.option_text, po.votes,
            (SELECT COUNT(*) FROM poll_votes WHERE poll_id = p.id) as total_votes
          FROM polls p
          LEFT JOIN poll_options po ON p.id = po.poll_id
          WHERE p.id = ?
          ORDER BY po.id";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $poll_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Poll not found']);
    exit;
}

$poll_data = [];
$options = [];
$total_votes = 0;

while ($row = mysqli_fetch_assoc($result)) {
    if (empty($poll_data)) {
        $poll_data = [
            'id' => $row['id'],
            'question' => $row['question'],
            'description' => $row['description'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'total_votes' => $row['total_votes']
        ];
        $total_votes = $row['total_votes'];
    }
    
    if ($row['option_id']) {
        $percentage = $total_votes > 0 ? round(($row['votes'] / $total_votes) * 100, 1) : 0;
        $options[] = [
            'id' => $row['option_id'],
            'option_text' => $row['option_text'],
            'votes' => $row['votes'],
            'percentage' => $percentage
        ];
    }
}

echo json_encode([
    'success' => true,
    'poll' => $poll_data,
    'options' => $options,
    'total_votes' => $total_votes
]);

mysqli_close($conn);
?>
