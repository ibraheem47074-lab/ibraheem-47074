<!DOCTYPE html>
<html>
<head>
    <title>Simple Vote Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin: 10px 0; padding: 10px; border: 1px solid #ddd; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .debug { background-color: #f8f9fa; padding: 10px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Simple Vote Test</h1>
    
    <button onclick="testVote()">Test Vote Submission</button>
    <button onclick="clearResults()">Clear Results</button>
    
    <div id="results"></div>

    <script>
    function clearResults() {
        document.getElementById('results').innerHTML = '';
    }
    
    function addResult(message, isError = false) {
        const results = document.getElementById('results');
        const div = document.createElement('div');
        div.className = 'result ' + (isError ? 'error' : 'success');
        div.textContent = message;
        results.appendChild(div);
    }
    
    function addDebug(message) {
        const results = document.getElementById('results');
        const div = document.createElement('div');
        div.className = 'debug';
        div.textContent = message;
        results.appendChild(div);
    }
    
    async function testVote() {
        clearResults();
        addResult('Starting vote test...');
        
        try {
            const formData = new FormData();
            formData.append('poll_id', '1');
            formData.append('poll_option', '1');
            
            addDebug('Sending POST request to vote_poll.php...');
            
            const response = await fetch('vote_poll.php', {
                method: 'POST',
                body: formData
            });
            
            addDebug(`Response status: ${response.status}`);
            addDebug(`Content-Type: ${response.headers.get('content-type')}`);
            
            const responseText = await response.text();
            addDebug('Raw response:');
            addDebug(responseText);
            
            // Try to parse as JSON
            try {
                const data = JSON.parse(responseText);
                addDebug('Successfully parsed JSON:');
                addDebug(JSON.stringify(data, null, 2));
                
                if (data.success) {
                    addResult('✅ Vote submission successful!');
                } else {
                    addResult(`❌ Vote submission failed: ${data.message}`, true);
                }
            } catch (parseError) {
                addResult('❌ Response is not valid JSON', true);
                addDebug(`JSON parse error: ${parseError.message}`);
            }
            
        } catch (error) {
            addResult(`❌ Network error: ${error.message}`, true);
            addDebug(`Error details: ${error.stack}`);
        }
    }
    </script>
</body>
</html>
