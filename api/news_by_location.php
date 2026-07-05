<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Get parameters
$country = isset($_GET['country']) ? clean_input($_GET['country']) : '';
$region = isset($_GET['region']) ? clean_input($_GET['region']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT n.id, n.title, n.slug, n.excerpt, n.image, n.published_at, n.views,
          c.name as category_name, c.slug as category_slug,
          u.name as author_name,
          co.flag_emoji, co.name as country_name, co.capital as country_capital,
          (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
          (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
          FROM news n
          LEFT JOIN categories c ON n.category_id = c.id
          LEFT JOIN users u ON n.author_id = u.id
          LEFT JOIN countries co ON n.country_code = co.code
          WHERE n.status = 'published'";

$params = [];
$types = '';

if (!empty($country)) {
    $query .= " AND n.country_code = ?";
    $params[] = $country;
    $types .= 's';
}

if (!empty($region)) {
    $query .= " AND n.country_code IN (SELECT code FROM countries WHERE region = ?)";
    $params[] = $region;
    $types .= 's';
}

$query .= " ORDER BY n.published_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Execute query
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

// Fetch news
$news = [];
while ($row = mysqli_fetch_assoc($result)) {
    $news[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'slug' => $row['slug'],
        'excerpt' => $row['excerpt'],
        'image' => $row['image'],
        'published_at' => $row['published_at'],
        'views' => $row['views'],
        'category' => [
            'name' => $row['category_name'],
            'slug' => $row['category_slug']
        ],
        'author' => $row['author_name'],
        'country' => [
            'name' => $row['country_name'],
            'flag' => $row['flag_emoji'],
            'capital' => $row['country_capital']
        ],
        'comments' => $row['comment_count'],
        'likes' => $row['likes_count'],
        'url' => SITE_URL . 'news.php?slug=' . $row['slug']
    ];
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM news n WHERE n.status = 'published'";
$count_params = [];
$count_types = '';

if (!empty($country)) {
    $count_query .= " AND n.country_code = ?";
    $count_params[] = $country;
    $count_types .= 's';
}

if (!empty($region)) {
    $count_query .= " AND n.country_code IN (SELECT code FROM countries WHERE region = ?)";
    $count_params[] = $region;
    $count_types .= 's';
}

if (!empty($count_params)) {
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
}

$total_news = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_news / $limit);

// Return response
echo json_encode([
    'success' => true,
    'news' => $news,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_news' => $total_news,
        'has_next' => $page < $total_pages,
        'has_prev' => $page > 1
    ],
    'filters' => [
        'country' => $country,
        'region' => $region
    ]
]);
?>
