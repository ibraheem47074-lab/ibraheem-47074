<?php
require_once 'config/database.php';

echo "<h1>PK Live News - Search CSS Fix</h1>";

// Fix 1: Check if search results are being hidden by CSS
echo "<h2>1. Adding Search Result CSS</h2>";

$search_css = "
/* Search Results Styling */
.search-results-section {
    background: #fff;
    padding: 20px 0;
}

.search-result-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.search-result-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.search-result-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: bold;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.search-meta {
    border-top: 1px solid #e9ecef;
    padding-top: 10px;
    margin-top: 10px;
}

.search-actions .btn {
    padding: 4px 8px;
    font-size: 12px;
}

/* Ensure search results are visible */
.results-list {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Fix for potential Bootstrap conflicts */
.search-result-item .card {
    border: none;
    box-shadow: none;
}

/* Loading state */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* Popular searches */
.popular-searches .badge {
    cursor: pointer;
    transition: all 0.2s ease;
}

.popular-searches .badge:hover {
    background-color: #dc3545 !important;
    color: white !important;
    transform: scale(1.05);
}
";

// Add CSS to the main style.css file
$style_file = 'assets/css/style.css';
if (file_exists($style_file)) {
    $current_css = file_get_contents($style_file);
    
    // Check if search CSS already exists
    if (strpos($current_css, '.search-results-section') === false) {
        // Add search CSS to the end
        file_put_contents($style_file, $current_css . "\n\n" . $search_css);
        echo "<div style='color: green;'>✓ Added search CSS to style.css</div>";
    } else {
        echo "<div style='color: blue;'>ℹ Search CSS already exists in style.css</div>";
    }
} else {
    // Create the CSS file
    file_put_contents($style_file, $search_css);
    echo "<div style='color: green;'>✓ Created style.css with search styles</div>";
}

// Fix 2: Create a standalone search page with embedded CSS
echo "<h2>2. Creating Standalone Search Page</h2>";

$standalone_search = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for: <?php echo htmlspecialchars($query); ?> - PK Live News</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #f8f9fa;
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .search-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .search-result-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .search-result-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .search-result-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .badge-category {
            background: #007bff;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .search-meta {
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .no-results {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
        }
        
        .popular-searches {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn-search {
            background: #dc3545;
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-search:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .form-control-search {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control-search:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
</head>
<body>';

// Add PHP processing
$standalone_search .= '<?php
require_once "config/database.php";

// Get search query
$query = isset($_GET["q"]) ? clean_input($_GET["q"]) : "";
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;

if (empty($query)) {
    header("Location: index.php");
    exit;
}

// Pagination
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search news
$search_term = "%$query%";
$search_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                 (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = \'approved\') as comment_count,
                 (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count,
                 (CASE WHEN n.title LIKE ? THEN 3 
                       WHEN n.excerpt LIKE ? THEN 2 
                       WHEN n.content LIKE ? THEN 1 
                       ELSE 0 END) as relevance
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 LEFT JOIN users u ON n.author_id = u.id 
                 WHERE n.status = \'published\' AND n.published_at <= NOW() 
                 AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)
                 ORDER BY relevance DESC, n.published_at DESC 
                 LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $search_query);
mysqli_stmt_bind_param($stmt, "ssssssii", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total results
$count_query = "SELECT COUNT(*) as total FROM news 
                WHERE status = \'published\' AND published_at <= NOW() 
                AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
$count_stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($count_stmt, "sss", $search_term, $search_term, $search_term);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)["total"];
$total_pages = ceil($total_records / $per_page);

// Helper function
function highlightSearchTerm($text, $term) {
    if (empty($term)) return $text;
    $words = explode(" ", $term);
    foreach ($words as $word) {
        if (strlen($word) > 2) {
            $text = preg_replace(\'/(\' . preg_quote($word, \'/\') . \')/i\', \'<span class="search-highlight">$1</span>\', $text);
        }
    }
    return $text;
}
?>

<div class="search-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">Search Results</h1>
                <p class="mb-0 opacity-75">
                    Found <?php echo number_format($total_records); ?> results for 
                    <strong>"<?php echo htmlspecialchars($query); ?>"</strong>
                </p>
            </div>
            <div class="col-md-4">
                <form method="GET" class="d-flex">
                    <input type="text" name="q" class="form-control form-control-search me-2" 
                           value="<?php echo htmlspecialchars($query); ?>" placeholder="Search again...">
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="search-results">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="results-list">
                <?php while ($news = mysqli_fetch_assoc($result)): ?>
                    <div class="search-result-item">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <?php if (!empty($news["image"]) && file_exists($news["image"])): ?>
                                    <img src="<?php echo htmlspecialchars($news["image"]); ?>" 
                                         alt="<?php echo htmlspecialchars($news["title"]); ?>" 
                                         class="img-fluid">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <div class="p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge-category"><?php echo htmlspecialchars($news["category_name"] ?? "General"); ?></span>
                                        </div>
                                        <small class="text-muted">Relevance: <?php echo round($news["relevance"], 2); ?></small>
                                    </div>
                                    
                                    <h4 class="mb-3">
                                        <a href="news.php?slug=<?php echo $news["slug"]; ?>" class="text-decoration-none text-dark">
                                            <?php echo highlightSearchTerm(htmlspecialchars($news["title"]), $query); ?>
                                        </a>
                                    </h4>
                                    
                                    <p class="text-muted mb-3">
                                        <?php 
                                        $excerpt = htmlspecialchars($news["excerpt"] ?? "");
                                        if (empty($excerpt)) {
                                            $excerpt = htmlspecialchars(substr(strip_tags($news["content"] ?? ""), 0, 200)) . "...";
                                        }
                                        echo highlightSearchTerm($excerpt, $query);
                                        ?>
                                    </p>
                                    
                                    <div class="search-meta">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-2"></i> <?php echo format_date($news["published_at"]); ?>
                                            <i class="fas fa-eye ms-3 me-1"></i> <?php echo number_format($news["views"] ?? 0); ?> views
                                            <i class="fas fa-heart ms-3 me-1"></i> <?php echo $news["likes_count"]; ?> likes
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search fa-4x mb-4 text-muted"></i>
                <h3 class="mb-3">No results found</h3>
                <p class="mb-4">We couldn\'t find any results for "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
                <a href="index.php" class="btn btn-search btn-lg">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Search results pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? "active" : ""; ?>">
                        <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
    
    <div class="popular-searches">
        <h5 class="mb-3"><i class="fas fa-fire me-2"></i>Popular Searches</h5>
        <div class="d-flex flex-wrap gap-2">
            <?php
            $popular_terms = ["politics", "sports", "technology", "breaking news", "pakistan", "international", "business", "entertainment"];
            foreach ($popular_terms as $term):
            ?>
                <a href="?q=<?php echo urlencode($term); ?>" class="badge bg-light text-dark text-decoration-none p-2">
                    <?php echo htmlspecialchars($term); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>';

if (file_put_contents('standalone_search.php', $standalone_search)) {
    echo "<div style='color: green;'>✓ Created standalone search page</div>";
} else {
    echo "<div style='color: red;'>✗ Failed to create standalone search page</div>";
}

echo "<h2>🎉 Search CSS Fix Complete!</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 4px;'>";
echo "<strong>Fixed:</strong><br>";
echo "• Added search CSS to main stylesheet<br>";
echo "• Created standalone search page with embedded CSS<br>";
echo "• Fixed visibility and display issues<br>";
echo "• Added proper styling for search results<br><br>";
echo "<strong>To Test:</strong><br>";
echo "1. Original search: <a href='search.php?q=Iranian'>search.php?q=Iranian</a><br>";
echo "2. Minimal test: <a href='minimal_search_test.php?q=Iranian'>minimal_search_test.php?q=Iranian</a><br>";
echo "3. Standalone: <a href='standalone_search.php?q=Iranian'>standalone_search.php?q=Iranian</a><br>";
echo "4. Check which one works best<br>";
echo "</div>";
?>
