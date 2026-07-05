<?php
require_once '../config/database.php';

/**
 * Job RSS Importer
 * Imports job postings from RSS feeds
 */

// RSS Feeds for job listings
$job_rss_feeds = [
    [
        'name' => 'Rozee.pk',
        'url' => 'https://www.rozee.pk/rss/jobs',
        'category' => 'private-jobs',
        'default_company' => 'Various Companies'
    ],
    [
        'name' => 'Mustakbil',
        'url' => 'https://www.mustakbil.com/rss',
        'category' => 'private-jobs',
        'default_company' => 'Various Companies'
    ],
    [
        'name' => 'Pakistan Govt Jobs',
        'url' => 'https://www.gov.pk/rss/jobs',
        'category' => 'govt-jobs',
        'default_company' => 'Government of Pakistan'
    ],
    [
        'name' => 'Overseas Jobs',
        'url' => 'https://overseasjobs.com/rss',
        'category' => 'overseas-jobs',
        'default_company' => 'International Companies'
    ]
];

function importJobsFromRSS($feed_url, $feed_name, $category_slug, $default_company) {
    global $conn;
    
    echo "Importing jobs from $feed_name...\n";
    
    // Get category ID
    $category_query = "SELECT id FROM categories WHERE slug = ?";
    $stmt = mysqli_prepare($conn, $category_query);
    mysqli_stmt_bind_param($stmt, 's', $category_slug);
    mysqli_stmt_execute($stmt);
    $category_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($category_result) === 0) {
        echo "Category '$category_slug' not found. Skipping...\n";
        return 0;
    }
    
    $category_id = mysqli_fetch_assoc($category_result)['id'];
    
    // Fetch RSS feed
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'PK Live News Job Importer 1.0'
        ]
    ]);
    
    $xml_content = @file_get_contents($feed_url, false, $context);
    
    if (!$xml_content) {
        echo "Failed to fetch RSS feed from $feed_url\n";
        return 0;
    }
    
    $xml = simplexml_load_string($xml_content);
    
    if (!$xml) {
        echo "Failed to parse RSS feed from $feed_url\n";
        return 0;
    }
    
    $imported_count = 0;
    
    foreach ($xml->channel->item as $item) {
        $title = (string) $item->title;
        $description = (string) $item->description;
        $link = (string) $item->link;
        $pub_date = (string) $item->pubDate;
        
        // Skip if title is empty
        if (empty($title)) {
            continue;
        }
        
        // Extract job details from title/description
        $job_details = extractJobDetails($title, $description);
        
        // Generate unique slug
        $slug = create_slug($title) . '-' . strtolower($feed_name) . '-' . time();
        
        // Check if already exists
        $check_query = "SELECT id FROM news WHERE slug = ? OR (title = ? AND apply_url = ?)";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'sss', $slug, $title, $link);
        mysqli_stmt_execute($check_stmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) > 0) {
            continue; // Skip duplicates
        }
        
        // Parse publication date
        $created_at = date('Y-m-d H:i:s');
        if ($pub_date) {
            $timestamp = strtotime($pub_date);
            if ($timestamp !== false) {
                $created_at = date('Y-m-d H:i:s', $timestamp);
            }
        }
        
        // Insert job posting
        $insert_query = "INSERT INTO news (
            title, slug, content, excerpt, category_id, author_id, status, 
            created_at, published_at, company_name, job_location, salary, 
            job_type, apply_url, requirements, is_job_posting
        ) VALUES (?, ?, ?, ?, ?, 1, 'published', ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $excerpt = substr(strip_tags($description), 0, 200) . '...';
        $published_at = $created_at;
        
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sssssisssssss', 
            $title, $slug, $description, $excerpt, $category_id, 
            $created_at, $published_at, 
            $job_details['company'], $job_details['location'], $job_details['salary'], 
            $job_details['job_type'], $link, $job_details['requirements']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $imported_count++;
            echo "Imported: $title\n";
        }
    }
    
    echo "Imported $imported_count jobs from $feed_name\n";
    return $imported_count;
}

function extractJobDetails($title, $description) {
    $details = [
        'company' => '',
        'location' => '',
        'salary' => '',
        'job_type' => '',
        'requirements' => ''
    ];
    
    // Extract location from title
    $locations = ['Lahore', 'Karachi', 'Islamabad', 'Peshawar', 'Quetta', 'Rawalpindi', 'Faisalabad', 'Multan', 'Gujranwala', 'Sialkot'];
    foreach ($locations as $loc) {
        if (stripos($title, $loc) !== false) {
            $details['location'] = $loc;
            break;
        }
    }
    
    // Extract job type
    $job_types = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'];
    foreach ($job_types as $type) {
        if (stripos($title, $type) !== false || stripos($description, $type) !== false) {
            $details['job_type'] = $type;
            break;
        }
    }
    
    // Extract salary information
    if (preg_match('/(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)\s*(?:PKR|Rs|Rupees)/i', $description, $matches)) {
        $details['salary'] = $matches[0];
    }
    
    // Extract company name
    if (preg_match('/^(.*?)\s+(?:is hiring|seeks|wants|needs|requires)/i', $title, $matches)) {
        $details['company'] = trim($matches[1]);
    }
    
    // Use description as requirements if it contains relevant keywords
    $requirement_keywords = ['requirement', 'qualification', 'experience', 'skill', 'education'];
    foreach ($requirement_keywords as $keyword) {
        if (stripos($description, $keyword) !== false) {
            $details['requirements'] = $description;
            break;
        }
    }
    
    return $details;
}

// Main execution
echo "Starting Job RSS Import...\n";
echo "========================\n";

$total_imported = 0;

foreach ($job_rss_feeds as $feed) {
    $imported = importJobsFromRSS($feed['url'], $feed['name'], $feed['category'], $feed['default_company']);
    $total_imported += $imported;
    echo "\n";
}

echo "========================\n";
echo "Total jobs imported: $total_imported\n";
echo "Import completed at: " . date('Y-m-d H:i:s') . "\n";

// Log the import
$log_message = "Job RSS Import completed. Total imported: $total_imported at " . date('Y-m-d H:i:s');
file_put_contents('../logs/job_import.log', $log_message . "\n", FILE_APPEND);

echo "Log saved to logs/job_import.log\n";
?>
