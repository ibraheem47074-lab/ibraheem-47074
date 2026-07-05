<?php
require_once 'config/database.php';

// Get current poll data
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM polls WHERE status = 'active' AND (ends_at IS NULL OR ends_at > NOW()) ORDER BY id DESC LIMIT 1"
));

$poll_options = [];
if ($active_poll) {
    $options_result = mysqli_query($conn, "SELECT * FROM poll_options WHERE poll_id = " . $active_poll['id'] . " ORDER BY id");
    while ($option = mysqli_fetch_assoc($options_result)) {
        $poll_options[] = $option;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vote Diagnostic Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Vote Diagnostic Tool</h1>
        
        <?php if ($active_poll): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Current Poll: <?php echo htmlspecialchars($active_poll['question']); ?></h3>
                </div>
                <div class="card-body">
                    <p><strong>Poll ID:</strong> <?php echo $active_poll['id']; ?></p>
                    <p><strong>Status:</strong> <?php echo $active_poll['status']; ?></p>
                    
                    <h5>Poll Options:</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Option ID</th>
                                <th>Text</th>
                                <th>Current Votes</th>
                                <th>Test Vote</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($poll_options as $option): ?>
                                <tr>
                                    <td><?php echo $option['id']; ?></td>
                                    <td><?php echo htmlspecialchars($option['option_text']); ?></td>
                                    <td id="votes_<?php echo $option['id']; ?>"><?php echo $option['votes']; ?></td>
                                    <td>
                                        <button onclick="testVote(<?php echo $active_poll['id']; ?>, <?php echo $option['id']; ?>)" class="btn btn-sm btn-primary">
                                            Test Vote
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Vote Test Results</h4>
                </div>
                <div class="card-body">
                    <div id="testResults"></div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>Manual Test Form</h4>
                </div>
                <div class="card-body">
                    <form id="manualTestForm">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Poll ID:</label>
                                <input type="number" name="poll_id" class="form-control" value="<?php echo $active_poll['id']; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Option ID:</label>
                                <input type="number" name="poll_option" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label><br>
                                <button type="submit" class="btn btn-success">Submit Test Vote</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
        <?php else: ?>
            <div class="alert alert-warning">
                No active poll found. Please create an active poll first.
            </div>
        <?php endif; ?>
    </div>

    <script>
    function addResult(message, isError = false) {
        const resultsDiv = document.getElementById('testResults');
        const div = document.createElement('div');
        div.className = 'alert ' + (isError ? 'alert-danger' : 'alert-success');
        div.innerHTML = message;
        resultsDiv.appendChild(div);
        
        // Auto-scroll to results
        resultsDiv.scrollTop = resultsDiv.scrollHeight;
    }
    
    function addDebug(message) {
        const resultsDiv = document.getElementById('testResults');
        const div = document.createElement('div');
        div.className = 'alert alert-info';
        div.innerHTML = '<pre>' + message + '</pre>';
        resultsDiv.appendChild(div);
    }
    
    async function testVote(pollId, optionId) {
        addResult(`Testing vote - Poll ID: ${pollId}, Option ID: ${optionId}`);
        
        try {
            const formData = new FormData();
            formData.append('poll_id', pollId);
            formData.append('poll_option', optionId);
            
            addDebug('Sending request to vote_poll.php...');
            
            const response = await fetch('vote_poll.php', {
                method: 'POST',
                body: formData
            });
            
            addDebug(`Response status: ${response.status} ${response.statusText}`);
            
            const contentType = response.headers.get('content-type');
            addDebug(`Content-Type: ${contentType}`);
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                addDebug('Response data: ' + JSON.stringify(data, null, 2));
                
                if (data.success) {
                    addResult('✅ Vote submitted successfully!');
                    // Update vote count display
                    const votesCell = document.getElementById('votes_' + optionId);
                    if (votesCell) {
                        votesCell.textContent = parseInt(votesCell.textContent) + 1;
                    }
                } else {
                    addResult('❌ Vote submission failed: ' + data.message, true);
                }
            } else {
                const text = await response.text();
                addResult('❌ Invalid response format (not JSON)', true);
                addDebug('Response text: ' + text);
            }
            
        } catch (error) {
            addResult('❌ Network error: ' + error.message, true);
            addDebug('Error details: ' + error.stack);
        }
    }
    
    // Manual form submission
    document.getElementById('manualTestForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const pollId = formData.get('poll_id');
        const optionId = formData.get('poll_option');
        
        testVote(pollId, optionId);
    });
    
    // Clear results function
    function clearResults() {
        document.getElementById('testResults').innerHTML = '';
    }
    </script>
    
    <div class="container mt-3">
        <button onclick="clearResults()" class="btn btn-secondary">Clear Results</button>
        <a href="poll_test.php" class="btn btn-info">Check Poll Data</a>
        <a href="index.php" class="btn btn-primary">Back to Index</a>
    </div>
</body>
</html>
