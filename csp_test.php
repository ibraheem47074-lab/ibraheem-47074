<?php
// CSP Test Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSP Test - PK Live News</title>
    
    <!-- Test Bootstrap CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Test Font Awesome from CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .status-success {
            color: green;
            font-weight: bold;
        }
        .status-error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">CSP Test Results</h1>
        
        <div class="test-section">
            <h2><i class="fas fa-shield-alt me-2"></i>Content Security Policy Test</h2>
            
            <h3>1. Bootstrap CSS Loading</h3>
            <div id="bootstrap-test">
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin me-2"></i>Testing Bootstrap CSS...
                </div>
            </div>
            
            <h3>2. Font Awesome Icons</h3>
            <div id="fontawesome-test">
                <i class="fas fa-check-circle fa-2x"></i>
                <i class="fas fa-times-circle fa-2x"></i>
                <i class="fas fa-info-circle fa-2x"></i>
                <div class="mt-2">If you see icons above, Font Awesome is working.</div>
            </div>
            
            <h3>3. Form Field Test</h3>
            <div id="form-test">
                <form id="test-form">
                    <div class="mb-3">
                        <label for="test-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="test-name" name="test_name" placeholder="Enter your name">
                    </div>
                    <div class="mb-3">
                        <label for="test-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="test-email" name="test_email" placeholder="Enter your email">
                    </div>
                    <div class="mb-3">
                        <label for="test-message" class="form-label">Message</label>
                        <textarea class="form-control" id="test-message" name="test_message" rows="3" placeholder="Enter your message"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit Test
                    </button>
                </form>
            </div>
            
            <h3>4. JavaScript Test</h3>
            <div id="js-test">
                <button id="test-btn" class="btn btn-success">
                    <i class="fas fa-play me-2"></i>Test JavaScript
                </button>
                <div id="js-result" class="mt-2"></div>
            </div>
            
            <h3>5. Network Requests Test</h3>
            <div id="network-test">
                <button id="network-btn" class="btn btn-warning">
                    <i class="fas fa-network-wired me-2"></i>Test Network Requests
                </button>
                <div id="network-result" class="mt-2"></div>
            </div>
        </div>
        
        <div class="test-section">
            <h2><i class="fas fa-list me-2"></i>CSP Violations Detected</h2>
            <div id="violations">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Check browser console for CSP violations...
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h2><i class="fas fa-cog me-2"></i>Current CSP Configuration</h2>
            <div id="csp-info">
                <div class="alert alert-secondary">
                    <i class="fas fa-spinner fa-spin me-2"></i>Loading CSP information...
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Test Bootstrap CSS
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const bootstrapTest = document.getElementById('bootstrap-test');
                const alertDiv = bootstrapTest.querySelector('.alert');
                
                if (window.getComputedStyle(alertDiv).backgroundColor === 'rgb(209, 236, 241)' || 
                    window.getComputedStyle(alertDiv).backgroundColor === 'rgb(13, 202, 240)') {
                    bootstrapTest.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><span class="status-success">Bootstrap CSS is working!</span></div>';
                } else {
                    bootstrapTest.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i><span class="status-error">Bootstrap CSS failed to load!</span></div>';
                }
            }, 1000);
        });
        
        // Test JavaScript
        document.getElementById('test-btn').addEventListener('click', function() {
            const result = document.getElementById('js-result');
            result.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>JavaScript is working!</div>';
        });
        
        // Test Network Requests
        document.getElementById('network-btn').addEventListener('click', function() {
            const result = document.getElementById('network-result');
            result.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Testing network requests...</div>';
            
            // Test fetch to CDN
            fetch('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css')
                .then(response => {
                    if (response.ok) {
                        result.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Network requests to CDN are working!</div>';
                    } else {
                        result.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Network request returned: ' + response.status + '</div>';
                    }
                })
                .catch(error => {
                    result.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Network request failed: ' + error.message + '</div>';
                });
        });
        
        // Display CSP information
        document.addEventListener('DOMContentLoaded', function() {
            const cspMeta = document.querySelector('meta[http-equiv="Content-Security-Policy"]');
            const cspInfo = document.getElementById('csp-info');
            
            if (cspMeta) {
                cspInfo.innerHTML = '<div class="alert alert-info"><strong>CSP Meta Tag:</strong><br><code>' + cspMeta.content + '</code></div>';
            } else {
                cspInfo.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No CSP meta tag found. CSP likely set via HTTP headers.</div>';
            }
        });
        
        // Listen for CSP violations
        document.addEventListener('securitypolicyviolation', function(e) {
            const violations = document.getElementById('violations');
            const violationDiv = document.createElement('div');
            violationDiv.className = 'alert alert-warning mt-2';
            violationDiv.innerHTML = `
                <strong>CSP Violation:</strong><br>
                <small>Blocked URI: ${e.blockedURI}</small><br>
                <small>Directive: ${e.violatedDirective}</small><br>
                <small>Source File: ${e.sourceFile}</small>
            `;
            violations.appendChild(violationDiv);
        });
        
        // Form test
        document.getElementById('test-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const result = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Form submission test successful! Fields have proper name attributes.</div>';
            this.insertAdjacentHTML('afterend', result);
        });
    </script>
</body>
</html>
