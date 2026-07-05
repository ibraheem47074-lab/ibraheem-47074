<?php
require_once 'config/database.php';

// Get active poll
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT p.*, po.option_text, po.votes, po.id as option_id 
     FROM polls p 
     LEFT JOIN poll_options po ON p.id = po.poll_id 
     WHERE p.status = 'active' AND (p.ends_at IS NULL OR p.ends_at > NOW()) 
     ORDER BY p.id DESC, po.id ASC LIMIT 1"
));

// Get all poll options if active poll exists
$poll_options = [];
if ($active_poll) {
    $poll_id = $active_poll['id'];
    $options_query = "SELECT * FROM poll_options WHERE poll_id = $poll_id ORDER BY id ASC";
    $options_result = mysqli_query($conn, $options_query);
    while ($option = mysqli_fetch_assoc($options_result)) {
        $poll_options[] = $option;
    }
}

// Check actual votes
$actual_votes_query = "SELECT option_id, COUNT(*) as count FROM poll_votes WHERE poll_id = " . ($active_poll['id'] ?? 0) . " GROUP BY option_id";
$actual_votes_result = mysqli_query($conn, $actual_votes_query);
$actual_votes = [];
while ($row = mysqli_fetch_assoc($actual_votes_result)) {
    $actual_votes[$row['option_id']] = $row['count'];
}

$total_votes = array_sum(array_column($poll_options, 'votes'));
$actual_total_votes = array_sum($actual_votes);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Poll Debug Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Poll Debug Information</h1>
        
        <?php if ($active_poll): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Poll: <?php echo htmlspecialchars($active_poll['question']); ?></h3>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> <?php echo $active_poll['status']; ?></p>
                    <p><strong>Total votes (poll_options table):</strong> <?php echo $total_votes; ?></p>
                    <p><strong>Total votes (poll_votes table):</strong> <?php echo $actual_total_votes; ?></p>
                    
                    <?php if ($total_votes != $actual_total_votes): ?>
                        <div class="alert alert-danger">
                            <strong>ISSUE DETECTED:</strong> Vote counts don't match!
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            Vote counts are synchronized.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>Poll Options Comparison</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Option</th>
                                <th>Votes in poll_options</th>
                                <th>Actual Votes in poll_votes</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($poll_options as $option): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($option['option_text']); ?></td>
                                    <td><?php echo $option['votes']; ?></td>
                                    <td><?php echo $actual_votes[$option['id']] ?? 0; ?></td>
                                    <td>
                                        <?php if ($option['votes'] != ($actual_votes[$option['id']] ?? 0)): ?>
                                            <span class="badge bg-danger">Mismatch</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Match</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($total_votes != $actual_total_votes): ?>
                <div class="mt-4">
                    <button onclick="fixVotes()" class="btn btn-warning">Fix Vote Counts</button>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info">
                No active polls found.
            </div>
        <?php endif; ?>
    </div>

    <script>
    function fixVotes() {
        if (confirm('This will synchronize the vote counts. Continue?')) {
            fetch('fix_poll_votes.php')
                .then(response => response.text())
                .then(data => {
                    alert('Vote counts have been fixed. Refresh the page to see changes.');
                    location.reload();
                })
                .catch(error => {
                    alert('Error fixing votes: ' + error);
                });
        }
    }
    </script>
</body>
</html>
