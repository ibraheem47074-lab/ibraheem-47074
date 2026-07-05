<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK Live News - Connection Test Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .controls {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            margin-right: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .results {
            padding: 30px;
            display: none;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card.success {
            border-left-color: #28a745;
        }

        .card.warning {
            border-left-color: #ffc107;
        }

        .card.danger {
            border-left-color: #dc3545;
        }

        .card h3 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .card p {
            color: #6c757d;
            font-size: 1.1em;
        }

        .test-results {
            margin-top: 30px;
        }

        .test-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }

        .test-item.success {
            border-left-color: #28a745;
        }

        .test-item.warning {
            border-left-color: #ffc107;
        }

        .test-item.danger {
            border-left-color: #dc3545;
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
        }

        .test-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .test-status.passed {
            background: #d4edda;
            color: #155724;
        }

        .test-status.warning {
            background: #fff3cd;
            color: #856404;
        }

        .test-status.failed {
            background: #f8d7da;
            color: #721c24;
        }

        .test-message {
            color: #6c757d;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .test-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .test-details h4 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
        }

        .detail-value {
            color: #6c757d;
        }

        .server-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .server-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }

        .timestamp {
            text-align: center;
            color: #6c757d;
            margin-top: 20px;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 2em;
            }

            .controls {
                padding: 20px;
            }

            .results {
                padding: 20px;
            }

            .summary-cards {
                grid-template-columns: 1fr;
            }

            .test-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PK Live News</h1>
            <p>Comprehensive Connection Test Dashboard</p>
        </div>

        <div class="controls">
            <button class="btn" onclick="runTests()">Run All Tests</button>
            <button class="btn btn-secondary" onclick="runDatabaseTest()">Database Only</button>
            <button class="btn btn-secondary" onclick="runFileSystemTest()">File System Only</button>
            <button class="btn btn-danger" onclick="clearResults()">Clear Results</button>
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <h3>Running Connection Tests...</h3>
            <p>Please wait while we check all system connections and configurations.</p>
        </div>

        <div class="results" id="results">
            <!-- Results will be populated here -->
        </div>
    </div>

    <script>
        let currentResults = null;

        function showLoading() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('results').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('results').style.display = 'block';
        }

        function clearResults() {
            document.getElementById('results').style.display = 'none';
            currentResults = null;
        }

        async function runTests() {
            showLoading();
            
            try {
                const response = await fetch('connection_test_system.php');
                const data = await response.json();
                
                currentResults = data;
                displayResults(data);
                hideLoading();
            } catch (error) {
                hideLoading();
                showError('Failed to run tests: ' + error.message);
            }
        }

        async function runDatabaseTest() {
            showLoading();
            
            try {
                const response = await fetch('connection_test_system.php?category=database');
                const data = await response.json();
                
                currentResults = data;
                displayResults(data);
                hideLoading();
            } catch (error) {
                hideLoading();
                showError('Failed to run database tests: ' + error.message);
            }
        }

        async function runFileSystemTest() {
            showLoading();
            
            try {
                const response = await fetch('connection_test_system.php?category=filesystem');
                const data = await response.json();
                
                currentResults = data;
                displayResults(data);
                hideLoading();
            } catch (error) {
                hideLoading();
                showError('Failed to run file system tests: ' + error.message);
            }
        }

        function showError(message) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="error-message">
                    <strong>Error:</strong> ${message}
                </div>
            `;
            resultsDiv.style.display = 'block';
        }

        function displayResults(data) {
            const resultsDiv = document.getElementById('results');
            
            let html = `
                <div class="server-info">
                    <h3>Server Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="detail-label">PHP Version</span>
                            <span class="detail-value">${data.server_info.php_version}</span>
                        </div>
                        <div class="info-item">
                            <span class="detail-label">Server</span>
                            <span class="detail-value">${data.server_info.server_software}</span>
                        </div>
                        <div class="info-item">
                            <span class="detail-label">Memory Limit</span>
                            <span class="detail-value">${data.server_info.memory_limit}</span>
                        </div>
                        <div class="info-item">
                            <span class="detail-label">Max Execution</span>
                            <span class="detail-value">${data.server_info.max_execution_time}s</span>
                        </div>
                    </div>
                </div>

                <div class="summary-cards">
                    <div class="card ${getCardClass(data.overall_status)}">
                        <h3>${data.summary.total_tests}</h3>
                        <p>Total Tests</p>
                    </div>
                    <div class="card success">
                        <h3>${data.summary.passed_tests}</h3>
                        <p>Passed</p>
                    </div>
                    <div class="card warning">
                        <h3>${data.summary.warnings}</h3>
                        <p>Warnings</p>
                    </div>
                    <div class="card danger">
                        <h3>${data.summary.failed_tests}</h3>
                        <p>Failed</p>
                    </div>
                </div>

                <div class="test-results">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">Test Results</h3>
            `;

            data.tests.forEach(test => {
                html += `
                    <div class="test-item ${getCardClass(test.status)}">
                        <div class="test-header">
                            <div class="test-title">${test.name}</div>
                            <div class="test-status ${test.status}">${test.status}</div>
                        </div>
                        <div class="test-message">${test.message}</div>
                        ${test.details ? `
                            <div class="test-details">
                                <h4>Details</h4>
                                ${formatDetails(test.details)}
                            </div>
                        ` : ''}
                    </div>
                `;
            });

            html += `
                </div>
                <div class="timestamp">
                    Last updated: ${data.timestamp}
                </div>
            `;

            resultsDiv.innerHTML = html;
        }

        function getCardClass(status) {
            switch(status) {
                case 'passed': return 'success';
                case 'warning': return 'warning';
                case 'failed': return 'danger';
                default: return '';
            }
        }

        function formatDetails(details) {
            let html = '';
            
            if (typeof details === 'object') {
                for (const [key, value] of Object.entries(details)) {
                    if (typeof value === 'object') {
                        html += `
                            <div class="detail-item">
                                <span class="detail-label">${key}</span>
                                <span class="detail-value">${JSON.stringify(value)}</span>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="detail-item">
                                <span class="detail-label">${key}</span>
                                <span class="detail-value">${value}</span>
                            </div>
                        `;
                    }
                }
            } else {
                html = `<div class="detail-item">${details}</div>`;
            }
            
            return html;
        }

        // Auto-run tests on page load
        window.addEventListener('load', function() {
            setTimeout(runTests, 500);
        });
    </script>
</body>
</html>
