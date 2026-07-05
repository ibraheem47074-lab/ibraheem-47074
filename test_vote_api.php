<!DOCTYPE html>
<html>
<head>
    <title>Vote API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .debug { background: #f5f5f5; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Vote API Test</h1>
    
    <div class="test-section">
        <h2>Test Vote Submission</h2>
        <div id="testResults"></div>
        
        <button onclick="testVoteSubmission()">Test Vote Submission</button>
        <button onclick="clearResults()">Clear Results</button>
    </div>
    
    <div class="test-section">
        <h2>Manual Vote Form</h2>
        <form id="manualVoteForm">
            <div>
                <label>Poll ID:</label>
                <input type="number" id="manualPollId" value="1" required>
            </div>
            <div>
                <label>Option ID:</label>
                <input type="number" id="manualOptionId" value="1" required>
            </div>
            <button type="submit">Submit Vote</button>
        </form>
    </div>

    <script>
    function clearResults() {
        document.getElementById('testResults').innerHTML = '';
    }
    
    function addResult(message, isError = false) {
        const resultsDiv = document.getElementById('testResults');
        const div = document.createElement('div');
        div.className = isError ? 'error' : 'success';
        div.innerHTML = message;
        resultsDiv.appendChild(div);
    }
    
    function addDebug(message) {
        const resultsDiv = document.getElementById('testResults');
        const div = document.createElement('div');
        div.className = 'debug';
        div.innerHTML = '<pre>' + message + '</pre>';
        resultsDiv.appendChild(div);
    }
    
    async function testVoteSubmission() {
        clearResults();
        addResult('Starting vote submission test...');
        
        try {
            // First, get current poll data
            addResult('Fetching current poll data...');
            const pollResponse = await fetch('get_poll_results.php?poll_id=1');
            const pollData = await pollResponse.json();
            
            if (pollData.success) {
                addResult('✅ Poll data retrieved successfully');
                addDebug('Poll data: ' + JSON.stringify(pollData, null, 2));
                
                // Test vote submission
                addResult('Testing vote submission...');
                
                const formData = new FormData();
                formData.append('poll_id', '1');
                formData.append('poll_option', '1'); // Vote for first option
                
                const voteResponse = await fetch('vote_poll.php', {
                    method: 'POST',
                    body: formData
                });
                
                addDebug('Response status: ' + voteResponse.status);
                addDebug('Response headers: ' + JSON.stringify([...voteResponse.headers.entries()]));
                
                const voteData = await voteResponse.json();
                
                if (voteData.success) {
                    addResult('✅ Vote submitted successfully');
                    addDebug('Vote response: ' + JSON.stringify(voteData, null, 2));
                } else {
                    addResult('❌ Vote submission failed: ' + voteData.message, true);
                    addDebug('Vote response: ' + JSON.stringify(voteData, null, 2));
                }
                
            } else {
                addResult('❌ Failed to get poll data: ' + pollData.message, true);
                addDebug('Poll response: ' + JSON.stringify(pollData, null, 2));
            }
            
        } catch (error) {
            addResult('❌ Network error: ' + error.message, true);
            addDebug('Error details: ' + error.stack);
        }
    }
    
    // Manual form submission
    document.getElementById('manualVoteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const pollId = document.getElementById('manualPollId').value;
        const optionId = document.getElementById('manualOptionId').value;
        
        addResult(`Manual vote test - Poll ID: ${pollId}, Option ID: ${optionId}`);
        
        try {
            const formData = new FormData();
            formData.append('poll_id', pollId);
            formData.append('poll_option', optionId);
            
            const response = await fetch('vote_poll.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                addResult('✅ Manual vote successful');
                addDebug('Response: ' + JSON.stringify(data, null, 2));
            } else {
                addResult('❌ Manual vote failed: ' + data.message, true);
                addDebug('Response: ' + JSON.stringify(data, null, 2));
            }
            
        } catch (error) {
            addResult('❌ Manual vote error: ' + error.message, true);
            addDebug('Error: ' + error.stack);
        }
    });
    </script>
</body>
</html>
