<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get events
        if (isset($_GET['id'])) {
            // Get single event
            $event_id = clean_input($_GET['id']);
            $query = "SELECT e.*, u.name as creator_name FROM events e 
                     LEFT JOIN users u ON e.created_by = u.id 
                     WHERE e.id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $event_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $event = mysqli_fetch_assoc($result);
            
            if ($event) {
                echo json_encode(['success' => true, 'event' => $event]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Event not found']);
            }
        } else {
            // Get events list with filters
            $status = clean_input($_GET['status'] ?? 'upcoming');
            $type = clean_input($_GET['type'] ?? '');
            $category = clean_input($_GET['category'] ?? '');
            $priority = clean_input($_GET['priority'] ?? '');
            $limit = isset($_GET['limit']) ? (int)clean_input($_GET['limit']) : 10;
            $offset = isset($_GET['offset']) ? (int)clean_input($_GET['offset']) : 0;
            
            // Build query
            $query = "SELECT e.*, u.name as creator_name FROM events e 
                     LEFT JOIN users u ON e.created_by = u.id 
                     WHERE 1=1";
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
            
            // For public users, only show public events
            if (!is_admin()) {
                $query .= " AND e.is_public = 1";
            }
            
            $query .= " ORDER BY e.priority DESC, e.event_date ASC, e.event_time ASC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
            
            $stmt = mysqli_prepare($conn, $query);
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $events = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $events[] = $row;
            }
            
            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM events e WHERE 1=1";
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
            
            if (!is_admin()) {
                $count_query .= " AND e.is_public = 1";
            }
            
            $count_stmt = mysqli_prepare($conn, $count_query);
            if (!empty($count_params)) {
                mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
            }
            mysqli_stmt_execute($count_stmt);
            $total = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
            
            echo json_encode([
                'success' => true, 
                'events' => $events, 
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        break;
        
    case 'POST':
        // Create new event (admin only)
        if (!is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }
        
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }
        
        // Validate required fields
        $required_fields = ['title', 'event_date'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                exit;
            }
        }
        
        $query = "INSERT INTO events (title, description, event_date, event_time, end_date, end_time, 
                  location, category, type, priority, image, url, organizer, contact_email, 
                  max_attendees, is_public, requires_registration, registration_deadline, tags, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssssssssssssiiisssi', 
            $data['title'],
            $data['description'] ?? '',
            $data['event_date'],
            $data['event_time'] ?? '',
            $data['end_date'] ?? '',
            $data['end_time'] ?? '',
            $data['location'] ?? '',
            $data['category'] ?? '',
            $data['type'] ?? 'other',
            $data['priority'] ?? 'medium',
            $data['image'] ?? '',
            $data['url'] ?? '',
            $data['organizer'] ?? '',
            $data['contact_email'] ?? '',
            $data['max_attendees'] ?? null,
            $data['is_public'] ?? 1,
            $data['requires_registration'] ?? 0,
            $data['registration_deadline'] ?? '',
            $data['tags'] ?? '',
            $_SESSION['user_id']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $event_id = mysqli_insert_id($conn);
            echo json_encode(['success' => true, 'message' => 'Event created successfully', 'event_id' => $event_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        }
        break;
        
    case 'PUT':
        // Update event (admin only)
        if (!is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }
        
        $event_id = clean_input($_GET['id']);
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        if (!$data || !$event_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid data or event ID']);
            exit;
        }
        
        // Build dynamic update query
        $update_fields = [];
        $params = [];
        $types = '';
        
        $allowed_fields = ['title', 'description', 'event_date', 'event_time', 'end_date', 'end_time', 
                          'location', 'category', 'type', 'priority', 'image', 'url', 'organizer', 
                          'contact_email', 'max_attendees', 'is_public', 'requires_registration', 
                          'registration_deadline', 'tags', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= 's';
            }
        }
        
        if (empty($update_fields)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            exit;
        }
        
        $query = "UPDATE events SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $params[] = $event_id;
        $types .= 'i';
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        }
        break;
        
    case 'DELETE':
        // Delete event (admin only)
        if (!is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }
        
        $event_id = clean_input($_GET['id']);
        
        $query = "DELETE FROM events WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $event_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
