<?php
require_once 'config/database.php';

echo '=== Polls Table Structure ===' . PHP_EOL;
$result = mysqli_query($conn, 'DESCRIBE polls');
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . PHP_EOL;
}

echo PHP_EOL . '=== Poll Options Table Structure ===' . PHP_EOL;
$result = mysqli_query($conn, 'DESCRIBE poll_options');
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . PHP_EOL;
}

echo PHP_EOL . '=== Poll Votes Table Structure ===' . PHP_EOL;
$result = mysqli_query($conn, 'DESCRIBE poll_votes');
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . PHP_EOL;
}

echo PHP_EOL . '=== Current Poll Data ===' . PHP_EOL;
$result = mysqli_query($conn, 'SELECT * FROM polls WHERE status = "active" LIMIT 1');
if ($row = mysqli_fetch_assoc($result)) {
    echo 'Active Poll ID: ' . $row['id'] . PHP_EOL;
    echo 'Question: ' . $row['question'] . PHP_EOL;
    
    echo PHP_EOL . '=== Poll Options ===' . PHP_EOL;
    $options_result = mysqli_query($conn, 'SELECT * FROM poll_options WHERE poll_id = ' . $row['id']);
    while ($option = mysqli_fetch_assoc($options_result)) {
        echo 'Option ID: ' . $option['id'] . ' - Text: ' . $option['option_text'] . ' - Votes: ' . $option['votes'] . PHP_EOL;
    }
    
    echo PHP_EOL . '=== Poll Votes ===' . PHP_EOL;
    $votes_result = mysqli_query($conn, 'SELECT * FROM poll_votes WHERE poll_id = ' . $row['id']);
    echo 'Total votes in poll_votes table: ' . mysqli_num_rows($votes_result) . PHP_EOL;
    while ($vote = mysqli_fetch_assoc($votes_result)) {
        echo 'Vote ID: ' . $vote['id'] . ' - Option ID: ' . $vote['option_id'] . ' - IP: ' . $vote['ip_address'] . PHP_EOL;
    }
} else {
    echo 'No active polls found' . PHP_EOL;
}
?>
