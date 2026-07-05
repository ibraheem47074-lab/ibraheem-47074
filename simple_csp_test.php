<?php
// Simple CSP Test
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple CSP Test</title>
    
    <!-- Try multiple Bootstrap sources -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-cdn">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-cdn2">
    
    <style>
        .test-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container" style="padding: 20px;">
        <h1>CSP Bootstrap Loading Test</h1>
        
        <div id="test-results">
            <div class="test-result warning">
                <strong>Testing Bootstrap CSS loading...</strong>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Bootstrap Components Test</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-primary">Primary Button</button>
                        <button class="btn btn-secondary">Secondary Button</button>
                        <button class="btn btn-success">Success Button</button>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <strong>Alert Test:</strong> If you see proper styling, Bootstrap is working.
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: 75%">75%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h3>Manual Bootstrap Test</h3>
            </div>
            <div class="card-body">
                <button id="test-manual" class="btn btn-outline-primary">Test Bootstrap Manually</button>
                <div id="manual-result" style="margin-top: 10px;"></div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h3>CSP Information</h3>
            </div>
            <div class="card-body">
                <div id="csp-info">
                    <p>Checking CSP configuration...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Test Bootstrap loading
        function testBootstrap() {
            const results = document.getElementById('test-results');
            let html = '';
            
            // Test 1: Check if Bootstrap CSS loaded from jsdelivr
            const bootstrapLink = document.getElementById('bootstrap-cdn');
            const bootstrapSheet = bootstrapLink ? bootstrapLink.sheet : null;
            
            if (bootstrapSheet) {
                try {
                    const rules = bootstrapSheet.cssRules || bootstrapSheet.rules;
                    if (rules && rules.length > 0) {
                        html += '<div class="test-result success">Bootstrap CSS (jsdelivr): LOADED - ' + rules.length + ' rules found</div>';
                    } else {
                        html += '<div class="test-result error">Bootstrap CSS (jsdelivr): FAILED - No rules found</div>';
                    }
                } catch (e) {
                    html += '<div class="test-result error">Bootstrap CSS (jsdelivr): FAILED - ' + e.message + '</div>';
                }
            } else {
                html += '<div class="test-result error">Bootstrap CSS (jsdelivr): FAILED - Not accessible</div>';
            }
            
            // Test 2: Check if Bootstrap CSS loaded from cdnjs
            const bootstrapLink2 = document.getElementById('bootstrap-cdn2');
            const bootstrapSheet2 = bootstrapLink2 ? bootstrapLink2.sheet : null;
            
            if (bootstrapSheet2) {
                try {
                    const rules2 = bootstrapSheet2.cssRules || bootstrapSheet2.rules;
                    if (rules2 && rules2.length > 0) {
                        html += '<div class="test-result success">Bootstrap CSS (cdnjs): LOADED - ' + rules2.length + ' rules found</div>';
                    } else {
                        html += '<div class="test-result error">Bootstrap CSS (cdnjs): FAILED - No rules found</div>';
                    }
                } catch (e) {
                    html += '<div class="test-result error">Bootstrap CSS (cdnjs): FAILED - ' + e.message + '</div>';
                }
            } else {
                html += '<div class="test-result error">Bootstrap CSS (cdnjs): FAILED - Not accessible</div>';
            }
            
            // Test 3: Check if Bootstrap classes work
            const testButton = document.querySelector('.btn-primary');
            if (testButton) {
                const styles = window.getComputedStyle(testButton);
                if (styles.backgroundColor && styles.backgroundColor !== 'rgba(0, 0, 0, 0)' && styles.backgroundColor !== 'transparent') {
                    html += '<div class="test-result success">Bootstrap Classes: WORKING - Button has proper styling</div>';
                } else {
                    html += '<div class="test-result error">Bootstrap Classes: FAILED - Button not styled</div>';
                }
            }
            
            // Test 4: Check for CSP violations
            if (window.cspViolationLogger) {
                html += '<div class="test-result warning">CSP violations detected</div>';
            } else {
                html += '<div class="test-result success">No CSP violations detected (so far)</div>';
            }
            
            results.innerHTML = html;
        }
        
        // Manual test
        document.getElementById('test-manual').addEventListener('click', function() {
            const result = document.getElementById('manual-result');
            
            // Try to create a Bootstrap modal
            try {
                const modalHtml = `
                    <div class="modal fade" id="testModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Test Modal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>If you see this modal, Bootstrap JavaScript is working!</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Try to use Bootstrap Modal
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(document.getElementById('testModal'));
                    modal.show();
                    result.innerHTML = '<div class="test-result success">Bootstrap Modal: WORKING</div>';
                } else {
                    result.innerHTML = '<div class="test-result warning">Bootstrap Modal: JavaScript not loaded (but CSS might be working)</div>';
                }
            } catch (e) {
                result.innerHTML = '<div class="test-result error">Bootstrap Modal: FAILED - ' + e.message + '</div>';
            }
        });
        
        // Check CSP
        function checkCSP() {
            const cspInfo = document.getElementById('csp-info');
            let html = '';
            
            // Check for CSP meta tag
            const cspMeta = document.querySelector('meta[http-equiv="Content-Security-Policy"]');
            if (cspMeta) {
                html += '<p><strong>CSP Meta Tag:</strong></p><code>' + cspMeta.content + '</code>';
            } else {
                html += '<p>No CSP meta tag found. CSP likely set via HTTP headers.</p>';
            }
            
            // Try to detect CSP violations
            let violationCount = 0;
            document.addEventListener('securitypolicyviolation', function(e) {
                violationCount++;
            });
            
            setTimeout(function() {
                if (violationCount > 0) {
                    html += '<p class="text-danger"><strong>CSP Violations:</strong> ' + violationCount + ' detected</p>';
                } else {
                    html += '<p class="text-success"><strong>CSP Violations:</strong> None detected</p>';
                }
                cspInfo.innerHTML = html;
            }, 2000);
        }
        
        // Run tests when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(testBootstrap, 1000);
            checkCSP();
        });
        
        // Log CSP violations
        document.addEventListener('securitypolicyviolation', function(e) {
            console.error('CSP Violation:', {
                blockedURI: e.blockedURI,
                violatedDirective: e.violatedDirective,
                sourceFile: e.sourceFile,
                lineNumber: e.lineNumber
            });
            window.cspViolationLogger = true;
        });
    </script>
</body>
</html>
