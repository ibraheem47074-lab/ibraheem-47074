<?php
require_once 'config/database.php';
require_once 'config/helpers.php';
require_once 'includes/language_functions.php';

$page_title = 'Upcoming Events';
$current_lang = get_current_language();

// Get events with filters
$status = clean_input($_GET['status'] ?? 'upcoming');
$type = clean_input($_GET['type'] ?? '');
$category = clean_input($_GET['category'] ?? '');
$priority = clean_input($_GET['priority'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$query = "SELECT e.*, u.name as creator_name FROM events e 
         LEFT JOIN users u ON e.created_by = u.id 
         WHERE e.is_public = 1";
$params = [];
$types = '';

if ($status) {
    $query .= " AND e.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($type) {
    $query .= " AND e.type = ?";
    $params[] = $type;
    $types .= 's';
}

if ($category) {
    $query .= " AND e.category LIKE ?";
    $params[] = '%' . $category . '%';
    $types .= 's';
}

if ($priority) {
    $query .= " AND e.priority = ?";
    $params[] = $priority;
    $types .= 's';
}

$query .= " ORDER BY e.priority DESC, e.event_date ASC, e.event_time ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$events_result = mysqli_stmt_get_result($stmt);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM events e WHERE e.is_public = 1";
$count_params = [];
$count_types = '';

if ($status) {
    $count_query .= " AND e.status = ?";
    $count_params[] = $status;
    $count_types .= 's';
}

if ($type) {
    $count_query .= " AND e.type = ?";
    $count_params[] = $type;
    $count_types .= 's';
}

if ($category) {
    $count_query .= " AND e.category LIKE ?";
    $count_params[] = '%' . $category . '%';
    $count_types .= 's';
}

if ($priority) {
    $count_query .= " AND e.priority = ?";
    $count_params[] = $priority;
    $count_types .= 's';
}

$count_stmt = mysqli_prepare($conn, $count_query);
if (!empty($count_params)) {
    mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
}
mysqli_stmt_execute($count_stmt);
$total_records = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
$total_pages = ceil($total_records / $per_page);

// Get event statistics
$stats_query = "SELECT 
                COUNT(*) as total_events,
                COUNT(CASE WHEN status = 'upcoming' THEN 1 END) as upcoming,
                COUNT(CASE WHEN status = 'ongoing' THEN 1 END) as ongoing,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent
                FROM events WHERE is_public = 1";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

include 'includes/header.php';
?>

<!-- Events Header -->
<section class="events-header py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-calendar-alt me-3"></i>Upcoming Events
                </h1>
                <p class="lead mb-0">Discover and participate in exciting events happening around you</p>
            </div>
            <div class="col-md-4">
                <div class="event-stats">
                    <div class="text-center">
                        <div class="event-stat-number"><?php echo $stats['total_events']; ?></div>
                        <div class="event-stat-label">Total Events</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Event Statistics -->
<section class="event-statistics py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="event-stat-card">
                    <div class="event-stat-number"><?php echo $stats['upcoming']; ?></div>
                    <div class="event-stat-label">Upcoming</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="event-stat-card">
                    <div class="event-stat-number"><?php echo $stats['ongoing']; ?></div>
                    <div class="event-stat-label">Ongoing</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="event-stat-card">
                    <div class="event-stat-number"><?php echo $stats['completed']; ?></div>
                    <div class="event-stat-label">Completed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="event-stat-card">
                    <div class="event-stat-number"><?php echo $stats['urgent']; ?></div>
                    <div class="event-stat-label">Urgent</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Events Filters -->
<section class="event-filters py-4">
    <div class="container">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="upcoming" <?php echo $status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="ongoing" <?php echo $status === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Event Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="conference" <?php echo $type === 'conference' ? 'selected' : ''; ?>>Conference</option>
                    <option value="meeting" <?php echo $type === 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                    <option value="webinar" <?php echo $type === 'webinar' ? 'selected' : ''; ?>>Webinar</option>
                    <option value="workshop" <?php echo $type === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                    <option value="social" <?php echo $type === 'social' ? 'selected' : ''; ?>>Social</option>
                    <option value="sports" <?php echo $type === 'sports' ? 'selected' : ''; ?>>Sports</option>
                    <option value="political" <?php echo $type === 'political' ? 'selected' : ''; ?>>Political</option>
                    <option value="other" <?php echo $type === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="urgent" <?php echo $priority === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" placeholder="Enter category" value="<?php echo htmlspecialchars($category ?? ''); ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
                <a href="events.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</section>

<!-- Events List -->
<section class="events-list-section py-5">
    <div class="container">
        <?php if (mysqli_num_rows($events_result) > 0): ?>
            <div class="row g-4">
                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card event-card h-100 <?php echo $event['status']; ?>">
                            <?php if ($event['image']): ?>
                                <img src="<?php echo $event['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <span class="badge bg-<?php echo $event['priority'] === 'urgent' ? 'danger' : ($event['priority'] === 'high' ? 'warning' : ($event['priority'] === 'medium' ? 'info' : 'secondary')); ?>">
                                        <?php echo ucfirst($event['priority']); ?>
                                    </span>
                                </div>
                                
                                <div class="event-date-badge mb-3">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                    <?php if ($event['event_time']): ?>
                                        at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($event['location']): ?>
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($event['organizer']): ?>
                                <div class="mb-2">
                                    <i class="fas fa-user text-info me-2"></i>
                                    <?php echo htmlspecialchars($event['organizer']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <p class="card-text flex-grow-1">
                                    <?php echo substr(htmlspecialchars($event['description']), 0, 150); ?>...
                                </p>
                                
                                <div class="event-meta mb-3">
                                    <span class="badge bg-info me-1"><?php echo ucfirst($event['type']); ?></span>
                                    <span class="badge bg-<?php echo $event['status'] === 'upcoming' ? 'primary' : ($event['status'] === 'ongoing' ? 'success' : 'secondary'); ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="event-actions mt-auto">
                                    <?php if ($event['url']): ?>
                                        <a href="<?php echo htmlspecialchars($event['url']); ?>" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fas fa-external-link-alt me-1"></i>Visit Event
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['requires_registration']): ?>
                                        <button class="btn btn-outline-success btn-sm" onclick="showRegistrationModal(<?php echo $event['id']; ?>)">
                                            <i class="fas fa-user-plus me-1"></i>Register
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Events pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&type=<?php echo $type; ?>&category=<?php echo $category; ?>&priority=<?php echo $priority; ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&type=<?php echo $type; ?>&category=<?php echo $category; ?>&priority=<?php echo $priority; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&type=<?php echo $type; ?>&category=<?php echo $category; ?>&priority=<?php echo $priority; ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-times"></i>
                <h4>No Events Found</h4>
                <p>There are no events matching your criteria. Try adjusting your filters or check back later.</p>
                <a href="events.php" class="btn btn-primary">View All Events</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Event Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="registrationContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Link to events CSS -->
<link rel="stylesheet" href="assets/css/events.css">

<script>
function showRegistrationModal(eventId) {
    // Load event details and show registration form
    fetch(`api/events.php?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const event = data.event;
                const content = `
                    <div class="event-registration">
                        <h6>${event.title}</h6>
                        <p><strong>Date:</strong> ${new Date(event.event_date).toLocaleDateString()}</p>
                        <p><strong>Location:</strong> ${event.location || 'Online'}</p>
                        <p><strong>Organizer:</strong> ${event.organizer || 'N/A'}</p>
                        ${event.registration_deadline ? `<p><strong>Registration Deadline:</strong> ${new Date(event.registration_deadline).toLocaleDateString()}</p>` : ''}
                        ${event.max_attendees ? `<p><strong>Max Attendees:</strong> ${event.max_attendees}</p>` : ''}
                        
                        <form id="registrationForm">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Email</label>
                                <input type="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Special Requirements (Optional)</label>
                                <textarea class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Register for Event</button>
                        </form>
                    </div>
                `;
                
                document.getElementById('registrationContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('registrationModal')).show();
            } else {
                alert('Error loading event details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading event details');
        });
}

// Handle registration form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Here you would typically send the registration data to your server
            alert('Registration submitted successfully! You will receive a confirmation email shortly.');
            bootstrap.Modal.getInstance(document.getElementById('registrationModal')).hide();
        });
    }
});
</script>
