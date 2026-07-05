<?php
require_once '../config/database.php';

// Check admin access
if (!is_admin()) {
    redirect('../login.php');
}

$page_title = 'Breaking News Alerts';
?>

<?php include 'includes/admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell me-2"></i>Breaking News Alerts</h2>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createAlertModal">
                    <i class="fas fa-plus me-2"></i>Create Alert
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="totalAlerts">0</h4>
                            <p class="mb-0">Total Alerts</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bell fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="completedAlerts">0</h4>
                            <p class="mb-0">Completed</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="totalDelivered">0</h4>
                            <p class="mb-0">Delivered</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="webSubscribers">0</h4>
                            <p class="mb-0">Web Subscribers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Recent Alerts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="alertsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Sent/Delivered</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Alerts will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Alert Modal -->
<div class="modal fade" id="createAlertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Breaking News Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createAlertForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newsSelect" class="form-label">Select News Article</label>
                        <select class="form-select" id="newsSelect" required>
                            <option value="">Choose a news article...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alertTitle" class="form-label">Alert Title</label>
                        <input type="text" class="form-control" id="alertTitle" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alertMessage" class="form-label">Alert Message</label>
                        <textarea class="form-control" id="alertMessage" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <label for="alertType" class="form-label">Alert Type</label>
                            <select class="form-select" id="alertType">
                                <option value="breaking">Breaking News</option>
                                <option value="urgent">Urgent</option>
                                <option value="update">Update</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="alertPriority" class="form-label">Priority</label>
                            <select class="form-select" id="alertPriority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="targetAudience" class="form-label">Target Audience</label>
                            <select class="form-select" id="targetAudience">
                                <option value="all" selected>All Users</option>
                                <option value="subscribers">Subscribers Only</option>
                                <option value="registered">Registered Users</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">Notification Channels</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendPush" checked>
                            <label class="form-check-label" for="sendPush">
                                Browser Push Notifications
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendMobile" checked>
                            <label class="form-check-label" for="sendMobile">
                                Mobile App Notifications
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendEmail">
                            <label class="form-check-label" for="sendEmail">
                                Email Notifications
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <label for="scheduleTime" class="form-label">Schedule (Optional)</label>
                        <input type="datetime-local" class="form-control" id="scheduleTime">
                        <small class="text-muted">Leave empty to send immediately</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-paper-plane me-2"></i>Create Alert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alert Details Modal -->
<div class="modal fade" id="alertDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alert Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="alertDetailsContent">
                <!-- Alert details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
.alert-status-pending { color: #ffc107; }
.alert-status-sending { color: #0dcaf0; }
.alert-status-completed { color: #198754; }
.alert-status-failed { color: #dc3545; }
.alert-status-cancelled { color: #6c757d; }

.priority-low { background-color: #d1ecf1; }
.priority-medium { background-color: #fff3cd; }
.priority-high { background-color: #f8d7da; }
.priority-critical { background-color: #ff6b6b; color: white; }
</style>

<script>
// Load statistics
async function loadStats() {
    try {
        const response = await fetch('../api/breaking-news-alerts.php?action=stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalAlerts').textContent = data.stats.total_alerts;
            document.getElementById('completedAlerts').textContent = data.stats.completed;
            document.getElementById('totalDelivered').textContent = data.stats.total_delivered;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
    
    // Load web subscribers count
    try {
        const response = await fetch('../api/mobile-notifications.php?action=stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('webSubscribers').textContent = data.stats.active_devices || 0;
        }
    } catch (error) {
        console.error('Error loading mobile stats:', error);
    }
}

// Load recent alerts
async function loadAlerts() {
    try {
        const response = await fetch('../api/breaking-news-alerts.php?action=recent');
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.querySelector('#alertsTable tbody');
            tbody.innerHTML = '';
            
            data.alerts.forEach(alert => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${alert.id}</td>
                    <td>
                        <strong>${alert.title}</strong>
                        ${alert.news_title ? `<br><small class="text-muted">${alert.news_title}</small>` : ''}
                    </td>
                    <td>${alert.message.substring(0, 50)}${alert.message.length > 50 ? '...' : ''}</td>
                    <td><span class="badge bg-info">${alert.alert_type}</span></td>
                    <td><span class="badge priority-${alert.priority}">${alert.priority}</span></td>
                    <td><span class="alert-status-${alert.status}">${alert.status}</span></td>
                    <td>${alert.total_sent || 0}/${alert.total_delivered || 0}</td>
                    <td>${new Date(alert.created_at).toLocaleString()}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewAlertDetails(${alert.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${alert.status === 'pending' ? `
                            <button class="btn btn-sm btn-outline-danger" onclick="cancelAlert(${alert.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Error loading alerts:', error);
    }
}

// Load news articles for selection
async function loadNewsArticles() {
    try {
        const response = await fetch('../api/news.php?action=list&status=published&limit=50');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('newsSelect');
            data.news.forEach(article => {
                const option = document.createElement('option');
                option.value = article.id;
                option.textContent = article.title;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading news articles:', error);
    }
}

// Create alert
document.getElementById('createAlertForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        news_id: document.getElementById('newsSelect').value,
        title: document.getElementById('alertTitle').value,
        message: document.getElementById('alertMessage').value,
        alert_type: document.getElementById('alertType').value,
        priority: document.getElementById('alertPriority').value,
        target_audience: document.getElementById('targetAudience').value,
        send_push: document.getElementById('sendPush').checked,
        send_mobile: document.getElementById('sendMobile').checked,
        send_email: document.getElementById('sendEmail').checked,
        scheduled_at: document.getElementById('scheduleTime').value || null
    };
    
    try {
        const response = await fetch('../api/breaking-news-alerts.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('createAlertModal')).hide();
            document.getElementById('createAlertForm').reset();
            showAlert('Alert created successfully!', 'success');
            loadAlerts();
            loadStats();
        } else {
            showAlert('Error creating alert: ' + result.error, 'danger');
        }
    } catch (error) {
        console.error('Error creating alert:', error);
        showAlert('Error creating alert', 'danger');
    }
});

// View alert details
async function viewAlertDetails(alertId) {
    try {
        const response = await fetch(`../api/breaking-news-alerts.php?action=details&id=${alertId}`);
        const data = await response.json();
        
        if (data.success) {
            const alert = data.alert;
            const content = document.getElementById('alertDetailsContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Alert Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>ID:</strong></td><td>${alert.id}</td></tr>
                            <tr><td><strong>Title:</strong></td><td>${alert.title}</td></tr>
                            <tr><td><strong>Message:</strong></td><td>${alert.message}</td></tr>
                            <tr><td><strong>Type:</strong></td><td>${alert.alert_type}</td></tr>
                            <tr><td><strong>Priority:</strong></td><td>${alert.priority}</td></tr>
                            <tr><td><strong>Status:</strong></td><td>${alert.status}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Delivery Statistics</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Total Sent:</strong></td><td>${alert.total_sent || 0}</td></tr>
                            <tr><td><strong>Delivered:</strong></td><td>${alert.total_delivered || 0}</td></tr>
                            <tr><td><strong>Failed:</strong></td><td>${alert.total_failed || 0}</td></tr>
                            <tr><td><strong>Delivery Rate:</strong></td><td>${alert.total_sent > 0 ? Math.round((alert.total_delivered / alert.total_sent) * 100) : 0}%</td></tr>
                            <tr><td><strong>Created:</strong></td><td>${new Date(alert.created_at).toLocaleString()}</td></tr>
                            <tr><td><strong>Sent:</strong></td><td>${alert.sent_at ? new Date(alert.sent_at).toLocaleString() : 'Not sent'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('alertDetailsModal')).show();
        }
    } catch (error) {
        console.error('Error loading alert details:', error);
    }
}

// Cancel alert
async function cancelAlert(alertId) {
    if (!confirm('Are you sure you want to cancel this alert?')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/breaking-news-alerts.php?action=cancel&id=${alertId}`, {
            method: 'POST'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Alert cancelled successfully', 'success');
            loadAlerts();
        } else {
            showAlert('Error cancelling alert: ' + result.error, 'danger');
        }
    } catch (error) {
        console.error('Error cancelling alert:', error);
        showAlert('Error cancelling alert', 'danger');
    }
}

// Show alert message
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 70px; right: 20px; z-index: 9999;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

// Auto-refresh data
function autoRefresh() {
    loadStats();
    loadAlerts();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadAlerts();
    loadNewsArticles();
    
    // Auto-refresh every 30 seconds
    setInterval(autoRefresh, 30000);
});
</script>

<?php include 'includes/admin-footer.php'; ?>
