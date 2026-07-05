<?php
require_once 'config/database.php';

echo "<h2>Business Outreach Manager</h2>";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_business') {
        $name = mysqli_real_escape_string($conn, $_POST['business_name']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact_person']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $type = mysqli_real_escape_string($conn, $_POST['business_type']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        
        $insert_query = "INSERT INTO business_outreach (business_name, contact_person, email, phone, business_type, notes) 
                         VALUES ('$name', '$contact', '$email', '$phone', '$type', '$notes')";
        
        if (mysqli_query($conn, $insert_query)) {
            echo "<div class='alert alert-success'>✅ Business added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error adding business: " . mysqli_error($conn) . "</div>";
        }
    }
    
    if ($action === 'update_status') {
        $id = (int)$_POST['business_id'];
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $follow_up = $_POST['follow_up_date'] ?? null;
        
        $update_query = "UPDATE business_outreach 
                         SET status = '$status', 
                             follow_up_date = " . ($follow_up ? "'$follow_up'" : "NULL") . ",
                             last_contact_date = CURDATE()
                         WHERE id = $id";
        
        if (mysqli_query($conn, $update_query)) {
            echo "<div class='alert alert-success'>✅ Status updated successfully!</div>";
        }
    }
}

// Add new business form
echo "<div class='card mb-4'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h5>➕ Add New Business</h5>";
echo "</div>";
echo "<div class='card-body'>";
echo "<form method='POST' class='row g-3'>";
echo "<input type='hidden' name='action' value='add_business'>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Business Name *</label>";
echo "<input type='text' class='form-control' name='business_name' required>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Contact Person</label>";
echo "<input type='text' class='form-control' name='contact_person'>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Email *</label>";
echo "<input type='email' class='form-control' name='email' required>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Phone</label>";
echo "<input type='tel' class='form-control' name='phone'>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Business Type</label>";
echo "<select class='form-select' name='business_type'>";
echo "<option value=''>Select type</option>";
echo "<option value='Restaurant'>Restaurant</option>";
echo "<option value='Retail'>Retail</option>";
echo "<option value='Services'>Services</option>";
echo "<option value='Technology'>Technology</option>";
echo "<option value='Healthcare'>Healthcare</option>";
echo "<option value='Education'>Education</option>";
echo "<option value='Real Estate'>Real Estate</option>";
echo "<option value='Automotive'>Automotive</option>";
echo "<option value='Fashion'>Fashion</option>";
echo "</select>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Follow-up Date</label>";
echo "<input type='date' class='form-control' name='follow_up_date'>";
echo "</div>";
echo "<div class='col-12'>";
echo "<label class='form-label'>Notes</label>";
echo "<textarea class='form-control' name='notes' rows='3'></textarea>";
echo "</div>";
echo "<div class='col-12'>";
echo "<button type='submit' class='btn btn-primary mt-2'>";
echo "<i class='fas fa-plus me-2'></i>Add Business</button>";
echo "</div>";
echo "</form>";
echo "</div>";
echo "</div>";

// Business list with status management
echo "<div class='card'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h5>📋 Business Outreach List</h5>";
echo "</div>";
echo "<div class='card-body'>";

$businesses_query = "SELECT * FROM business_outreach ORDER BY created_at DESC";
$businesses_result = mysqli_query($conn, $businesses_query);

if (mysqli_num_rows($businesses_result) > 0) {
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Business</th>";
    echo "<th>Contact</th>";
    echo "<th>Type</th>";
    echo "<th>Status</th>";
    echo "<th>Last Contact</th>";
    echo "<th>Follow-up</th>";
    echo "<th>Actions</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    while ($business = mysqli_fetch_assoc($businesses_result)) {
        $status_color = [
            'prospect' => 'secondary',
            'contacted' => 'info',
            'interested' => 'primary',
            'negotiating' => 'warning',
            'closed' => 'success',
            'not_interested' => 'danger'
        ];
        
        $color = $status_color[$business['status']] ?? 'secondary';
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($business['business_name']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($business['contact_person']) . "<br><small>" . htmlspecialchars($business['email']) . "</small></td>";
        echo "<td>" . htmlspecialchars($business['business_type']) . "</td>";
        echo "<td><span class='badge bg-$color'>" . ucfirst($business['status']) . "</span></td>";
        echo "<td>" . ($business['last_contact_date'] ? date('M d, Y', strtotime($business['last_contact_date'])) : 'Never') . "</td>";
        echo "<td>" . ($business['follow_up_date'] ? date('M d, Y', strtotime($business['follow_up_date'])) : 'Not set') . "</td>";
        echo "<td>";
        echo "<div class='btn-group' role='group'>";
        echo "<button type='button' class='btn btn-sm btn-outline-primary' onclick='updateStatus({$business['id']})'>";
        echo "<i class='fas fa-edit'></i>";
        echo "</button>";
        echo "<button type='button' class='btn btn-sm btn-outline-info' onclick='sendEmail(\"{$business['email']}\", \"" . htmlspecialchars($business['business_name']) . "\")'>";
        echo "<i class='fas fa-envelope'></i>";
        echo "</button>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "<p class='text-muted'>No businesses added yet. Start by adding businesses above!</p>";
}

echo "</div>";
echo "</div>";

// Status update modal
echo "<div class='modal fade' id='statusModal' tabindex='-1'>";
echo "<div class='modal-dialog'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Update Business Status</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal'></button>";
echo "</div>";
echo "<div class='modal-body'>";
echo "<form method='POST' id='statusForm'>";
echo "<input type='hidden' name='action' value='update_status'>";
echo "<input type='hidden' name='business_id' id='businessId'>";
echo "<div class='mb-3'>";
echo "<label class='form-label'>Status</label>";
echo "<select class='form-select' name='status' id='statusSelect'>";
echo "<option value='prospect'>Prospect</option>";
echo "<option value='contacted'>Contacted</option>";
echo "<option value='interested'>Interested</option>";
echo "<option value='negotiating'>Negotiating</option>";
echo "<option value='closed'>Closed</option>";
echo "<option value='not_interested'>Not Interested</option>";
echo "</select>";
echo "</div>";
echo "<div class='mb-3'>";
echo "<label class='form-label'>Follow-up Date</label>";
echo "<input type='date' class='form-control' name='follow_up_date'>";
echo "</div>";
echo "<button type='submit' class='btn btn-primary'>Update Status</button>";
echo "</form>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Quick email function
echo "<script>";
echo "function updateStatus(businessId) {";
echo "    document.getElementById('businessId').value = businessId;";
echo "    new bootstrap.Modal(document.getElementById('statusModal')).show();";
echo "}";
echo "function sendEmail(email, businessName) {";
echo "    window.location.href = 'mailto:' + email + '?subject=Advertising Opportunity with PK Live News&body=Dear ' + encodeURIComponent(businessName) + ' team,';";
echo "}";
echo "</script>";

// Bootstrap CSS and JS
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>";
?>

<style>
.table { margin-bottom: 0; }
.btn-group .btn { margin: 0; }
.card { margin-bottom: 20px; }
.card-header { padding: 15px; }
.badge { font-size: 0.8em; }
</style>
