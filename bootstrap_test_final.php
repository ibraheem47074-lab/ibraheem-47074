<?php
// Final Bootstrap Test with CSP-Safe Header
require_once 'includes/header-csp-safe.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Bootstrap CSP Test - Final Verification</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-check-circle me-2"></i>Bootstrap Components Test</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-primary me-2">Primary Button</button>
                        <button class="btn btn-secondary me-2">Secondary Button</button>
                        <button class="btn btn-success me-2">Success Button</button>
                        <button class="btn btn-outline-primary">Outline Primary</button>
                    </div>
                    
                    <div class="mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Alert Test:</strong> If you see proper styling, Bootstrap is working.
                        </div>
                        <div class="alert alert-success">
                            <i class="fas fa-check me-2"></i>
                            <strong>Success Alert:</strong> Bootstrap CSS is loaded!
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning Alert:</strong> CSP configuration check.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Form Input Test:</label>
                        <input type="text" class="form-control" placeholder="Test input field" name="test_input">
                    </div>
                    
                    <div class="progress mb-3">
                        <div class="progress-bar" style="width: 75%">75%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-cogs me-2"></i>Loading Status</h3>
                </div>
                <div class="card-body">
                    <div id="loading-status">
                        <div class="alert alert-info">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Testing Bootstrap loading...
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button id="test-modal" class="btn btn-primary">
                            <i class="fas fa-window-restore me-2"></i>Test Modal
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <button id="test-csp" class="btn btn-warning">
                            <i class="fas fa-shield-alt me-2"></i>Check CSP Status
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3><i class="fas fa-bug me-2"></i>Debug Information</h3>
                </div>
                <div class="card-body">
                    <div id="debug-info">
                        <p><strong>CSS Sources:</strong></p>
                        <ul id="css-sources">
                            <li>Checking...</li>
                        </ul>
                        
                        <p><strong>CSP Violations:</strong></p>
                        <div id="csp-violations">
                            <p>Monitoring...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bootstrap Modal Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><i class="fas fa-check-circle text-success me-2"></i>If you see this modal, Bootstrap is working properly!</p>
                <p>The modal should have proper styling and functionality.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let violationCount = 0;
    let bootstrapLoaded = false;
    
    // Monitor CSP violations
    document.addEventListener('securitypolicyviolation', function(e) {
        violationCount++;
        const violationsDiv = document.getElementById('csp-violations');
        violationsDiv.innerHTML = `
            <div class="alert alert-warning">
                <strong>CSP Violation Detected:</strong><br>
                <small>Blocked: ${e.blockedURI}</small><br>
                <small>Directive: ${e.violatedDirective}</small><br>
                <small>File: ${e.sourceFile}</small>
            </div>
        `;
    });
    
    // Test Bootstrap loading
    function testBootstrapLoading() {
        const statusDiv = document.getElementById('loading-status');
        const sourcesList = document.getElementById('css-sources');
        
        // Check CSS sources
        const links = document.querySelectorAll('link[rel="stylesheet"]');
        let sourcesHtml = '';
        
        links.forEach((link, index) => {
            const href = link.href;
            const isLocal = href.includes('bootstrap-local.css');
            const isCDN = href.includes('cdn.jsdelivr.net');
            
            sourcesHtml += `<li>
                <strong>Source ${index + 1}:</strong> ${href}<br>
                <small>Type: ${isLocal ? 'Local' : isCDN ? 'CDN' : 'Other'}</small><br>
                <small>Status: ${link.disabled ? 'Disabled' : 'Active'}</small>
            </li>`;
        });
        
        sourcesList.innerHTML = sourcesHtml;
        
        // Test Bootstrap functionality
        setTimeout(function() {
            const testButton = document.querySelector('.btn-primary');
            const testAlert = document.querySelector('.alert-info');
            const testInput = document.querySelector('.form-control');
            
            let testsPassed = 0;
            let totalTests = 3;
            
            // Test button styling
            if (testButton) {
                const buttonStyles = window.getComputedStyle(testButton);
                if (buttonStyles.backgroundColor && buttonStyles.backgroundColor !== 'rgba(0, 0, 0, 0)') {
                    testsPassed++;
                }
            }
            
            // Test alert styling
            if (testAlert) {
                const alertStyles = window.getComputedStyle(testAlert);
                if (alertStyles.backgroundColor && alertStyles.backgroundColor !== 'rgba(0, 0, 0, 0)') {
                    testsPassed++;
                }
            }
            
            // Test input styling
            if (testInput) {
                const inputStyles = window.getComputedStyle(testInput);
                if (inputStyles.borderRadius && inputStyles.borderRadius !== '0px') {
                    testsPassed++;
                }
            }
            
            bootstrapLoaded = testsPassed === totalTests;
            
            let statusHtml = '';
            if (bootstrapLoaded) {
                statusHtml = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Bootstrap CSS: LOADED SUCCESSFULLY</strong><br>
                        <small>Tests passed: ${testsPassed}/${totalTests}</small>
                    </div>
                `;
            } else {
                statusHtml = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Bootstrap CSS: FAILED TO LOAD</strong><br>
                        <small>Tests passed: ${testsPassed}/${totalTests}</small>
                    </div>
                `;
            }
            
            if (violationCount > 0) {
                statusHtml += `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>CSP Violations:</strong> ${violationCount} detected
                    </div>
                `;
            } else {
                statusHtml += `
                    <div class="alert alert-success">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>No CSP violations detected</strong>
                    </div>
                `;
            }
            
            statusDiv.innerHTML = statusHtml;
        }, 2000);
    }
    
    // Test modal functionality
    document.getElementById('test-modal').addEventListener('click', function() {
        try {
            // Try to create and show modal
            const modal = new bootstrap.Modal(document.getElementById('testModal'));
            modal.show();
            
            // Update status
            const statusDiv = document.getElementById('loading-status');
            const modalStatus = document.createElement('div');
            modalStatus.className = 'alert alert-success mt-2';
            modalStatus.innerHTML = '<i class="fas fa-check me-2"></i>Bootstrap Modal: Working!';
            statusDiv.appendChild(modalStatus);
        } catch (e) {
            // Fallback if bootstrap.Modal is not available
            const modal = document.getElementById('testModal');
            modal.style.display = 'block';
            modal.classList.add('show');
            
            const statusDiv = document.getElementById('loading-status');
            const modalStatus = document.createElement('div');
            modalStatus.className = 'alert alert-warning mt-2';
            modalStatus.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Bootstrap Modal: Manual fallback used';
            statusDiv.appendChild(modalStatus);
        }
    });
    
    // Check CSP status
    document.getElementById('test-csp').addEventListener('click', function() {
        const debugDiv = document.getElementById('debug-info');
        
        let cspInfo = '<div class="alert alert-info"><strong>CSP Analysis:</strong><br>';
        
        // Check for CSP meta tag
        const cspMeta = document.querySelector('meta[http-equiv="Content-Security-Policy"]');
        if (cspMeta) {
            cspInfo += 'CSP Meta Tag: Found<br>';
        } else {
            cspInfo += 'CSP Meta Tag: Not found (likely HTTP headers)<br>';
        }
        
        cspInfo += `CSP Violations: ${violationCount}<br>`;
        cspInfo += `Bootstrap Loaded: ${bootstrapLoaded ? 'Yes' : 'No'}<br>`;
        cspInfo += '</div>';
        
        debugDiv.insertAdjacentHTML('beforeend', cspInfo);
    });
    
    // Run initial test
    testBootstrapLoading();
});
</script>

<?php require_once 'includes/footer.php'; ?>
