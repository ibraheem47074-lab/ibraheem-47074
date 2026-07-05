<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class NewsAPI {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // List news articles
    public function listNews() {
        $status = $_GET['status'] ?? 'published';
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $query = "SELECT n.id, n.title, n.slug, n.excerpt, n.image, n.published_at, 
                 c.name as category_name, c.slug as category_slug,
                 u.name as author_name 
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 LEFT JOIN users u ON n.author_id = u.id 
                 WHERE n.status = ? 
                 ORDER BY n.published_at DESC 
                 LIMIT ? OFFSET ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'sii', $status, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $news = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'news' => $news,
            'total' => count($news)
        ]);
    }
    
    // Get news details
    public function getNews() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'News ID is required']);
            return;
        }
        
        $query = "SELECT n.*, c.name as category_name, c.slug as category_slug,
                 u.name as author_name, u.email as author_email 
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 LEFT JOIN users u ON n.author_id = u.id 
                 WHERE n.id = ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $news = mysqli_fetch_assoc($result);
            echo json_encode(['success' => true, 'news' => $news]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'News article not found']);
        }
    }
    
    // Get breaking news for alerts
    public function getBreakingNews() {
        $limit = (int)($_GET['limit'] ?? 10);
        
        $query = "SELECT n.id, n.title, n.slug, n.excerpt, n.image, n.published_at,
                 c.name as category_name, c.slug as category_slug
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'published' AND (n.is_breaking = 1 OR n.status = 'featured')
                 ORDER BY n.published_at DESC 
                 LIMIT ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $news = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'news' => $news,
            'total' => count($news)
        ]);
    }
    
    // Search news
    public function searchNews() {
        $q = clean_input($_GET['q'] ?? '');
        $limit = (int)($_GET['limit'] ?? 20);
        
        if (empty($q)) {
            http_response_code(400);
            echo json_encode(['error' => 'Search query is required']);
            return;
        }
        
        $query = "SELECT n.id, n.title, n.slug, n.excerpt, n.image, n.published_at,
                 c.name as category_name, c.slug as category_slug
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'published' AND (n.title LIKE ? OR n.content LIKE ?)
                 ORDER BY n.published_at DESC 
                 LIMIT ?";
        
        $search_term = "%$q%";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $search_term, $search_term, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $news = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'news' => $news,
            'query' => $q,
            'total' => count($news)
        ]);
    }
    
    // Get news by category
    public function getNewsByCategory() {
        $category_slug = clean_input($_GET['category'] ?? '');
        $limit = (int)($_GET['limit'] ?? 20);
        
        if (empty($category_slug)) {
            http_response_code(400);
            echo json_encode(['error' => 'Category slug is required']);
            return;
        }
        
        $query = "SELECT n.id, n.title, n.slug, n.excerpt, n.image, n.published_at,
                 c.name as category_name, c.slug as category_slug
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'published' AND c.slug = ?
                 ORDER BY n.published_at DESC 
                 LIMIT ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $category_slug, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $news = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'news' => $news,
            'category' => $category_slug,
            'total' => count($news)
        ]);
    }
    
    // Get recent news for homepage
    public function getRecentNews() {
        $limit = (int)($_GET['limit'] ?? 10);
        $exclude_breaking = ($_GET['exclude_breaking'] ?? 'false') === 'true';
        
        $query = "SELECT n.id, n.title, n.slug, n.excerpt, n.image, n.published_at, n.is_breaking,
                 c.name as category_name, c.slug as category_slug
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'published'";
        
        if ($exclude_breaking) {
            $query .= " AND n.is_breaking = 0";
        }
        
        $query .= " ORDER BY n.published_at DESC LIMIT ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $news = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'news' => $news,
            'total' => count($news)
        ]);
    }
}

// Handle API requests
$newsAPI = new NewsAPI($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        switch ($action) {
            case 'list':
                $newsAPI->listNews();
                break;
            case 'get':
                $newsAPI->getNews();
                break;
            case 'breaking':
                $newsAPI->getBreakingNews();
                break;
            case 'search':
                $newsAPI->searchNews();
                break;
            case 'category':
                $newsAPI->getNewsByCategory();
                break;
            case 'recent':
                $newsAPI->getRecentNews();
                break;
            default:
                // Default to list if no action specified
                $newsAPI->listNews();
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
