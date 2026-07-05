<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Handle CRUD operations
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = clean_input($_POST['title']);
            $description = clean_input($_POST['description']);
            $event_date = clean_input($_POST['event_date']);
            $event_time = clean_input($_POST['event_time']);
            $end_date = clean_input($_POST['end_date']);
            $end_time = clean_input($_POST['end_time']);
            $location = clean_input($_POST['location']);
            $category = clean_input($_POST['category']);
            $type = clean_input($_POST['type']);
            $priority = clean_input($_POST['priority']);
            $url = clean_input($_POST['url']);
            $organizer = clean_input($_POST['organizer']);
            $contact_email = clean_input($_POST['contact_email']);
            $max_attendees = clean_input($_POST['max_attendees']) ? (int)clean_input($_POST['max_attendees']) : null;
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            $requires_registration = isset($_POST['requires_registration']) ? 1 : 0;
            $registration_deadline = clean_input($_POST['registration_deadline']);
            $tags = clean_input($_POST['tags']);
            
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/events/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = 'uploads/events/' . $file_name;
                }
            }
            
            $query = "INSERT INTO events (title, description, event_date, event_time, end_date, end_time, 
                      location, category, type, priority, image, url, organizer, contact_email, 
                      max_attendees, is_public, requires_registration, registration_deadline, tags, created_by) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssssssssssssssiiisss', 
                $title, $description, $event_date, $event_time, $end_date, $end_time,
                $location, $category, $type, $priority, $image, $url, $organizer, $contact_email,
                $max_attendees, $is_public, $requires_registration, $registration_deadline, $tags, $_SESSION['user_id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Event created successfully!";
                $action = 'list';
            } else {
                $error = "Error creating event: " . mysqli_error($conn);
            }
        }
        break;
        
    case 'edit':
        $event_id = clean_input($_GET['id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update logic similar to add
            $title = clean_input($_POST['title']);
            $description = clean_input($_POST['description']);
            $event_date = clean_input($_POST['event_date']);
            $event_time = clean_input($_POST['event_time']);
            $end_date = clean_input($_POST['end_date']);
            $end_time = clean_input($_POST['end_time']);
            $location = clean_input($_POST['location']);
            $category = clean_input($_POST['category']);
            $type = clean_input($_POST['type']);
            $priority = clean_input($_POST['priority']);
            $url = clean_input($_POST['url']);
            $organizer = clean_input($_POST['organizer']);
            $contact_email = clean_input($_POST['contact_email']);
            $max_attendees = clean_input($_POST['max_attendees']) ? (int)clean_input($_POST['max_attendees']) : null;
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            $requires_registration = isset($_POST['requires_registration']) ? 1 : 0;
            $registration_deadline = clean_input($_POST['registration_deadline']);
            $tags = clean_input($_POST['tags']);
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/events/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image = 'uploads/events/' . $file_name;
                    
                    // Update with new image
                    $query = "UPDATE events SET title=?, description=?, event_date=?, event_time=?, 
                              end_date=?, end_time=?, location=?, category=?, type=?, priority=?, 
                              image=?, url=?, organizer=?, contact_email=?, max_attendees=?, 
                              is_public=?, requires_registration=?, registration_deadline=?, tags=? 
                              WHERE id=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'ssssssssssssssiiisss', 
                        $title, $description, $event_date, $event_time, $end_date, $end_time,
                        $location, $category, $type, $priority, $image, $url, $organizer, $contact_email,
                        $max_attendees, $is_public, $requires_registration, $registration_deadline, $tags, $event_id);
                }
            } else {
                // Update without changing image
                $query = "UPDATE events SET title=?, description=?, event_date=?, event_time=?, 
                          end_date=?, end_time=?, location=?, category=?, type=?, priority=?, 
                          url=?, organizer=?, contact_email=?, max_attendees=?, 
                          is_public=?, requires_registration=?, registration_deadline=?, tags=? 
                          WHERE id=?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssssssssssssssiiisss', 
                    $title, $description, $event_date, $event_time, $end_date, $end_time,
                    $location, $category, $type, $priority, $url, $organizer, $contact_email,
                    $max_attendees, $is_public, $requires_registration, $registration_deadline, $tags, $event_id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Event updated successfully!";
                $action = 'list';
            } else {
                $error = "Error updating event: " . mysqli_error($conn);
            }
        } else {
            // Get event data for editing
            $query = "SELECT * FROM events WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $event_id);
            mysqli_stmt_execute($stmt);
            $event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        }
        break;
        
    case 'delete':
        $event_id = clean_input($_GET['id']);
        
        $query = "DELETE FROM events WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $event_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Event deleted successfully!";
        } else {
            $error = "Error deleting event: " . mysqli_error($conn);
        }
        $action = 'list';
        break;
        
    case 'toggle_status':
        $event_id = clean_input($_GET['id']);
        $new_status = clean_input($_GET['status']);
        
        $query = "UPDATE events SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $new_status, $event_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Event status updated successfully!";
        } else {
            $error = "Error updating event status: " . mysqli_error($conn);
        }
        $action = 'list';
        break;
}

// Get events list
$events_query = "SELECT e.*, u.name as creator_name FROM events e 
                 LEFT JOIN users u ON e.created_by = u.id 
                 ORDER BY e.event_date ASC, e.created_at DESC";
$events = mysqli_query($conn, $events_query);

$page_title = $action === 'add' ? 'Add Event' : ($action === 'edit' ? 'Edit Event' : 'Manage Events');
include 'includes/admin-header.php';
?>

<div class="admin-main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-alt me-2"></i><?php echo $page_title; ?></h2>
        <?php if ($action === 'list'): ?>
        <a href="manage-events.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Event
        </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Events List -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($event = mysqli_fetch_assoc($events)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($event['image']): ?>
                                            <img src="../<?php echo $event['image']; ?>" alt="<?php echo $event['title']; ?>" 
                                                 class="me-3" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo $event['title']; ?></strong><br>
                                            <small class="text-muted"><?php echo $event['organizer']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($event['event_date'])); ?><br>
                                    <small class="text-muted"><?php echo $event['event_time']; ?></small>
                                </td>
                                <td><?php echo $event['location']; ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($event['type']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'upcoming' => 'primary',
                                        'ongoing' => 'success',
                                        'completed' => 'secondary',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $status_colors[$event['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($event['status']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $priority_colors = [
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'urgent' => 'danger'
                                    ];
                                    $color = $priority_colors[$event['priority']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($event['priority']); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="manage-events.php?action=edit&id=<?php echo $event['id']; ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($event['status'] === 'upcoming'): ?>
                                        <a href="manage-events.php?action=toggle_status&id=<?php echo $event['id']; ?>&status=ongoing" 
                                           class="btn btn-outline-success" title="Start Event">
                                            <i class="fas fa-play"></i>
                                        </a>
                                        <?php elseif ($event['status'] === 'ongoing'): ?>
                                        <a href="manage-events.php?action=toggle_status&id=<?php echo $event['id']; ?>&status=completed" 
                                           class="btn btn-outline-secondary" title="Complete Event">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="manage-events.php?action=delete&id=<?php echo $event['id']; ?>" 
                                           class="btn btn-outline-danger" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this event?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Add/Edit Event Form -->
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Event Title *</label>
                                <input type="text" class="form-control" name="title" required
                                       value="<?php echo $event['title'] ?? ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"><?php echo $event['description'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Event Date *</label>
                                        <input type="date" class="form-control" name="event_date" required
                                               value="<?php echo $event['event_date'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Event Time</label>
                                        <input type="time" class="form-control" name="event_time"
                                               value="<?php echo $event['event_time'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date"
                                               value="<?php echo $event['end_date'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">End Time</label>
                                        <input type="time" class="form-control" name="end_time"
                                               value="<?php echo $event['end_time'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" class="form-control" name="location"
                                               value="<?php echo $event['location'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <input type="text" class="form-control" name="category"
                                               value="<?php echo $event['category'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Event Type</label>
                                        <select class="form-select" name="type">
                                            <option value="conference" <?php echo ($event['type'] ?? '') === 'conference' ? 'selected' : ''; ?>>Conference</option>
                                            <option value="meeting" <?php echo ($event['type'] ?? '') === 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                                            <option value="webinar" <?php echo ($event['type'] ?? '') === 'webinar' ? 'selected' : ''; ?>>Webinar</option>
                                            <option value="workshop" <?php echo ($event['type'] ?? '') === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                                            <option value="social" <?php echo ($event['type'] ?? '') === 'social' ? 'selected' : ''; ?>>Social</option>
                                            <option value="sports" <?php echo ($event['type'] ?? '') === 'sports' ? 'selected' : ''; ?>>Sports</option>
                                            <option value="political" <?php echo ($event['type'] ?? '') === 'political' ? 'selected' : ''; ?>>Political</option>
                                            <option value="other" <?php echo ($event['type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <select class="form-select" name="priority">
                                            <option value="low" <?php echo ($event['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                                            <option value="medium" <?php echo ($event['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                            <option value="high" <?php echo ($event['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                                            <option value="urgent" <?php echo ($event['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Max Attendees</label>
                                        <input type="number" class="form-control" name="max_attendees"
                                               value="<?php echo $event['max_attendees'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Organizer</label>
                                        <input type="text" class="form-control" name="organizer"
                                               value="<?php echo $event['organizer'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" name="contact_email"
                                               value="<?php echo $event['contact_email'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Event URL</label>
                                        <input type="url" class="form-control" name="url"
                                               value="<?php echo $event['url'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Registration Deadline</label>
                                        <input type="datetime-local" class="form-control" name="registration_deadline"
                                               value="<?php echo $event['registration_deadline'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tags</label>
                                <input type="text" class="form-control" name="tags"
                                       value="<?php echo $event['tags'] ?? ''; ?>"
                                       placeholder="Comma-separated tags">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Event Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <?php if (isset($event['image']) && $event['image']): ?>
                                    <div class="mt-2">
                                        <img src="../<?php echo $event['image']; ?>" alt="Current image" 
                                             class="img-fluid" style="max-height: 200px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_public" 
                                           <?php echo ($event['is_public'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Public Event</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requires_registration" 
                                           <?php echo ($event['requires_registration'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Requires Registration</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?php echo $action === 'add' ? 'Create Event' : 'Update Event'; ?>
                        </button>
                        <a href="manage-events.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
