<?php
// Form Field Verification Test
require_once 'includes/header-csp-safe.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Form Field Attribute Verification</h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-check-circle me-2"></i>Form Elements Test</h3>
                </div>
                <div class="card-body">
                    <form id="test-form">
                        <div class="mb-3">
                            <label for="test-text" class="form-label">Text Input</label>
                            <input type="text" class="form-control" id="test-text" name="test_text" placeholder="Test text input">
                        </div>
                        
                        <div class="mb-3">
                            <label for="test-email" class="form-label">Email Input</label>
                            <input type="email" class="form-control" id="test-email" name="test_email" placeholder="Test email input">
                        </div>
                        
                        <div class="mb-3">
                            <label for="test-password" class="form-label">Password Input</label>
                            <input type="password" class="form-control" id="test-password" name="test_password" placeholder="Test password input">
                        </div>
                        
                        <div class="mb-3">
                            <label for="test-select" class="form-label">Select Input</label>
                            <select class="form-select" id="test-select" name="test_select">
                                <option value="">Choose an option</option>
                                <option value="1">Option 1</option>
                                <option value="2">Option 2</option>
                                <option value="3">Option 3</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="test-textarea" class="form-label">Textarea</label>
                            <textarea class="form-control" id="test-textarea" name="test_textarea" rows="3" placeholder="Test textarea"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="test-checkbox1" name="test_checkbox1" value="option1">
                                <label class="form-check-label" for="test-checkbox1">
                                    Checkbox Option 1
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="test-checkbox2" name="test_checkbox2" value="option2">
                                <label class="form-check-label" for="test-checkbox2">
                                    Checkbox Option 2
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="test-radio1" name="test_radio" value="radio1">
                                <label class="form-check-label" for="test-radio1">
                                    Radio Option 1
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="test-radio2" name="test_radio" value="radio2">
                                <label class="form-check-label" for="test-radio2">
                                    Radio Option 2
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="test-file" class="form-label">File Input</label>
                            <input type="file" class="form-control" id="test-file" name="test_file">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Test Form Submission
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-cogs me-2"></i>Verification Results</h3>
                </div>
                <div class="card-body">
                    <div id="verification-results">
                        <div class="alert alert-info">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Running verification...
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button id="run-verification" class="btn btn-outline-primary">
                            <i class="fas fa-sync me-2"></i>Run Verification
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle me-2"></i>Form Statistics</h3>
                </div>
                <div class="card-body">
                    <div id="form-stats">
                        <p><strong>Total Form Elements:</strong> <span id="total-elements">0</span></p>
                        <p><strong>Elements with ID:</strong> <span id="elements-with-id">0</span></p>
                        <p><strong>Elements with Name:</strong> <span id="elements-with-name">0</span></p>
                        <p><strong>Elements with Both:</strong> <span id="elements-with-both">0</span></p>
                        <p><strong>Elements Missing Attributes:</strong> <span id="elements-missing">0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list me-2"></i>Detailed Element Analysis</h3>
                </div>
                <div class="card-body">
                    <div id="detailed-analysis">
                        <p>Analyzing form elements...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function runVerification() {
        const resultsDiv = document.getElementById('verification-results');
        const statsDiv = document.getElementById('form-stats');
        const analysisDiv = document.getElementById('detailed-analysis');
        
        // Get all form elements
        const formElements = document.querySelectorAll('#test-form input, #test-form select, #test-form textarea');
        
        let totalElements = formElements.length;
        let elementsWithId = 0;
        let elementsWithName = 0;
        let elementsWithBoth = 0;
        let elementsMissing = 0;
        let issues = [];
        let analysisHtml = '';
        
        // Analyze each element
        formElements.forEach((element, index) => {
            const hasId = element.hasAttribute('id');
            const hasName = element.hasAttribute('name');
            const type = element.type || element.tagName.toLowerCase();
            const id = element.id || 'N/A';
            const name = element.name || 'N/A';
            
            if (hasId) elementsWithId++;
            if (hasName) elementsWithName++;
            if (hasId && hasName) elementsWithBoth++;
            if (!hasId && !hasName) elementsMissing++;
            
            // Create analysis entry
            analysisHtml += `
                <div class="row mb-2">
                    <div class="col-md-3">
                        <strong>Element ${index + 1}:</strong> ${type}
                    </div>
                    <div class="col-md-3">
                        ID: <span class="${hasId ? 'text-success' : 'text-danger'}">${id}</span>
                    </div>
                    <div class="col-md-3">
                        Name: <span class="${hasName ? 'text-success' : 'text-danger'}">${name}</span>
                    </div>
                    <div class="col-md-3">
                        Status: <span class="${hasId && hasName ? 'text-success' : 'text-warning'}">${hasId && hasName ? 'OK' : 'Missing attributes'}</span>
                    </div>
                </div>
            `;
            
            if (!hasId || !hasName) {
                issues.push({
                    type: type,
                    id: id,
                    name: name,
                    missingId: !hasId,
                    missingName: !hasName
                });
            }
        });
        
        // Update statistics
        document.getElementById('total-elements').textContent = totalElements;
        document.getElementById('elements-with-id').textContent = elementsWithId;
        document.getElementById('elements-with-name').textContent = elementsWithName;
        document.getElementById('elements-with-both').textContent = elementsWithBoth;
        document.getElementById('elements-missing').textContent = elementsMissing;
        
        // Update detailed analysis
        analysisDiv.innerHTML = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Element</th>
                            <th>Type</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${formElements.map((element, index) => {
                            const hasId = element.hasAttribute('id');
                            const hasName = element.hasAttribute('name');
                            const type = element.type || element.tagName.toLowerCase();
                            const id = element.id || 'N/A';
                            const name = element.name || 'N/A';
                            const status = hasId && hasName ? 'success' : 'warning';
                            
                            return `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${type}</td>
                                    <td><span class="text-${hasId ? 'success' : 'danger'}">${id}</span></td>
                                    <td><span class="text-${hasName ? 'success' : 'danger'}">${name}</span></td>
                                    <td><span class="badge bg-${status}">${hasId && hasName ? 'OK' : 'Missing'}</span></td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        // Update verification results
        let resultsHtml = '';
        if (issues.length === 0) {
            resultsHtml = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>All form elements are properly configured!</strong><br>
                    <small>All ${totalElements} elements have both ID and name attributes.</small>
                </div>
            `;
        } else {
            resultsHtml = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>${issues.length} form elements have missing attributes</strong><br>
                    <small>Please check the detailed analysis below.</small>
                </div>
            `;
            
            issues.forEach(issue => {
                const missingAttrs = [];
                if (issue.missingId) missingAttrs.push('ID');
                if (issue.missingName) missingAttrs.push('Name');
                
                resultsHtml += `
                    <div class="alert alert-danger mt-2">
                        <strong>${issue.type} element</strong> is missing: ${missingAttrs.join(', ')}<br>
                        <small>ID: ${issue.id}, Name: ${issue.name}</small>
                    </div>
                `;
            });
        }
        
        resultsDiv.innerHTML = resultsHtml;
    }
    
    // Run verification on page load
    runVerification();
    
    // Run verification when button is clicked
    document.getElementById('run-verification').addEventListener('click', runVerification);
    
    // Test form submission
    document.getElementById('test-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        const resultsDiv = document.getElementById('verification-results');
        const submissionResult = document.createElement('div');
        submissionResult.className = 'alert alert-success mt-2';
        submissionResult.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>Form submission test successful!</strong><br>
            <small>Form data collected: ${JSON.stringify(data, null, 2)}</small>
        `;
        
        resultsDiv.appendChild(submissionResult);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
