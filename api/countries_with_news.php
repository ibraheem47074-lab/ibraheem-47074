<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Get parameters
$region = isset($_GET['region']) ? clean_input($_GET['region']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

// Build query
$query = "SELECT c.code, c.name, c.flag_emoji, c.region, c.continent, c.capital, c.population,
         COUNT(n.id) as news_count,
         MAX(n.published_at) as latest_news_date,
         GROUP_CONCAT(DISTINCT n.category_id) as category_ids
         FROM countries c
         LEFT JOIN news n ON c.code = n.country_code AND n.status = 'published'";

$where_conditions = [];
$params = [];
$types = '';

if (!empty($region)) {
    $where_conditions[] = "c.region = ?";
    $params[] = $region;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(c.name LIKE ? OR c.capital LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY c.code, c.name, c.flag_emoji, c.region, c.continent, c.capital, c.population
           HAVING news_count > 0
           ORDER BY news_count DESC, c.name ASC
           LIMIT ?";
$params[] = $limit;
$types .= 'i';

// Execute query
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

// Fetch countries
$countries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $countries[] = [
        'code' => $row['code'],
        'name' => $row['name'],
        'flag_emoji' => $row['flag_emoji'],
        'region' => $row['region'],
        'continent' => $row['continent'],
        'capital' => $row['capital'],
        'population' => $row['population'],
        'news_count' => $row['news_count'],
        'latest_news_date' => $row['latest_news_date'],
        'category_ids' => $row['category_ids'] ? explode(',', $row['category_ids']) : []
    ];
}

// Get regions summary
$regions_query = "SELECT region, COUNT(DISTINCT c.code) as countries_count, COUNT(n.id) as news_count,
                  SUM(CASE WHEN n.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as recent_news_count
                  FROM countries c
                  LEFT JOIN news n ON c.code = n.country_code AND n.status = 'published'
                  WHERE c.region IS NOT NULL AND c.region != ''";

if (!empty($region)) {
    $regions_query .= " AND c.region = ?";
}

$regions_query .= " GROUP BY c.region
                   HAVING news_count > 0
                   ORDER BY news_count DESC, c.region ASC";

$regions_result = !empty($region) ? 
    (function() use ($conn, $regions_query, $region) {
        $stmt = mysqli_prepare($conn, $regions_query);
        mysqli_stmt_bind_param($stmt, 's', $region);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    })() : mysqli_query($conn, $regions_query);

$regions = [];
while ($row = mysqli_fetch_assoc($regions_result)) {
    $regions[] = [
        'name' => $row['region'],
        'countries_count' => $row['countries_count'],
        'news_count' => $row['news_count'],
        'recent_news_count' => $row['recent_news_count']
    ];
}

// Get global statistics
$stats_query = "SELECT 
                COUNT(DISTINCT c.code) as total_countries,
                COUNT(n.id) as total_news,
                SUM(CASE WHEN n.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as recent_news,
                COUNT(DISTINCT c.region) as total_regions
                FROM countries c
                LEFT JOIN news n ON c.code = n.country_code AND n.status = 'published'
                WHERE n.id IS NOT NULL";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Return response
echo json_encode([
    'success' => true,
    'countries' => $countries,
    'regions' => $regions,
    'statistics' => [
        'total_countries' => $stats['total_countries'],
        'total_news' => $stats['total_news'],
        'recent_news' => $stats['recent_news'],
        'total_regions' => $stats['total_regions']
    ],
    'filters' => [
        'region' => $region,
        'search' => $search
    ]
]);
?>
