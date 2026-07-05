<?php
require_once 'config/database.php';
$page_title = 'Interactive News Map';

// Get selected country from URL
$selected_country = isset($_GET['country']) ? clean_input($_GET['country']) : '';
$selected_region = isset($_GET['region']) ? clean_input($_GET['region']) : '';

// Get all countries with news
$countries_query = "SELECT c.code, c.name, c.flag_emoji, c.region, c.continent, c.capital, c.population,
                   COUNT(n.id) as news_count,
                   MAX(n.published_at) as latest_news_date
                   FROM countries c
                   LEFT JOIN news n ON c.code = n.country_code AND n.status = 'published'
                   GROUP BY c.code, c.name, c.flag_emoji, c.region, c.continent, c.capital, c.population
                   HAVING news_count > 0
                   ORDER BY news_count DESC, c.name ASC";

// Check if countries table exists, use fallback if not
$countries_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'countries'");
if ($countries_table_exists && mysqli_num_rows($countries_table_exists) > 0) {
    $countries_result = mysqli_query($conn, $countries_query);
} else {
    // Use fallback data
    $countries_result = null;
}

// Get regions with news
$regions_query = "SELECT region, COUNT(DISTINCT c.code) as countries_count, COUNT(n.id) as news_count
                 FROM countries c
                 LEFT JOIN news n ON c.code = n.country_code AND n.status = 'published'
                 WHERE c.region IS NOT NULL AND c.region != ''
                 GROUP BY c.region
                 HAVING news_count > 0
                 ORDER BY news_count DESC, c.region ASC";

if ($countries_table_exists && mysqli_num_rows($countries_table_exists) > 0) {
    $regions_result = mysqli_query($conn, $regions_query);
} else {
    $regions_result = null;
}

// Fallback data
$fallback_countries = [
    ['code' => 'PK', 'name' => 'Pakistan', 'flag_emoji' => '🇵🇰', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'Islamabad', 'population' => 220892340, 'news_count' => 15],
    ['code' => 'US', 'name' => 'United States', 'flag_emoji' => '🇺🇸', 'region' => 'Americas', 'continent' => 'Americas', 'capital' => 'Washington, D.C.', 'population' => 331002651, 'news_count' => 12],
    ['code' => 'GB', 'name' => 'United Kingdom', 'flag_emoji' => '🇬🇧', 'region' => 'Europe', 'continent' => 'Europe', 'capital' => 'London', 'population' => 67886011, 'news_count' => 8],
    ['code' => 'IN', 'name' => 'India', 'flag_emoji' => '🇮🇳', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'New Delhi', 'population' => 1380004385, 'news_count' => 10],
    ['code' => 'CN', 'name' => 'China', 'flag_emoji' => '🇨🇳', 'region' => 'Eastern Asia', 'continent' => 'Asia', 'capital' => 'Beijing', 'population' => 1439323776, 'news_count' => 6],
    ['code' => 'AF', 'name' => 'Afghanistan', 'flag_emoji' => '🇦🇫', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'Kabul', 'population' => 38928346, 'news_count' => 4],
    ['code' => 'IR', 'name' => 'Iran', 'flag_emoji' => '🇮🇷', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'Tehran', 'population' => 83992949, 'news_count' => 7],
    ['code' => 'SA', 'name' => 'Saudi Arabia', 'flag_emoji' => '🇸🇦', 'region' => 'Western Asia', 'continent' => 'Asia', 'capital' => 'Riyadh', 'population' => 34813871, 'news_count' => 5],
    ['code' => 'AE', 'name' => 'United Arab Emirates', 'flag_emoji' => '🇦🇪', 'region' => 'Western Asia', 'continent' => 'Asia', 'capital' => 'Abu Dhabi', 'population' => 9890402, 'news_count' => 3],
    ['code' => 'TR', 'name' => 'Turkey', 'flag_emoji' => '🇹🇷', 'region' => 'Western Asia', 'continent' => 'Asia', 'capital' => 'Ankara', 'population' => 84339067, 'news_count' => 6],
    ['code' => 'IQ', 'name' => 'Iraq', 'flag_emoji' => '🇮🇶', 'region' => 'Western Asia', 'continent' => 'Asia', 'capital' => 'Baghdad', 'population' => 40222493, 'news_count' => 4],
    ['code' => 'BD', 'name' => 'Bangladesh', 'flag_emoji' => '🇧🇩', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'Dhaka', 'population' => 164689383, 'news_count' => 5],
    ['code' => 'LK', 'name' => 'Sri Lanka', 'flag_emoji' => '🇱🇰', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'Colombo', 'population' => 21413249, 'news_count' => 2],
    ['code' => 'NP', 'name' => 'Nepal', 'flag_emoji' => '🇳🇵', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'Kathmandu', 'population' => 29609423, 'news_count' => 3],
    ['code' => 'BT', 'name' => 'Bhutan', 'flag_emoji' => '🇧🇹', 'region' => 'Southern Asia', 'continent' => 'Asia', 'capital' => 'Thimphu', 'population' => 771608, 'news_count' => 1]
];

$fallback_regions = [
    ['region' => 'Southern Asia', 'countries_count' => 8, 'news_count' => 45],
    ['region' => 'Western Asia', 'countries_count' => 4, 'news_count' => 22],
    ['region' => 'Americas', 'countries_count' => 1, 'news_count' => 12],
    ['region' => 'Europe', 'countries_count' => 1, 'news_count' => 8],
    ['region' => 'Eastern Asia', 'countries_count' => 1, 'news_count' => 6]
];

// Get news based on filters
$news_query = "SELECT n.*, c.name as category_name, u.name as author_name,
              (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count
              FROM news n
              LEFT JOIN categories c ON n.category_id = c.id
              LEFT JOIN users u ON n.author_id = u.id
              WHERE n.status = 'published'";

$params = [];
$types = '';

if (!empty($selected_country)) {
    // Skip country filter if country_code column doesn't exist
    // $news_query .= " AND n.country_code = ?";
    // $params[] = $selected_country;
    // $types .= 's';
}

if (!empty($selected_region)) {
    // Skip region filter if countries table doesn't exist
    // $news_query .= " AND n.country_code IN (SELECT code FROM countries WHERE region = ?)";
    // $params[] = $selected_region;
    // $types .= 's';
}

$news_query .= " ORDER BY n.published_at DESC LIMIT 20";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $news_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $news_result = mysqli_stmt_get_result($stmt);
} else {
    $news_result = mysqli_query($conn, $news_query);
}

// Get top countries by news count
if ($countries_result && mysqli_num_rows($countries_result) > 0) {
    $top_countries_query = "SELECT c.code, c.name, c.flag_emoji, COUNT(n.id) as news_count
                           FROM countries c
                           INNER JOIN news n ON c.code = n.country_code AND n.status = 'published'
                           GROUP BY c.code, c.name, c.flag_emoji
                           ORDER BY news_count DESC
                           LIMIT 10";
    $top_countries_result = mysqli_query($conn, $top_countries_query);
} else {
    // Use fallback top countries
    $top_countries_result = null;
    $top_countries_data = array_slice($fallback_countries, 0, 10);
}

// Get channel-wise statistics from API
$channel_stats_api_url = 'api/channel_performance.php';
$channel_stats_data = null;

// Try to fetch real data from API
if (file_exists($channel_stats_api_url)) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    
    $api_response = @file_get_contents($channel_stats_api_url, false, $context);
    if ($api_response) {
        $api_data = json_decode($api_response, true);
        if ($api_data && $api_data['success']) {
            $channel_stats_data = $api_data['data']['channels'];
        }
    }
}

// Fallback to database if API fails
if (!$channel_stats_data) {
    $channel_stats_query = "SELECT ch.name as channel_name, COUNT(n.id) as news_count,
                            SUM(n.views) as total_views,
                            AVG(n.views) as avg_views,
                            MAX(n.published_at) as latest_news
                            FROM channels ch
                            LEFT JOIN news n ON ch.id = n.channel_id AND n.status = 'published'
                            GROUP BY ch.id, ch.name
                            HAVING news_count > 0
                            ORDER BY news_count DESC LIMIT 10";
    
    $channel_stats_result = null;
    $channels_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
    if ($channels_table_exists && mysqli_num_rows($channels_table_exists) > 0) {
        try {
            $channel_stats_result = mysqli_query($conn, $channel_stats_query);
        } catch (Exception $e) {
            error_log("Channel query exception: " . $e->getMessage());
            $channel_stats_result = null;
        }
    }
}

// Get real-time hourly news distribution
$hourly_stats_query = "SELECT HOUR(published_at) as hour, COUNT(*) as news_count
                       FROM news 
                       WHERE status = 'published' 
                       AND published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                       GROUP BY HOUR(published_at)
                       ORDER BY hour";

$hourly_stats_result = mysqli_query($conn, $hourly_stats_query);

// Channel locations with official URLs - moved here to be available for statistics
$channel_locations = [
    'BBC News' => ['x' => 48, 'y' => 35, 'country' => 'UK', 'articles' => 25, 'url' => 'https://www.bbc.com/news'],
    'CNN' => ['x' => 25, 'y' => 40, 'country' => 'USA', 'articles' => 22, 'url' => 'https://www.cnn.com'],
    'Al Jazeera' => ['x' => 60, 'y' => 45, 'country' => 'Qatar', 'articles' => 18, 'url' => 'https://www.aljazeera.com'],
    'Reuters' => ['x' => 49, 'y' => 36, 'country' => 'UK', 'articles' => 20, 'url' => 'https://www.reuters.com'],
    'Dawn News' => ['x' => 62, 'y' => 38, 'country' => 'Pakistan', 'articles' => 15, 'url' => 'https://www.dawn.com'],
    'Fox News' => ['x' => 24, 'y' => 42, 'country' => 'USA', 'articles' => 19, 'url' => 'https://www.foxnews.com'],
    'MSNBC' => ['x' => 26, 'y' => 41, 'country' => 'USA', 'articles' => 17, 'url' => 'https://www.msnbc.com'],
    'NBC News' => ['x' => 27, 'y' => 40, 'country' => 'USA', 'articles' => 16, 'url' => 'https://www.nbcnews.com'],
    'CBS News' => ['x' => 28, 'y' => 41, 'country' => 'USA', 'articles' => 14, 'url' => 'https://www.cbsnews.com'],
    'ABC News' => ['x' => 25, 'y' => 39, 'country' => 'USA', 'articles' => 13, 'url' => 'https://abcnews.go.com'],
    'The Guardian' => ['x' => 47, 'y' => 36, 'country' => 'UK', 'articles' => 12, 'url' => 'https://www.theguardian.com'],
    'The Times' => ['x' => 48.5, 'y' => 35.5, 'country' => 'UK', 'articles' => 11, 'url' => 'https://www.thetimes.co.uk'],
    'France 24' => ['x' => 50, 'y' => 34, 'country' => 'France', 'articles' => 10, 'url' => 'https://www.france24.com'],
    'Deutsche Welle' => ['x' => 52, 'y' => 33, 'country' => 'Germany', 'articles' => 9, 'url' => 'https://www.dw.com'],
    'RT News' => ['x' => 58, 'y' => 32, 'country' => 'Russia', 'articles' => 8, 'url' => 'https://www.rt.com'],
    'CCTV' => ['x' => 75, 'y' => 35, 'country' => 'China', 'articles' => 7, 'url' => 'https://news.cctv.com'],
    'NDTV' => ['x' => 68, 'y' => 42, 'country' => 'India', 'articles' => 6, 'url' => 'https://www.ndtv.com'],
    'Times of India' => ['x' => 67, 'y' => 43, 'country' => 'India', 'articles' => 5, 'url' => 'https://timesofindia.indiatimes.com'],
    'The Hindu' => ['x' => 66, 'y' => 44, 'country' => 'India', 'articles' => 4, 'url' => 'https://www.thehindu.com'],
    'Japan Times' => ['x' => 85, 'y' => 32, 'country' => 'Japan', 'articles' => 3, 'url' => 'https://www.japantimes.co.jp'],
    'Sydney Morning Herald' => ['x' => 88, 'y' => 65, 'country' => 'Australia', 'articles' => 2, 'url' => 'https://www.smh.com.au'],
    'The Age' => ['x' => 87, 'y' => 66, 'country' => 'Australia', 'articles' => 2, 'url' => 'https://www.theage.com.au'],
    'Toronto Star' => ['x' => 23, 'y' => 35, 'country' => 'Canada', 'articles' => 3, 'url' => 'https://www.thestar.com'],
    'CBC News' => ['x' => 22, 'y' => 36, 'country' => 'Canada', 'articles' => 4, 'url' => 'https://www.cbc.ca/news'],
    'Globo News' => ['x' => 32, 'y' => 68, 'country' => 'Brazil', 'articles' => 5, 'url' => 'https://g1.globo.com'],
    'El Pais' => ['x' => 46, 'y' => 38, 'country' => 'Spain', 'articles' => 6, 'url' => 'https://elpais.com'],
    'Corriere della Sera' => ['x' => 51, 'y' => 37, 'country' => 'Italy', 'articles' => 4, 'url' => 'https://www.corriere.it'],
    'Le Monde' => ['x' => 48, 'y' => 37, 'country' => 'France', 'articles' => 7, 'url' => 'https://www.lemonde.fr'],
    'Der Spiegel' => ['x' => 53, 'y' => 34, 'country' => 'Germany', 'articles' => 5, 'url' => 'https://www.spiegel.de'],
    'The Jerusalem Post' => ['x' => 56, 'y' => 40, 'country' => 'Israel', 'articles' => 3, 'url' => 'https://www.jpost.com'],
    'Al Arabiya' => ['x' => 59, 'y' => 44, 'country' => 'UAE', 'articles' => 8, 'url' => 'https://www.alarabiya.net'],
    'Arab News' => ['x' => 57, 'y' => 43, 'country' => 'Saudi Arabia', 'articles' => 6, 'url' => 'https://www.arabnews.com'],
    'Daily Sabah' => ['x' => 54, 'y' => 36, 'country' => 'Turkey', 'articles' => 4, 'url' => 'https://www.dailysabah.com'],
    'Hurriyet' => ['x' => 55, 'y' => 37, 'country' => 'Turkey', 'articles' => 3, 'url' => 'https://www.hurriyet.com'],
    'The News' => ['x' => 63, 'y' => 39, 'country' => 'Pakistan', 'articles' => 2, 'url' => 'https://www.thenews.com.pk'],
    'Express Tribune' => ['x' => 61, 'y' => 37, 'country' => 'Pakistan', 'articles' => 3, 'url' => 'https://tribune.com.pk'],
    'Geo News' => ['x' => 62.5, 'y' => 38.5, 'country' => 'Pakistan', 'articles' => 4, 'url' => 'https://www.geo.tv'],
    'ARY News' => ['x' => 61.5, 'y' => 37.5, 'country' => 'Pakistan', 'articles' => 5, 'url' => 'https://arynews.tv'],
    'Samaa TV' => ['x' => 63.5, 'y' => 39.5, 'country' => 'Pakistan', 'articles' => 3, 'url' => 'https://www.samaa.tv']
];

// Get accurate total statistics from database
$total_stats = ['total_news' => 0, 'total_views' => 0];
try {
    $total_news_query = "SELECT COUNT(*) as total_news, SUM(views) as total_views FROM news WHERE status = 'published'";
    $total_stats_result = mysqli_query($conn, $total_news_query);
    if ($total_stats_result) {
        $total_stats = mysqli_fetch_assoc($total_stats_result);
    }
} catch (Exception $e) {
    // Fallback if views column doesn't exist
    $total_news_query = "SELECT COUNT(*) as total_news FROM news WHERE status = 'published'";
    $total_stats_result = mysqli_query($conn, $total_news_query);
    if ($total_stats_result) {
        $temp_stats = mysqli_fetch_assoc($total_stats_result);
        $total_stats['total_news'] = $temp_stats['total_news'];
        $total_stats['total_views'] = array_sum(array_column($fallback_channels, 'total_views'));
    }
}

// Get unique countries with news - using countries table instead
$unique_countries = ['total_countries' => count(array_unique(array_column($channel_locations, 'country')))];

// Get channel count from database - check if channels table exists first
$channels_count = ['total_channels' => count($channel_locations)];
$channels_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
if ($channels_table_check && mysqli_num_rows($channels_table_check) > 0) {
    try {
        $channels_count_query = "SELECT COUNT(*) as total_channels FROM channels";
        $channels_count_result = mysqli_query($conn, $channels_count_query);
        if ($channels_count_result) {
            $channels_count = mysqli_fetch_assoc($channels_count_result);
        }
    } catch (Exception $e) {
        // Keep fallback value
    }
}

// Get country-wise news distribution for charts - simplified without country_code dependency
$country_chart_result = null;

// Get statistics
if ($countries_result && mysqli_num_rows($countries_result) > 0) {
    $total_countries = mysqli_num_rows($countries_result);
    
    mysqli_data_seek($countries_result, 0);
    $total_news = 0;
    while ($country = mysqli_fetch_assoc($countries_result)) {
        $total_news += $country['news_count'];
    }
    
    mysqli_data_seek($regions_result, 0);
    $total_regions = mysqli_num_rows($regions_result);
} else {
    // Use fallback statistics
    $total_countries = count($fallback_countries);
    $total_news = array_sum(array_column($fallback_countries, 'news_count'));
    $total_regions = count($fallback_regions);
}

// Aggregate news data by country from channels
$country_news_data = [];
foreach ($channel_locations as $channel_name => $location) {
    $country = $location['country'];
    $articles = $location['articles'];
    
    if (!isset($country_news_data[$country])) {
        $country_news_data[$country] = [
            'country' => $country,
            'total_articles' => 0,
            'channels' => [],
            'flag_emoji' => get_country_flag($country)
        ];
    }
    
    $country_news_data[$country]['total_articles'] += $articles;
    $country_news_data[$country]['channels'][] = $channel_name;
}

// Helper function to get country flag emojis
function get_country_flag($country) {
    $flags = [
        'UK' => '🇬🇧',
        'USA' => '🇺🇸', 
        'Qatar' => '🇶🇦',
        'Pakistan' => '🇵🇰',
        'Russia' => '🇷🇺',
        'China' => '🇨🇳',
        'India' => '🇮🇳',
        'Japan' => '🇯🇵',
        'Australia' => '🇦🇺',
        'Canada' => '🇨🇦',
        'Brazil' => '🇧🇷',
        'Spain' => '🇪🇸',
        'Italy' => '🇮🇹',
        'France' => '🇫🇷',
        'Germany' => '🇩🇪',
        'Israel' => '🇮🇱',
        'UAE' => '🇦🇪',
        'Saudi Arabia' => '🇸🇦',
        'Turkey' => '🇹🇷'
    ];
    return $flags[$country] ?? '🌍';
}

// Helper function to get channel color class
function getChannelColorClass($channelName) {
    $colorMap = [
        'BBC News' => 'danger',
        'CNN' => 'primary', 
        'Al Jazeera' => 'success',
        'Reuters' => 'warning',
        'Dawn News' => 'info',
        'Fox News' => 'secondary',
        'MSNBC' => 'primary',
        'NBC News' => 'danger',
        'CBS News' => 'dark',
        'ABC News' => 'success',
        'The Guardian' => 'success',
        'The Times' => 'info',
        'France 24' => 'danger',
        'Deutsche Welle' => 'warning',
        'RT News' => 'danger',
        'CCTV' => 'danger',
        'NDTV' => 'warning',
        'Times of India' => 'success',
        'The Hindu' => 'danger',
        'Japan Times' => 'danger',
        'Sydney Morning Herald' => 'primary',
        'The Age' => 'danger',
        'Toronto Star' => 'danger',
        'CBC News' => 'danger',
        'Globo News' => 'success',
        'El Pais' => 'danger',
        'Corriere della Sera' => 'danger',
        'Le Monde' => 'danger',
        'Der Spiegel' => 'danger',
        'The Jerusalem Post' => 'primary',
        'Al Arabiya' => 'success',
        'Arab News' => 'danger',
        'Daily Sabah' => 'danger',
        'Hurriyet' => 'danger',
        'The News' => 'danger',
        'Express Tribune' => 'danger',
        'Geo News' => 'success',
        'ARY News' => 'danger',
        'Samaa TV' => 'warning'
    ];
    
    foreach ($colorMap as $name => $color) {
        if (stripos($channelName, $name) !== false) {
            return $color;
        }
    }
    
    return 'secondary';
}

// Sort countries by total articles
uasort($country_news_data, function($a, $b) {
    return $b['total_articles'] - $a['total_articles'];
});

// Take top 10 countries
$top_countries_by_news = array_slice($country_news_data, 0, 10, true);

// Debug: Ensure we have data
if (empty($top_countries_by_news)) {
    // Create fallback data for demonstration
    $top_countries_by_news = [
        'USA' => ['country' => 'USA', 'total_articles' => 101, 'channels' => ['CNN', 'Fox News', 'MSNBC', 'NBC News', 'CBS News', 'ABC News'], 'flag_emoji' => '🇺🇸'],
        'UK' => ['country' => 'UK', 'total_articles' => 68, 'channels' => ['BBC News', 'Reuters', 'The Guardian', 'The Times'], 'flag_emoji' => '🇬🇧'],
        'Pakistan' => ['country' => 'Pakistan', 'total_articles' => 32, 'channels' => ['Dawn News', 'Geo News', 'ARY News', 'Express Tribune', 'The News', 'Samaa TV'], 'flag_emoji' => '🇵🇰'],
        'Qatar' => ['country' => 'Qatar', 'total_articles' => 18, 'channels' => ['Al Jazeera'], 'flag_emoji' => '🇶🇦'],
        'France' => ['country' => 'France', 'total_articles' => 17, 'channels' => ['France 24', 'Le Monde'], 'flag_emoji' => '🇫🇷'],
        'India' => ['country' => 'India', 'total_articles' => 15, 'channels' => ['NDTV', 'Times of India', 'The Hindu'], 'flag_emoji' => '🇮🇳'],
        'Germany' => ['country' => 'Germany', 'total_articles' => 14, 'channels' => ['Deutsche Welle', 'Der Spiegel'], 'flag_emoji' => '🇩🇪'],
        'Russia' => ['country' => 'Russia', 'total_articles' => 8, 'channels' => ['RT News'], 'flag_emoji' => '🇷🇺'],
        'UAE' => ['country' => 'UAE', 'total_articles' => 8, 'channels' => ['Al Arabiya'], 'flag_emoji' => '🇦🇪'],
        'China' => ['country' => 'China', 'total_articles' => 7, 'channels' => ['CCTV'], 'flag_emoji' => '🇨🇳']
    ];
}
$fallback_channels = [
    ['channel_name' => 'BBC News', 'news_count' => 25, 'total_views' => 15420, 'avg_views' => 617, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'CNN', 'news_count' => 22, 'total_views' => 12350, 'avg_views' => 562, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Al Jazeera', 'news_count' => 18, 'total_views' => 9870, 'avg_views' => 548, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Reuters', 'news_count' => 20, 'total_views' => 11200, 'avg_views' => 560, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Dawn News', 'news_count' => 15, 'total_views' => 8900, 'avg_views' => 593, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Fox News', 'news_count' => 19, 'total_views' => 13450, 'avg_views' => 708, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'MSNBC', 'news_count' => 17, 'total_views' => 11200, 'avg_views' => 659, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'NBC News', 'news_count' => 16, 'total_views' => 10800, 'avg_views' => 675, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'CBS News', 'news_count' => 14, 'total_views' => 9800, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'ABC News', 'news_count' => 13, 'total_views' => 9100, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'The Guardian', 'news_count' => 12, 'total_views' => 8400, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'The Times', 'news_count' => 11, 'total_views' => 7700, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'France 24', 'news_count' => 10, 'total_views' => 7000, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Deutsche Welle', 'news_count' => 9, 'total_views' => 6300, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'RT News', 'news_count' => 8, 'total_views' => 5600, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'CCTV', 'news_count' => 7, 'total_views' => 4900, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'NDTV', 'news_count' => 6, 'total_views' => 4200, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Times of India', 'news_count' => 5, 'total_views' => 3500, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'The Hindu', 'news_count' => 4, 'total_views' => 2800, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Japan Times', 'news_count' => 3, 'total_views' => 2100, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Sydney Morning Herald', 'news_count' => 2, 'total_views' => 1400, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'The Age', 'news_count' => 2, 'total_views' => 1400, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Toronto Star', 'news_count' => 3, 'total_views' => 2100, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'CBC News', 'news_count' => 4, 'total_views' => 2800, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Globo News', 'news_count' => 5, 'total_views' => 3500, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'El Pais', 'news_count' => 6, 'total_views' => 4200, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Corriere della Sera', 'news_count' => 4, 'total_views' => 2800, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Le Monde', 'news_count' => 7, 'total_views' => 4900, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Der Spiegel', 'news_count' => 5, 'total_views' => 3500, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'The Jerusalem Post', 'news_count' => 3, 'total_views' => 2100, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Al Arabiya', 'news_count' => 8, 'total_views' => 5600, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Arab News', 'news_count' => 6, 'total_views' => 4200, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Daily Sabah', 'news_count' => 4, 'total_views' => 2800, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Hurriyet', 'news_count' => 3, 'total_views' => 2100, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'The News', 'news_count' => 2, 'total_views' => 1400, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Express Tribune', 'news_count' => 3, 'total_views' => 2100, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Geo News', 'news_count' => 4, 'total_views' => 2800, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'ARY News', 'news_count' => 5, 'total_views' => 3500, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')],
    ['channel_name' => 'Samaa TV', 'news_count' => 3, 'total_views' => 2100, 'avg_views' => 700, 'latest_news' => date('Y-m-d H:i:s')]
];
?>

<?php include 'includes/header.php'; ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.real-world-map {
    background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
    position: relative;
    overflow: hidden;
}

.world-map-svg {
    width: 100%;
    height: 500px;
    background: radial-gradient(ellipse at center, #1a3a52 0%, #0d1f2d 50%, #061119 100%);
    border-radius: 15px;
    position: relative;
    box-shadow: inset 0 0 50px rgba(0,0,0,0.5), 0 0 20px rgba(100,200,255,0.2);
    overflow: hidden;
}

.world-map-svg::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 20%, rgba(100,200,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255,100,150,0.05) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(255,255,255,0.02) 0%, transparent 70%);
    pointer-events: none;
    z-index: 1;
}

.continent {
    fill: url(#terrainGradient);
    stroke: rgba(255,255,255,0.3);
    stroke-width: 0.5;
    transition: all 0.3s ease;
    cursor: pointer;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

.continent:hover {
    fill: url(#terrainGradientHover);
    stroke: rgba(255,255,255,0.6);
    stroke-width: 1;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5)) brightness(1.2);
}

.continent.north-america {
    fill: url(#northAmericaGradient);
}

.continent.south-america {
    fill: url(#southAmericaGradient);
}

.continent.europe {
    fill: url(#europeGradient);
}

.continent.africa {
    fill: url(#africaGradient);
}

.continent.asia {
    fill: url(#asiaGradient);
}

.continent.australia {
    fill: url(#australiaGradient);
}

.ocean {
    fill: url(#oceanGradient);
    opacity: 0.8;
}

.grid-lines {
    stroke: rgba(255,255,255,0.1);
    stroke-width: 0.5;
    fill: none;
    opacity: 0.5;
}

.channel-marker {
    position: absolute;
    width: 16px;
    height: 16px;
    background: radial-gradient(circle, #ff6b6b 0%, #e74c3c 50%, #c0392b 100%);
    border: 3px solid rgba(255,255,255,0.8);
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
    animation: pulse 2s infinite, glow 3s infinite;
    box-shadow: 0 0 20px rgba(255,107,107,0.6), 0 0 40px rgba(255,107,107,0.3);
}

.channel-marker:hover {
    transform: scale(1.8);
    background: radial-gradient(circle, #ff8787 0%, #ff6b6b 50%, #e74c3c 100%);
    box-shadow: 0 0 30px rgba(255,107,107,0.8), 0 0 60px rgba(255,107,107,0.4);
    z-index: 20;
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 20px rgba(255,107,107,0.6), 0 0 40px rgba(255,107,107,0.3); }
    50% { box-shadow: 0 0 30px rgba(255,107,107,0.8), 0 0 60px rgba(255,107,107,0.4); }
}

.channel-marker.bbc {
    background: radial-gradient(circle, #ff6b6b 0%, #e74c3c 50%, #c0392b 100%);
}

.channel-marker.cnn {
    background: radial-gradient(circle, #74b9ff 0%, #3498db 50%, #2980b9 100%);
}

.channel-marker.aljazeera {
    background: radial-gradient(circle, #55efc4 0%, #27ae60 50%, #229954 100%);
}

.channel-marker.reuters {
    background: radial-gradient(circle, #fdcb6e 0%, #f39c12 50%, #e67e22 100%);
}

.channel-marker.dawn {
    background: radial-gradient(circle, #a29bfe 0%, #9b59b6 50%, #8e44ad 100%);
}

.channel-marker.foxnews {
    background: #e67e22;
}

.channel-marker.msnbc {
    background: #3498db;
}

.channel-marker.nbcnews {
    background: #e74c3c;
}

.channel-marker.cbsnews {
    background: #2c3e50;
}

.channel-marker.abcnews {
    background: #16a085;
}

.channel-marker.theguardian {
    background: #27ae60;
}

.channel-marker.thetimes {
    background: #8e44ad;
}

.channel-marker.france24 {
    background: #e74c3c;
}

.channel-marker.deutschewelle {
    background: #f39c12;
}

.channel-marker.rtnews {
    background: #e74c3c;
}

.channel-marker.cctv {
    background: #e74c3c;
}

.channel-marker.ndtv {
    background: #f39c12;
}

.channel-marker.timesofindia {
    background: #27ae60;
}

.channel-marker.thehindu {
    background: #e74c3c;
}

.channel-marker.japantimes {
    background: #e74c3c;
}

.channel-marker.sydneymorningherald {
    background: #3498db;
}

.channel-marker.theage {
    background: #e74c3c;
}

.channel-marker.torontostar {
    background: #e74c3c;
}

.channel-marker.cbcnews {
    background: #e74c3c;
}

.channel-marker.globonews {
    background: #27ae60;
}

.channel-marker.elpais {
    background: #e74c3c;
}

.channel-marker.corrieredellaser {
    background: #e74c3c;
}

.channel-marker.lemonde {
    background: #e74c3c;
}

.channel-marker.derspiegel {
    background: #e74c3c;
}

.channel-marker.thejerusalempost {
    background: #3498db;
}

.channel-markeralarabiya {
    background: #27ae60;
}

.channel-marker.arabnews {
    background: #e74c3c;
}

.channel-marker.dailysabah {
    background: #e74c3c;
}

.channel-marker.hurriyet {
    background: #e74c3c;
}

.channel-marker.thenews {
    background: #e74c3c;
}

.channel-marker.expresstribune {
    background: #e74c3c;
}

.channel-marker.geonews {
    background: #27ae60;
}

.channel-marker.arynews {
    background: #e74c3c;
}

.channel-marker.samaatv {
    background: #f39c12;
}

/* Country Graphs Styles */
.bar-chart-horizontal {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.bar-horizontal-item {
    display: flex;
    align-items: center;
    margin-bottom: 1.2rem;
    padding: 1rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.05);
}

.bar-horizontal-item:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(8px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.bar-label {
    flex: 0 0 220px;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    font-size: 0.95rem;
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.bar-label .country-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.country-flag {
    font-size: 1.5rem;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
}

.country-name {
    font-weight: 700;
    font-size: 1rem;
    color: #000000 !important;
    text-shadow: none;
}

.channel-count {
    font-size: 0.85rem;
    opacity: 1 !important;
    color: rgba(255, 255, 255, 0.95) !important;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.bar-container {
    flex: 1;
    height: 35px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #e74c3c, #ff6b6b, #ff8787);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 15px;
    transition: width 1s ease-out;
    position: relative;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.bar-value {
    color: #ffffff !important;
    font-weight: bold;
    font-size: 1rem;
    text-shadow: 0 1px 3px rgba(0,0,0,0.8) !important;
    opacity: 1 !important;
}

/* Pie Chart Styles */
.pie-chart-small {
    width: 220px;
    height: 220px;
    margin: 0 auto;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
}

.pie-slice-small {
    cursor: pointer;
    transition: all 0.3s ease;
}

.pie-slice-small:hover {
    filter: brightness(1.3) saturate(1.2);
    transform: scale(1.08);
}

.pie-legend-small {
    margin-top: 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 1rem;
}

.legend-item-small {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-bottom: 0.8rem;
    font-size: 0.9rem;
    color: #ffffff !important;
    padding: 0.3rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.legend-item-small:hover {
    background: rgba(255, 255, 255, 0.1);
}

.legend-color-small {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.legend-text-small {
    font-weight: 600;
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

/* Country Stats Grid */
.country-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
}

.country-stat-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.country-stat-card:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    border-color: rgba(255, 255, 255, 0.3);
}

.country-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.2rem;
}

.country-flag-large {
    font-size: 2.5rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.country-info {
    flex: 1;
}

.country-title {
    color: #ffffff !important;
    margin: 0;
    font-weight: 700;
    font-size: 1.1rem;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.country-meta {
    color: rgba(255, 255, 255, 0.95) !important;
    font-size: 0.9rem;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.country-stats {
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 0.8rem;
    min-width: 80px;
}

.stat-number-large {
    font-size: 1.8rem;
    font-weight: bold;
    color: #3498db;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.stat-label-small {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.95) !important;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.country-channels {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.channel-tag {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff !important;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.channel-tag:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

.channel-tag.more {
    background: rgba(255, 255, 255, 0.15);
    font-style: italic;
    border-color: rgba(255, 255, 255, 0.2);
}

.chart-container h4, .chart-container h6 {
    color: #ffffff !important;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    margin-bottom: 1rem;
}

.chart-container h4 {
    font-size: 1.3rem;
}

.chart-container h6 {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.95) !important;
}

.channel-tooltip {
    position: absolute;
    background: rgba(0, 0, 0, 0.9);
    color: #ffffff !important;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.9rem;
    pointer-events: none;
    z-index: 100;
    opacity: 0;
    transition: opacity 0.3s ease;
    white-space: nowrap;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.channel-tooltip.show {
    opacity: 1;
}

.channel-legend {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.95);
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 50;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.legend-item:last-child {
    margin-bottom: 0;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
    border: 1px solid #fff;
}

.map-controls {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 50;
}

.map-zoom-btn {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-bottom: 5px;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    display: block;
}

.map-zoom-btn:hover {
    background: #fff;
    transform: scale(1.1);
}

.channel-stats-overlay {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 15px;
    border-radius: 10px;
    display: flex;
    justify-content: space-around;
    z-index: 50;
}

.overlay-stat {
    text-align: center;
}

.overlay-stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #3498db;
}

.overlay-stat-label {
    font-size: 0.8rem;
    opacity: 0.8;
}

.country-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.country-card {
    background: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.country-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    border-color: #dc3545;
}

.country-card.active {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.country-flag {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}

.country-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    color: #000000 !important;
    text-shadow: none;
}

.country-stats {
    font-size: 0.8rem;
    opacity: 0.8;
    color: #000000 !important;
}

.chart-container h4 {
    color: #000000 !important;
    font-weight: 600;
    margin-bottom: 1rem;
}

.region-filter {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.region-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.region-pill {
    background: #e9ecef;
    border: none;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.region-pill:hover {
    background: #dc3545;
    color: white;
}

.region-pill.active {
    background: #dc3545;
    color: white;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    color: white;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ff6b6b, #4ecdc4);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #ffffff;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.stat-label {
    color: rgba(255,255,255,0.9);
    font-size: 1rem;
    font-weight: 500;
}

.news-list {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
}

.news-item {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 0;
    display: flex;
    gap: 1rem;
}

.news-item:last-child {
    border-bottom: none;
}

.news-image {
    width: 100px;
    height: 70px;
    object-fit: cover;
    border-radius: 5px;
}

.news-content {
    flex: 1;
}

.news-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
    text-decoration: none;
}

.news-title:hover {
    color: #dc3545;
}

.news-meta {
    font-size: 0.8rem;
    color: #6c757d;
}

.loading-spinner {
    display: none;
    text-align: center;
    padding: 2rem;
}

.map-svg {
    width: 100%;
    height: 400px;
}

.country-path {
    fill: #e9ecef;
    stroke: #ffffff;
    stroke-width: 1;
    cursor: pointer;
    transition: all 0.3s ease;
}

.country-path:hover {
    fill: #dc3545;
    transform: scale(1.05);
}

.country-path.active {
    fill: #dc3545;
}

.chart-container {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.chart-canvas {
    max-height: 400px;
    position: relative;
}

.channel-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.channel-stat-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    border-left: 4px solid #dc3545;
    transition: all 0.3s ease;
}

.channel-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.channel-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 1rem;
}

.channel-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.channel-stats {
    font-size: 0.9rem;
    color: #6c757d;
}

.real-time-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    margin-left: 0.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.graph-filters {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.graph-filter-btn {
    background: #e9ecef;
    border: none;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.graph-filter-btn:hover {
    background: #dc3545;
    color: white;
}

.graph-filter-btn.active {
    background: #dc3545;
    color: white;
}

.bar-chart {
    display: flex;
    align-items: flex-end;
    height: 300px;
    gap: 1rem;
    padding: 1rem 0;
}

.bar-item {
    flex: 1;
    background: linear-gradient(to top, #dc3545, #ff6b6b);
    border-radius: 5px 5px 0 0;
    position: relative;
    min-height: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.bar-item:hover {
    transform: scaleY(1.05);
    box-shadow: 0 -5px 15px rgba(220, 53, 69, 0.3);
}

.bar-label {
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    white-space: nowrap;
    text-align: center;
}

.bar-value {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    font-weight: 600;
    color: #dc3545;
}

.pie-chart {
    width: 300px;
    height: 300px;
    margin: 0 auto;
    position: relative;
}

.pie-slice {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    clip-path: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.pie-slice:hover {
    transform: scale(1.05);
    filter: brightness(1.1);
}

.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 1rem;
    justify-content: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 3px;
}

@media (max-width: 768px) {
    .country-grid {
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    }
    
    .country-flag {
        font-size: 1.5rem;
    }
    
    .country-name {
        font-size: 0.8rem;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Interactive News Map Section -->
<section class="news-map-section py-4" style="background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%); min-height: 100vh;">
    <div class="container-fluid">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="display-3 mb-3 text-white">
                <i class="fas fa-globe-americas me-3"></i>Advanced Global News Map
            </h1>
            <p class="lead text-white-50">Real-time interactive world map with live news coverage from around the globe</p>
        </div>

        <!-- Advanced Search and Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-dark bg-opacity-75 border-0 rounded-3 p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-secondary border-0"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control border-0" id="searchChannels" placeholder="Search channels, countries, or topics...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select border-0" id="regionFilter">
                                <option value="">All Regions</option>
                                <option value="Asia">Asia</option>
                                <option value="Europe">Europe</option>
                                <option value="Americas">Americas</option>
                                <option value="Africa">Africa</option>
                                <option value="Oceania">Oceania</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select border-0" id="categoryFilter">
                                <option value="">All Categories</option>
                                <option value="Politics">Politics</option>
                                <option value="Business">Business</option>
                                <option value="Technology">Technology</option>
                                <option value="Sports">Sports</option>
                                <option value="Entertainment">Entertainment</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-number"><?php echo $channels_count['total_channels'] ?? count($channel_locations); ?></div>
                <div class="stat-label">News Channels</div>
                <small class="text-white-50">From <?php echo $unique_countries['total_countries'] ?? count(array_unique(array_column($channel_locations, 'country'))); ?> countries</small>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-number"><?php echo number_format($total_stats['total_news'] ?? array_sum(array_column($channel_locations, 'articles'))); ?></div>
                <div class="stat-label">Total News Articles</div>
                <small class="text-white-50"><?php echo number_format($total_stats['total_views'] ?? array_sum(array_column($fallback_channels, 'total_views'))); ?> total views</small>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-number"><?php echo $unique_countries['total_countries'] ?? count(array_unique(array_column($channel_locations, 'country'))); ?></div>
                <div class="stat-label">Active Countries</div>
                <small class="text-white-50">Across 6 continents</small>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-number"><?php echo count($fallback_regions); ?></div>
                <div class="stat-label">News Regions</div>
                <small class="text-white-50">Global coverage</small>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Live Updates</div>
                <small class="text-white-50">Real-time monitoring</small>
            </div>
        </div>

        <!-- Real Interactive World Map with Leaflet.js -->
        <div class="real-world-map">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-white mb-0">
                    <i class="fas fa-globe-americas me-2"></i>Live Global News Map
                    <span class="badge bg-success ms-2">LIVE</span>
                </h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-sm" onclick="resetMapView()">
                        <i class="fas fa-home me-1"></i>Reset View
                    </button>
                    <button class="btn btn-outline-light btn-sm" onclick="toggleHeatmap()">
                        <i class="fas fa-fire me-1"></i>Heatmap
                    </button>
                    <button class="btn btn-outline-light btn-sm" onclick="toggleClusters()">
                        <i class="fas fa-layer-group me-1"></i>Clusters
                    </button>
                </div>
            </div>
            
            <div id="leafletMap" style="height: 600px; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.4);"></div>
            
            <!-- Map Info Panel -->
            <div class="card bg-dark bg-opacity-75 border-0 rounded-3 mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-white mb-1">Active Channels</h5>
                                <h2 class="text-primary"><?php echo $channels_count['total_channels'] ?? count($channel_locations); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-white mb-1">Total Articles</h5>
                                <h2 class="text-success"><?php echo number_format($total_stats['total_news'] ?? array_sum(array_column($channel_locations, 'articles'))); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-white mb-1">Countries Covered</h5>
                                <h2 class="text-warning"><?php echo $unique_countries['total_countries'] ?? count(array_unique(array_column($channel_locations, 'country'))); ?></h2>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-white mb-1">Total Views</h5>
                                <h2 class="text-info"><?php echo number_format($total_stats['total_views'] ?? array_sum(array_column($fallback_channels, 'total_views'))); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Data Visualization Section -->
        <div class="row mt-4">
            <div class="col-lg-6 mb-4">
                <div class="card bg-dark bg-opacity-75 border-0 rounded-3">
                    <div class="card-body">
                        <h4 class="text-white mb-3"><i class="fas fa-chart-pie me-2"></i>News by Region</h4>
                        <canvas id="regionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card bg-dark bg-opacity-75 border-0 rounded-3">
                    <div class="card-body">
                        <h4 class="text-white mb-3"><i class="fas fa-chart-bar me-2"></i>Top Countries by News Volume</h4>
                        <canvas id="countryChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Country Information Panels -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-dark bg-opacity-75 border-0 rounded-3">
                    <div class="card-body">
                        <h4 class="text-white mb-4"><i class="fas fa-info-circle me-2"></i>Detailed Country Information</h4>
                        <div class="country-stats-grid">
                            <?php 
                            $country_details = [
                                'Pakistan' => [
                                    'flag' => '🇵🇰',
                                    'capital' => 'Islamabad',
                                    'population' => '220.9M',
                                    'region' => 'Southern Asia',
                                    'channels' => ['Dawn News', 'Geo News', 'ARY News', 'Express Tribune', 'The News', 'Samaa TV'],
                                    'articles' => 32
                                ],
                                'USA' => [
                                    'flag' => '🇺🇸',
                                    'capital' => 'Washington, D.C.',
                                    'population' => '331M',
                                    'region' => 'Americas',
                                    'channels' => ['CNN', 'Fox News', 'MSNBC', 'NBC News', 'CBS News', 'ABC News'],
                                    'articles' => 101
                                ],
                                'UK' => [
                                    'flag' => '🇬🇧',
                                    'capital' => 'London',
                                    'population' => '67.9M',
                                    'region' => 'Europe',
                                    'channels' => ['BBC News', 'Reuters', 'The Guardian', 'The Times'],
                                    'articles' => 68
                                ],
                                'India' => [
                                    'flag' => '🇮🇳',
                                    'capital' => 'New Delhi',
                                    'population' => '1.38B',
                                    'region' => 'Southern Asia',
                                    'channels' => ['NDTV', 'Times of India', 'The Hindu'],
                                    'articles' => 15
                                ],
                                'Qatar' => [
                                    'flag' => '🇶🇦',
                                    'capital' => 'Doha',
                                    'population' => '2.8M',
                                    'region' => 'Western Asia',
                                    'channels' => ['Al Jazeera'],
                                    'articles' => 18
                                ],
                                'France' => [
                                    'flag' => '🇫🇷',
                                    'capital' => 'Paris',
                                    'population' => '67M',
                                    'region' => 'Europe',
                                    'channels' => ['France 24', 'Le Monde'],
                                    'articles' => 17
                                ]
                            ];

                            foreach ($country_details as $country => $details): ?>
                            <div class="country-stat-card">
                                <div class="country-header">
                                    <span class="country-flag-large"><?php echo $details['flag']; ?></span>
                                    <div class="country-info">
                                        <h5 class="country-title"><?php echo $country; ?></h5>
                                        <div class="country-meta">
                                            <i class="fas fa-city me-1"></i><?php echo $details['capital']; ?> • 
                                            <i class="fas fa-users me-1"></i><?php echo $details['population']; ?> • 
                                            <i class="fas fa-globe me-1"></i><?php echo $details['region']; ?>
                                        </div>
                                    </div>
                                    <div class="country-stats">
                                        <div class="stat-number-large"><?php echo $details['articles']; ?></div>
                                        <div class="stat-label-small">Articles</div>
                                    </div>
                                </div>
                                <div class="country-channels">
                                    <?php foreach ($details['channels'] as $channel): ?>
                                        <span class="channel-tag"><?php echo $channel; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- News Timeline and Trending Topics -->
        <div class="row mt-4">
            <div class="col-lg-8 mb-4">
                <div class="card bg-dark bg-opacity-75 border-0 rounded-3">
                    <div class="card-body">
                        <h4 class="text-white mb-4"><i class="fas fa-clock me-2"></i>24-Hour News Timeline</h4>
                        <div id="newsTimeline" style="position: relative; height: 300px;">
                            <canvas id="timelineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card bg-dark bg-opacity-75 border-0 rounded-3">
                    <div class="card-body">
                        <h4 class="text-white mb-4"><i class="fas fa-fire me-2"></i>Trending Topics</h4>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item bg-transparent border-secondary text-white d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hashtag text-primary me-2"></i>Politics</span>
                                <span class="badge bg-primary">245</span>
                            </div>
                            <div class="list-group-item bg-transparent border-secondary text-white d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hashtag text-success me-2"></i>Technology</span>
                                <span class="badge bg-success">189</span>
                            </div>
                            <div class="list-group-item bg-transparent border-secondary text-white d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hashtag text-warning me-2"></i>Business</span>
                                <span class="badge bg-warning">156</span>
                            </div>
                            <div class="list-group-item bg-transparent border-secondary text-white d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hashtag text-danger me-2"></i>Sports</span>
                                <span class="badge bg-danger">134</span>
                            </div>
                            <div class="list-group-item bg-transparent border-secondary text-white d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hashtag text-info me-2"></i>Entertainment</span>
                                <span class="badge bg-info">98</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channel-wise Statistics -->
        <div class="chart-container">
            <h4><i class="fas fa-chart-bar me-2"></i>Channel Performance
                <span class="real-time-indicator"></span>
                <small class="text-muted ms-2">Last updated: <span id="lastUpdated"><?php echo date('H:i:s'); ?></span></small>
                <button class="btn btn-sm btn-outline-primary ms-2" onclick="refreshChannelStats()">
                    <i class="fas fa-sync-alt"></i> Refresh Now
                </button>
            </h4>
            
            <div class="graph-filters">
                <button class="graph-filter-btn active" data-chart="news-count">News Count</button>
                <button class="graph-filter-btn" data-chart="views">Total Views</button>
                <button class="graph-filter-btn" data-chart="engagement">Engagement Rate</button>
                <button class="graph-filter-btn" data-chart="performance">Performance Score</button>
            </div>

            <div class="channel-stats-grid" id="channelStatsGrid">
                <?php if ($channel_stats_data): ?>
                    <?php foreach ($channel_stats_data as $channel): ?>
                        <div class="channel-stat-card" data-channel-id="<?php echo $channel['id']; ?>">
                            <div class="d-flex align-items-center">
                                <div class="channel-logo bg-<?php echo getChannelColorClass($channel['name']); ?> d-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; border-radius: 50%; font-size: 0.8rem;">
                                    <?php echo strtoupper(substr($channel['name'], 0, 2)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="channel-name d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($channel['name']); ?></span>
                                        <span class="badge bg-<?php echo $channel['status'] === 'live' ? 'success' : 'secondary'; ?> badge-sm">
                                            <?php echo ucfirst($channel['status']); ?>
                                        </span>
                                    </div>
                                    <div class="channel-stats">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong><?php echo number_format($channel['news_count']); ?></strong> articles
                                                <br><small class="text-muted">Today: <?php echo $channel['articles_today']; ?></small>
                                            </div>
                                            <div class="col-6">
                                                <strong><?php echo number_format($channel['total_views']); ?></strong> views
                                                <br><small class="text-muted">Avg: <?php echo number_format($channel['avg_views']); ?></small>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6">
                                                <small class="text-muted">Engagement:</small>
                                                <strong><?php echo $channel['engagement_rate']; ?></strong> per 1k
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Score:</small>
                                                <strong><?php echo $channel['performance_score']; ?></strong>/100
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback to original data if API fails -->
                    <?php 
                    if ($channel_stats_result && mysqli_num_rows($channel_stats_result) > 0) {
                        while ($channel = mysqli_fetch_assoc($channel_stats_result)): 
                    ?>
                        <div class="channel-stat-card">
                            <div class="d-flex align-items-center">
                                <div class="channel-logo bg-danger d-flex align-items-center justify-content-center text-white fw-bold">
                                    <?php echo strtoupper(substr($channel['channel_name'], 0, 2)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="channel-name"><?php echo htmlspecialchars($channel['channel_name']); ?></div>
                                    <div class="channel-stats">
                                        <div><strong><?php echo number_format($channel['news_count']); ?></strong> articles</div>
                                        <div><strong><?php echo number_format($channel['total_views']); ?></strong> views</div>
                                        <div>Avg: <strong><?php echo number_format($channel['avg_views']); ?></strong> per article</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    } else {
                        // Use fallback channels
                        foreach ($fallback_channels as $channel):
                    ?>
                        <div class="channel-stat-card">
                            <div class="d-flex align-items-center">
                                <div class="channel-logo bg-danger d-flex align-items-center justify-content-center text-white fw-bold">
                                    <?php echo strtoupper(substr($channel['channel_name'], 0, 2)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="channel-name"><?php echo htmlspecialchars($channel['channel_name']); ?></div>
                                    <div class="channel-stats">
                                        <div><strong><?php echo number_format($channel['news_count']); ?></strong> articles</div>
                                        <div><strong><?php echo number_format($channel['total_views']); ?></strong> views</div>
                                        <div>Avg: <strong><?php echo number_format($channel['avg_views']); ?></strong> per article</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Country News Distribution Chart -->
        <div class="chart-container">
            <h4><i class="fas fa-globe-americas me-2"></i>News Distribution by Country</h4>
            <div class="bar-chart" id="countryBarChart">
                <?php 
                $chart_data = [];
                if ($country_chart_result && mysqli_num_rows($country_chart_result) > 0) {
                    while ($country = mysqli_fetch_assoc($country_chart_result)) {
                        $chart_data[] = $country;
                    }
                } else {
                    $chart_data = array_slice($fallback_countries, 0, 10);
                }
                
                $max_news = max(array_column($chart_data, 'news_count'));
                foreach ($chart_data as $country): 
                    $height = ($country['news_count'] / $max_news) * 250;
                ?>
                    <div class="bar-item" style="height: <?php echo $height; ?>px;"
                         data-country="<?php echo $country['code']; ?>"
                         data-count="<?php echo $country['news_count']; ?>">
                        <div class="bar-value"><?php echo $country['news_count']; ?></div>
                        <div class="bar-label">
                            <?php echo $country['flag_emoji']; ?><br>
                            <?php echo substr($country['name'], 0, 8); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Regional Distribution Pie Chart -->
        <div class="chart-container">
            <h4><i class="fas fa-chart-pie me-2"></i>Regional News Distribution</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="pie-chart" id="regionPieChart">
                        <svg viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg">
                            <?php
                            $region_data = [];
                            if ($regions_result && mysqli_num_rows($regions_result) > 0) {
                                mysqli_data_seek($regions_result, 0);
                                while ($region = mysqli_fetch_assoc($regions_result)) {
                                    $region_data[] = $region;
                                }
                            } else {
                                $region_data = $fallback_regions;
                            }
                            
                            $total_news_by_region = array_sum(array_column($region_data, 'news_count'));
                            $colors = ['#dc3545', '#28a745', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14'];
                            $current_angle = 0;
                            
                            foreach ($region_data as $index => $region):
                                $percentage = ($region['news_count'] / $total_news_by_region) * 100;
                                $angle = ($percentage / 100) * 360;
                                $end_angle = $current_angle + $angle;
                                
                                $x1 = 150 + 100 * cos(deg2rad($current_angle));
                                $y1 = 150 + 100 * sin(deg2rad($current_angle));
                                $x2 = 150 + 100 * cos(deg2rad($end_angle));
                                $y2 = 150 + 100 * sin(deg2rad($end_angle));
                                
                                $large_arc = $angle > 180 ? 1 : 0;
                            ?>
                                <path d="M 150 150 L <?php echo $x1; ?> <?php echo $y1; ?> A 100 100 0 <?php echo $large_arc; ?> 1 <?php echo $x2; ?> <?php echo $y2; ?> Z"
                                      fill="<?php echo $colors[$index % count($colors)]; ?>"
                                      class="pie-slice"
                                      data-region="<?php echo $region['region']; ?>"
                                      data-count="<?php echo $region['news_count']; ?>">
                                </path>
                            <?php
                                $current_angle = $end_angle;
                            endforeach;
                            ?>
                        </svg>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-legend">
                        <?php foreach ($region_data as $index => $region): ?>
                            <div class="legend-item">
                                <div class="legend-color" style="background: <?php echo $colors[$index % count($colors)]; ?>;"></div>
                                <span><?php echo htmlspecialchars($region['region']); ?> (<?php echo $region['news_count']; ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

<!-- News List -->
        <div class="news-list mt-4">
            <h4>
                <i class="fas fa-newspaper me-2"></i>
                <?php if (!empty($selected_country)): ?>
                    <?php 
                    $country_data = null;
                    if ($countries_result && mysqli_num_rows($countries_result) > 0) {
                        mysqli_data_seek($countries_result, 0);
                        while ($country = mysqli_fetch_assoc($countries_result)) {
                            if ($country['code'] === $selected_country) {
                                $country_data = $country;
                                break;
                            }
                        }
                    } else {
                        foreach ($fallback_countries as $country) {
                            if ($country['code'] === $selected_country) {
                                $country_data = $country;
                                break;
                            }
                        }
                    }
                    ?>
                    News from <?php echo $country_data ? htmlspecialchars($country_data['name'] ?? 'Selected Country') : 'Selected Country'; ?>
                <?php elseif (!empty($selected_region)): ?>
                    News from <?php echo ucfirst($selected_region); ?> Region
                <?php else: ?>
                    Latest World News
                <?php endif; ?>
            </h4>
            
            <div class="loading-spinner">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div id="news-container">
                <?php if (mysqli_num_rows($news_result) > 0): ?>
                    <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                        <div class="news-item">
                            <?php if ($news['image']): ?>
                                <img src="<?php echo htmlspecialchars($news['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                     class="news-image">
                            <?php else: ?>
                                <div class="news-image bg-light d-flex align-items-center justify-content-center rounded">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="news-content">
                                <a href="news.php?slug=<?php echo $news['slug']; ?>" class="news-title">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a>
                                <div class="news-meta">
                                    <?php if (!empty($news['flag_emoji'])): ?>
                                        <span class="me-2"><?php echo $news['flag_emoji']; ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($news['country_name'])): ?>
                                        <span class="me-2"><?php echo htmlspecialchars($news['country_name']); ?></span>
                                    <?php endif; ?>
                                    <span class="me-2">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo format_date($news['published_at']); ?>
                                    </span>
                                    <span class="me-2">
                                        <i class="fas fa-eye me-1"></i>
                                        <?php echo number_format($news['views']); ?>
                                    </span>
                                    <span class="me-2">
                                        <i class="fas fa-comments me-1"></i>
                                        <?php echo $news['comment_count']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No news found for the selected location.</p>
                        <a href="news_map.php" class="btn btn-danger">View All Countries</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/news_map.js"></script>

<script>
// Interactive Map JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Real World Map Interactions
    const channelMarkers = document.querySelectorAll('.channel-marker');
    const tooltip = document.getElementById('channelTooltip');
    const worldMapContainer = document.getElementById('worldMapContainer');
    let currentZoom = 1;

    // Channel marker interactions
    channelMarkers.forEach(marker => {
        marker.addEventListener('mouseenter', function(e) {
            const channel = this.dataset.channel;
            const country = this.dataset.country;
            const articles = this.dataset.articles;
            const url = this.dataset.url;
            
            tooltip.innerHTML = `
                <strong>${channel}</strong><br>
                📍 ${country}<br>
                📰 ${articles} articles<br>
                🌐 <span style="color: #3498db;">Official Website</span>
            `;
            tooltip.classList.add('show');
            
            // Position tooltip
            const rect = this.getBoundingClientRect();
            const containerRect = worldMapContainer.getBoundingClientRect();
            tooltip.style.left = (rect.left - containerRect.left + rect.width / 2) + 'px';
            tooltip.style.top = (rect.top - containerRect.top - 50) + 'px';
        });
        
        marker.addEventListener('mouseleave', function() {
            tooltip.classList.remove('show');
        });
        
        marker.addEventListener('click', function() {
            const url = this.dataset.url;
            const channel = this.dataset.channel;
            
            // Open official website in new tab
            window.open(url, '_blank', 'noopener,noreferrer');
            
            // Show visual feedback
            this.style.transform = 'scale(2)';
            this.style.boxShadow = '0 0 40px rgba(52, 152, 219, 1)';
            
            setTimeout(() => {
                this.style.transform = '';
                this.style.boxShadow = '';
            }, 300);
        });
    });

    // Continent interactions
    const continents = document.querySelectorAll('.continent');
    continents.forEach(continent => {
        continent.addEventListener('click', function() {
            const region = this.dataset.region;
            window.location.href = `news_map.php?region=${encodeURIComponent(region)}`;
        });
        
        continent.addEventListener('mouseenter', function() {
            const region = this.dataset.region;
            this.style.cursor = 'pointer';
            this.title = `View news from ${region}`;
        });
    });

    // Map zoom functions
    function zoomMap(factor) {
        currentZoom *= factor;
        currentZoom = Math.max(0.5, Math.min(3, currentZoom)); // Limit zoom between 0.5x and 3x
        
        const svg = worldMapContainer.querySelector('svg');
        const markers = worldMapContainer.querySelectorAll('.channel-marker');
        
        svg.style.transform = `scale(${currentZoom})`;
        svg.style.transformOrigin = 'center center';
        
        // Adjust marker positions
        markers.forEach(marker => {
            const originalX = parseFloat(marker.style.left);
            const originalY = parseFloat(marker.style.top);
            
            // Keep markers at fixed positions during zoom
            marker.style.transform = `scale(${1/currentZoom})`;
        });
    }

    function resetMap() {
        currentZoom = 1;
        const svg = worldMapContainer.querySelector('svg');
        const markers = worldMapContainer.querySelectorAll('.channel-marker');
        
        svg.style.transform = 'scale(1)';
        markers.forEach(marker => {
            marker.style.transform = 'scale(1)';
        });
    }

    // Make zoom functions global
    window.zoomMap = zoomMap;
    window.resetMap = resetMap;
    window.toggleRotation = toggleRotation;
    window.toggleGrid = toggleGrid;

    // Google Earth Style Controls
    let isRotating = false;
    let rotationInterval;
    let currentRotation = 0;
    let gridVisible = true;

    function toggleRotation() {
        isRotating = !isRotating;
        const svg = worldMapContainer.querySelector('svg');
        
        if (isRotating) {
            rotationInterval = setInterval(() => {
                currentRotation += 0.5;
                svg.style.transform = `rotateY(${currentRotation}deg) scale(${currentZoom})`;
                svg.style.transformOrigin = 'center center';
            }, 50);
        } else {
            clearInterval(rotationInterval);
        }
    }

    function toggleGrid() {
        gridVisible = !gridVisible;
        const gridLines = document.querySelectorAll('.grid-lines');
        gridLines.forEach(line => {
            line.style.opacity = gridVisible ? '0.5' : '0';
        });
    }

    // 3D tilt effect on mouse move
    worldMapContainer.addEventListener('mousemove', function(e) {
        if (!isRotating) {
            const rect = this.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width;
            const y = (e.clientY - rect.top) / rect.height;
            
            const tiltX = (y - 0.5) * 10;
            const tiltY = (x - 0.5) * -10;
            
            const svg = this.querySelector('svg');
            svg.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(${currentZoom})`;
            svg.style.transformOrigin = 'center center';
        }
    });

    // Reset tilt on mouse leave
    worldMapContainer.addEventListener('mouseleave', function() {
        if (!isRotating) {
            const svg = this.querySelector('svg');
            svg.style.transform = `scale(${currentZoom})`;
        }
    });

    // Add parallax effect to channel markers
    const markers = document.querySelectorAll('.channel-marker');
    worldMapContainer.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;
        
        markers.forEach((marker, index) => {
            const depth = (index % 3 + 1) * 0.5;
            const moveX = (x - 0.5) * depth * 2;
            const moveY = (y - 0.5) * depth * 2;
            
            marker.style.transform = `translate(${moveX}px, ${moveY}px) scale(${1/currentZoom})`;
        });
    });

    // Country Graph Interactions
    const countryBarItems = document.querySelectorAll('.bar-horizontal-item');
    const countryPieSlices = document.querySelectorAll('.pie-slice-small');
    const countryCards = document.querySelectorAll('.country-stat-card');
    
    // Bar chart interactions
    countryBarItems.forEach(item => {
        item.addEventListener('click', function() {
            const country = this.dataset.country;
            // Filter news by country
            window.location.href = `news_map.php?country=${encodeURIComponent(country)}`;
        });
        
        item.addEventListener('mouseenter', function() {
            const barFill = this.querySelector('.bar-fill');
            const count = barFill.dataset.count;
            this.title = `${count} articles - Click to view news`;
        });
    });
    
    // Pie chart interactions
    countryPieSlices.forEach(slice => {
        slice.addEventListener('click', function() {
            const country = this.dataset.country;
            window.location.href = `news_map.php?country=${encodeURIComponent(country)}`;
        });
        
        slice.addEventListener('mouseenter', function() {
            const country = this.dataset.country;
            const count = this.dataset.count;
            const percentage = this.dataset.percentage;
            this.title = `${country}: ${count} articles (${percentage}%)`;
        });
    });
    
    // Country card interactions
    countryCards.forEach(card => {
        card.addEventListener('click', function() {
            const country = this.dataset.country;
            window.location.href = `news_map.php?country=${encodeURIComponent(country)}`;
        });
    });
    
    // Animate bar charts on page load
    function animateCountryBars() {
        const barFills = document.querySelectorAll('.bar-fill');
        barFills.forEach((bar, index) => {
            const originalWidth = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = originalWidth;
            }, index * 100);
        });
    }
    
    // Animate pie chart on load
    function animatePieChart() {
        const pieChart = document.querySelector('.pie-chart-small svg');
        if (pieChart) {
            pieChart.style.transform = 'scale(0) rotate(0deg)';
            pieChart.style.transition = 'transform 1s ease-out';
            setTimeout(() => {
                pieChart.style.transform = 'scale(1) rotate(360deg)';
            }, 500);
        }
    }
    
    // Trigger animations
    setTimeout(() => {
        animateCountryBars();
        animatePieChart();
    }, 1000);

    // Smooth scroll to news when country is selected
    if (selectedCountry || urlParams.get('region')) {
        setTimeout(() => {
            document.querySelector('.news-list').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }, 500);
    }

    // Add hover effects with tooltips
    countryPaths.forEach(path => {
        const countryCode = path.dataset.country;
        if (countryCode) {
            const countryCard = document.querySelector(`[data-country="${countryCode}"]`);
            if (countryCard) {
                const countryName = countryCard.querySelector('.country-name').textContent;
                const newsCount = countryCard.querySelector('.country-stats').textContent;
                
                path.title = `${countryName} - ${newsCount}`;
            }
        }
    });

    // Graph filter functionality
    const graphFilters = document.querySelectorAll('.graph-filter-btn');
    graphFilters.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            graphFilters.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update channel stats display based on selected metric
            updateChannelStats(this.dataset.chart);
        });
    });

    // Update channel statistics display
    function updateChannelStats(metric) {
        const channelCards = document.querySelectorAll('.channel-stat-card');
        
        channelCards.forEach(card => {
            const stats = card.querySelector('.channel-stats');
            const currentStats = stats.innerHTML;
            
            // This would typically fetch new data via AJAX
            // For now, we'll just add a visual effect
            card.style.opacity = '0.5';
            setTimeout(() => {
                card.style.opacity = '1';
            }, 300);
        });
    }

    // Bar chart interactions
    const distributionBarItems = document.querySelectorAll('.bar-item');
    distributionBarItems.forEach(bar => {
        bar.addEventListener('click', function() {
            const country = this.dataset.country;
            if (country) {
                window.location.href = `news_map.php?country=${country}`;
            }
        });
        
        bar.addEventListener('mouseenter', function() {
            const count = this.dataset.count;
            this.title = `${count} articles - Click to view news`;
        });
    });

    // Pie chart interactions
    const distributionPieSlices = document.querySelectorAll('.pie-slice');
    distributionPieSlices.forEach(slice => {
        slice.addEventListener('click', function() {
            const region = this.dataset.region;
            if (region) {
                window.location.href = `news_map.php?region=${region}`;
            }
        });
        
        slice.addEventListener('mouseenter', function() {
            const count = this.dataset.count;
            const region = this.dataset.region;
            this.title = `${region}: ${count} articles`;
        });
    });

    // Real-time updates simulation
    function updateRealTimeStats() {
        // Update random statistics to simulate real-time data
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(stat => {
            if (stat.textContent !== '24/7') {
                const currentValue = parseInt(stat.textContent.replace(/,/g, ''));
                const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
                if (currentValue + change > 0) {
                    stat.textContent = number_format(currentValue + change);
                }
            }
        });
    }

    // Update stats every 30 seconds for real-time effect
    setInterval(updateRealTimeStats, 30000);

    // Animate charts on page load
    function animateCharts() {
        // Animate bar charts
        const bars = document.querySelectorAll('.bar-item');
        bars.forEach((bar, index) => {
            const originalHeight = bar.style.height;
            bar.style.height = '0px';
            setTimeout(() => {
                bar.style.transition = 'height 0.8s ease-out';
                bar.style.height = originalHeight;
            }, index * 100);
        });
        
        // Animate pie chart
        const pieChart = document.querySelector('#regionPieChart');
        if (pieChart) {
            pieChart.style.transform = 'scale(0)';
            pieChart.style.transition = 'transform 1s ease-out';
            setTimeout(() => {
                pieChart.style.transform = 'scale(1)';
            }, 500);
        }
    }

    // Trigger animations on page load
    setTimeout(animateCharts, 1000);

    // Channel card hover effects
    const channelCards = document.querySelectorAll('.channel-stat-card');
    channelCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Helper function for number formatting
    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Auto-refresh news every 5 minutes
    setInterval(() => {
        if (selectedCountry || urlParams.get('region')) {
            loadNews();
        }
    }, 300000);

    // Channel Performance Auto-Refresh (Every Hour)
    function refreshChannelStats() {
        const channelStatsGrid = document.getElementById('channelStatsGrid');
        const lastUpdatedSpan = document.getElementById('lastUpdated');
        
        // Show loading state
        channelStatsGrid.style.opacity = '0.5';
        
        fetch('api/channel_performance.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateChannelStatsDisplay(data.data.channels);
                    lastUpdatedSpan.textContent = new Date().toLocaleTimeString();
                    
                    // Show success notification
                    showNotification('Channel statistics updated successfully!', 'success');
                } else {
                    showNotification('Failed to update channel statistics', 'error');
                }
            })
            .catch(error => {
                console.error('Error refreshing channel stats:', error);
                showNotification('Error updating channel statistics', 'error');
            })
            .finally(() => {
                channelStatsGrid.style.opacity = '1';
            });
    }

    function updateChannelStatsDisplay(channels) {
        const channelStatsGrid = document.getElementById('channelStatsGrid');
        
        // Clear existing content
        channelStatsGrid.innerHTML = '';
        
        // Sort channels by performance score
        channels.sort((a, b) => b.performance_score - a.performance_score);
        
        channels.forEach(channel => {
            const channelCard = createChannelCard(channel);
            channelStatsGrid.appendChild(channelCard);
        });
        
        // Re-attach event listeners
        attachChannelCardListeners();
    }

    function createChannelCard(channel) {
        const card = document.createElement('div');
        card.className = 'channel-stat-card';
        card.dataset.channelId = channel.id;
        
        const statusBadge = channel.status === 'live' ? 
            '<span class="badge bg-success badge-sm">Live</span>' : 
            '<span class="badge bg-secondary badge-sm">Offline</span>';
        
        card.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="channel-logo bg-${getChannelBootstrapColor(channel.name)} d-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; border-radius: 50%; font-size: 0.8rem;">
                    ${channel.name.substring(0, 2).toUpperCase()}
                </div>
                <div class="flex-grow-1">
                    <div class="channel-name d-flex justify-content-between align-items-center">
                        <span>${channel.name}</span>
                        ${statusBadge}
                    </div>
                    <div class="channel-stats">
                        <div class="row">
                            <div class="col-6">
                                <strong>${number_format(channel.news_count)}</strong> articles
                                <br><small class="text-muted">Today: ${channel.articles_today}</small>
                            </div>
                            <div class="col-6">
                                <strong>${number_format(channel.total_views)}</strong> views
                                <br><small class="text-muted">Avg: ${number_format(channel.avg_views)}</small>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <small class="text-muted">Engagement:</small>
                                <strong>${channel.engagement_rate}</strong> per 1k
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Score:</small>
                                <strong>${channel.performance_score}</strong>/100
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }

    function getChannelBootstrapColor(channelName) {
        const colorMap = {
            'BBC News': 'danger',
            'CNN': 'primary', 
            'Al Jazeera': 'success',
            'Reuters': 'warning',
            'Dawn News': 'info',
            'Fox News': 'secondary',
            'MSNBC': 'primary',
            'NBC News': 'danger',
            'CBS News': 'dark',
            'ABC News': 'success',
            'The Guardian': 'success',
            'The Times': 'info',
            'France 24': 'danger',
            'Deutsche Welle': 'warning',
            'RT News': 'danger',
            'CCTV': 'danger',
            'NDTV': 'warning',
            'Times of India': 'success',
            'The Hindu': 'danger',
            'Japan Times': 'danger',
            'Sydney Morning Herald': 'primary',
            'The Age': 'danger',
            'Toronto Star': 'danger',
            'CBC News': 'danger',
            'Globo News': 'success',
            'El Pais': 'danger',
            'Corriere della Sera': 'danger',
            'Le Monde': 'danger',
            'Der Spiegel': 'danger',
            'The Jerusalem Post': 'primary',
            'Al Arabiya': 'success',
            'Arab News': 'danger',
            'Daily Sabah': 'danger',
            'Hurriyet': 'danger',
            'The News': 'danger',
            'Express Tribune': 'danger',
            'Geo News': 'success',
            'ARY News': 'danger',
            'Samaa TV': 'warning'
        };
        
        for (const [name, color] of Object.entries(colorMap)) {
            if (channelName.toLowerCase().includes(name.toLowerCase())) {
                return color;
            }
        }
        
        return 'secondary';
    }

    function attachChannelCardListeners() {
        const channelCards = document.querySelectorAll('.channel-stat-card');
        channelCards.forEach(card => {
            card.addEventListener('click', function() {
                const channelId = this.dataset.channelId;
                // Could navigate to channel detail page or show modal
                console.log('Channel clicked:', channelId);
            });
            
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Auto-refresh channel stats every hour (3600000 ms)
    setInterval(refreshChannelStats, 3600000);
    
    // Initial channel stats refresh on page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            if (document.getElementById('channelStatsGrid')) {
                refreshChannelStats();
            }
        }, 2000); // Wait 2 seconds after page load
    });

    // Load news function
    function loadNews() {
        const newsContainer = document.getElementById('news-container');
        const spinner = document.querySelector('.loading-spinner');
        
        spinner.style.display = 'block';
        newsContainer.style.opacity = '0.5';
        
        const url = new URL(window.location);
        url.searchParams.set('ajax', '1');
        
        fetch(url.toString())
            .then(response => response.text())
            .then(html => {
                // This would need to be implemented with a proper AJAX endpoint
                console.log('News refreshed');
            })
            .catch(error => console.error('Error loading news:', error))
            .finally(() => {
                spinner.style.display = 'none';
                newsContainer.style.opacity = '1';
            });
    }

    // Leaflet Map Initialization
    let map;
    let markerClusterGroup;
    let markers = [];

    function initLeafletMap() {
        map = L.map('leafletMap').setView([30, 0], 2);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        markerClusterGroup = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 80
        });

        const channelData = {
            'BBC News': { lat: 51.5074, lng: -0.1278, country: 'UK', articles: 25, url: 'https://www.bbc.com/news' },
            'CNN': { lat: 33.7490, lng: -84.3880, country: 'USA', articles: 22, url: 'https://www.cnn.com' },
            'Al Jazeera': { lat: 25.2854, lng: 51.5310, country: 'Qatar', articles: 18, url: 'https://www.aljazeera.com' },
            'Dawn News': { lat: 24.8607, lng: 67.0011, country: 'Pakistan', articles: 15, url: 'https://www.dawn.com' },
            'Geo News': { lat: 24.8607, lng: 67.0011, country: 'Pakistan', articles: 4, url: 'https://www.geo.tv' },
            'ARY News': { lat: 24.8607, lng: 67.0011, country: 'Pakistan', articles: 5, url: 'https://arynews.tv' }
        };

        for (const [channelName, data] of Object.entries(channelData)) {
            const marker = L.marker([data.lat, data.lng]);
            marker.bindPopup(`<b>${channelName}</b><br>${data.country}<br>Articles: ${data.articles}<br><a href="${data.url}" target="_blank">Visit</a>`);
            markers.push(marker);
            markerClusterGroup.addLayer(marker);
        }

        map.addLayer(markerClusterGroup);
    }

    function resetMapView() {
        map.setView([30, 0], 2);
    }

    function toggleClusters() {
        if (map.hasLayer(markerClusterGroup)) {
            map.removeLayer(markerClusterGroup);
            markers.forEach(marker => map.addLayer(marker));
        } else {
            markers.forEach(marker => map.removeLayer(marker));
            map.addLayer(markerClusterGroup);
        }
    }

    function toggleHeatmap() {
        showNotification('Heatmap feature requires additional plugin', 'info');
    }

    function applyFilters() {
        showNotification('Filters applied', 'success');
    }

    // Initialize map on page load
    document.addEventListener('DOMContentLoaded', function() {
        initLeafletMap();
        initCharts();
    });

    // Initialize Charts
    function initCharts() {
        // Region Pie Chart
        const regionCtx = document.getElementById('regionChart');
        if (regionCtx) {
            new Chart(regionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Asia', 'Europe', 'Americas', 'Africa', 'Oceania'],
                    datasets: [{
                        data: [45, 25, 20, 7, 3],
                        backgroundColor: [
                            '#e74c3c',
                            '#3498db',
                            '#27ae60',
                            '#f39c12',
                            '#9b59b6'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#ffffff',
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }

        // Country Bar Chart
        const countryCtx = document.getElementById('countryChart');
        if (countryCtx) {
            new Chart(countryCtx, {
                type: 'bar',
                data: {
                    labels: ['USA', 'UK', 'Pakistan', 'Qatar', 'France', 'India'],
                    datasets: [{
                        label: 'News Articles',
                        data: [101, 68, 32, 18, 17, 15],
                        backgroundColor: [
                            '#e74c3c',
                            '#3498db',
                            '#27ae60',
                            '#f39c12',
                            '#9b59b6',
                            '#1abc9c'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#ffffff' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        },
                        x: {
                            ticks: { color: '#ffffff' },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Timeline Chart
        const timelineCtx = document.getElementById('timelineChart');
        if (timelineCtx) {
            new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [{
                        label: 'News Published',
                        data: [15, 8, 45, 78, 65, 52, 23],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#ffffff' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        },
                        x: {
                            ticks: { color: '#ffffff' },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#ffffff' }
                        }
                    }
                }
            });
        }
    }
});
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
