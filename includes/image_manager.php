<?php
/**
 * Image Management System for PK Live News
 * Handles AI-generated images, storage, and management
 */

class ImageManager {
    public $conn;
    private $imageDir;
    private $maxFileSize;
    private $allowedTypes;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->imageDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/generated/';
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        
        // Ensure directory exists
        if (!file_exists($this->imageDir)) {
            mkdir($this->imageDir, 0755, true);
        }
    }
    
    /**
     * Save generated image record
     */
    public function saveGeneratedImage($news_id, $image_data) {
        $query = "INSERT INTO generated_images 
                 (news_id, original_url, local_path, prompt, provider, generation_time, 
                  file_size, dimensions, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed')";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'issssdis', 
            $news_id,
            $image_data['original_url'],
            $image_data['local_path'],
            $image_data['prompt'],
            $image_data['provider'],
            $image_data['generation_time'],
            $image_data['file_size'],
            $image_data['dimensions']
        );
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Update news article with image
     */
    public function updateNewsImage($news_id, $image_path, $provider, $prompt) {
        $query = "UPDATE news 
                 SET image = ?, image_provider = ?, image_prompt = ?, image_generated_at = NOW()
                 WHERE id = ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssi', $image_path, $provider, $prompt, $news_id);
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Get articles that need images
     */
    public function getArticlesNeedingImages($limit = 10, $category_id = 0) {
        $where_clause = "WHERE (n.image IS NULL OR n.image = '') AND n.status = 'published'";
        $params = [];
        $types = '';
        
        if ($category_id > 0) {
            $where_clause .= " AND n.category_id = ?";
            $params[] = $category_id;
            $types .= 'i';
        }
        
        $query = "SELECT n.id, n.title, n.content, n.category_id, c.name as category_name,
                 CASE 
                     WHEN n.is_breaking = 1 THEN 'high'
                     WHEN n.status = 'featured' THEN 'high'
                     WHEN n.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'medium'
                     ELSE 'low'
                 END as priority
                 FROM news n
                 LEFT JOIN categories c ON n.category_id = c.id
                 $where_clause
                 ORDER BY 
                     CASE 
                         WHEN n.is_breaking = 1 THEN 1
                         WHEN n.status = 'featured' THEN 2
                         WHEN n.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 3
                         ELSE 4
                     END,
                     n.published_at DESC
                 LIMIT ?";
        
        $params[] = $limit;
        $types .= 'i';
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $articles = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $articles[] = $row;
        }
        
        return $articles;
    }
    
    /**
     * Queue image generation for articles
     */
    public function queueImageGeneration($articles, $provider = 'openai', $style = 'realistic journalistic news photo') {
        $queued = 0;
        
        foreach ($articles as $article) {
            // Check if already queued
            $check_query = "SELECT id FROM image_generation_queue 
                           WHERE news_id = ? AND status IN ('pending', 'processing')";
            $stmt = mysqli_prepare($this->conn, $check_query);
            mysqli_stmt_bind_param($stmt, 'i', $article['id']);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_get_result($stmt)->num_rows == 0) {
                $insert_query = "INSERT INTO image_generation_queue 
                               (news_id, priority, provider, style) 
                               VALUES (?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($this->conn, $insert_query);
                mysqli_stmt_bind_param($stmt, 'isss', 
                    $article['id'], 
                    $article['priority'], 
                    $provider, 
                    $style
                );
                
                if (mysqli_stmt_execute($stmt)) {
                    $queued++;
                }
            }
        }
        
        return $queued;
    }
    
    /**
     * Process image generation queue
     */
    public function processQueue($limit = 5) {
        $processed = 0;
        $failed = 0;
        
        // Get next items from queue
        $query = "SELECT q.*, n.title, n.content, c.name as category_name
                 FROM image_generation_queue q
                 JOIN news n ON q.news_id = n.id
                 LEFT JOIN categories c ON n.category_id = c.id
                 WHERE q.status = 'pending' AND q.attempts < q.max_attempts
                 ORDER BY q.priority DESC, q.created_at ASC
                 LIMIT ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $limit);
        mysqli_stmt_execute($stmt);
        
        $queue_items = [];
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $queue_items[] = $row;
        }
        
        foreach ($queue_items as $item) {
            try {
                // Mark as processing
                $this->updateQueueStatus($item['id'], 'processing');
                
                // Generate image
                $imageGenerator = new ImageGenerator($item['provider']);
                $excerpt = substr(strip_tags($item['content']), 0, 200) . '...';
                $result = $imageGenerator->generateImageForNews(
                    $item['title'], 
                    $excerpt, 
                    $item['category_name'], 
                    $item['style']
                );
                
                if ($result['success']) {
                    // Update news article
                    $this->updateNewsImage(
                        $item['news_id'], 
                        $result['local_path'], 
                        $item['provider'], 
                        $result['prompt']
                    );
                    
                    // Save generation record
                    $image_data = [
                        'original_url' => $result['image_url'],
                        'local_path' => $result['local_path'],
                        'prompt' => $result['prompt'],
                        'provider' => $item['provider'],
                        'generation_time' => 0, // Would be measured in actual implementation
                        'file_size' => 0, // Would be measured in actual implementation
                        'dimensions' => '1024x1024'
                    ];
                    
                    $this->saveGeneratedImage($item['news_id'], $image_data);
                    
                    // Mark queue item as completed
                    $this->updateQueueStatus($item['id'], 'completed');
                    $processed++;
                    
                } else {
                    throw new Exception($result['error']);
                }
                
            } catch (Exception $e) {
                // Mark as failed
                $this->updateQueueStatus($item['id'], 'failed', $e->getMessage());
                $failed++;
                
                // Update attempts
                $this->incrementQueueAttempts($item['id']);
            }
            
            // Add delay to avoid rate limiting
            sleep(1);
        }
        
        return [
            'processed' => $processed,
            'failed' => $failed,
            'total' => count($queue_items)
        ];
    }
    
    /**
     * Update queue status
     */
    private function updateQueueStatus($queue_id, $status, $error_message = null) {
        $query = "UPDATE image_generation_queue 
                 SET status = ?, processed_at = NOW()";
        
        $params = [$status];
        $types = 's';
        
        if ($error_message) {
            $query .= ", error_message = ?";
            $params[] = $error_message;
            $types .= 's';
        }
        
        $query .= " WHERE id = ?";
        $params[] = $queue_id;
        $types .= 'i';
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Increment queue attempts
     */
    private function incrementQueueAttempts($queue_id) {
        $query = "UPDATE image_generation_queue 
                 SET attempts = attempts + 1 
                 WHERE id = ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $queue_id);
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Get image generation statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Overall stats
        $query = "SELECT 
                 COUNT(*) as total_articles,
                 COUNT(CASE WHEN image IS NOT NULL AND image != '' THEN 1 END) as with_images,
                 COUNT(CASE WHEN image IS NULL OR image = '' THEN 1 END) as without_images,
                 ROUND(COUNT(CASE WHEN image IS NOT NULL AND image != '' THEN 1 END) * 100.0 / COUNT(*), 1) as coverage_percentage
                 FROM news 
                 WHERE status = 'published'";
        
        $result = mysqli_query($this->conn, $query);
        $stats['overall'] = mysqli_fetch_assoc($result);
        
        // Generated images stats
        $query = "SELECT 
                 COUNT(*) as total_generated,
                 COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful,
                 COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                 COUNT(CASE WHEN provider = 'openai' THEN 1 END) as openai_count,
                 COUNT(CASE WHEN provider = 'stability' THEN 1 END) as stability_count,
                 COUNT(CASE WHEN provider = 'replicate' THEN 1 END) as replicate_count,
                 COUNT(CASE WHEN provider = 'placeholder' THEN 1 END) as placeholder_count,
                 AVG(generation_time) as avg_generation_time
                 FROM generated_images";
        
        $result = mysqli_query($this->conn, $query);
        $stats['generated'] = mysqli_fetch_assoc($result);
        
        // Queue stats
        $query = "SELECT 
                 COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                 COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                 COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                 COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed
                 FROM image_generation_queue";
        
        $result = mysqli_query($this->conn, $query);
        $stats['queue'] = mysqli_fetch_assoc($result);
        
        // Recent activity
        $query = "SELECT DATE(created_at) as date, COUNT(*) as count
                 FROM generated_images 
                 WHERE status = 'completed'
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY date DESC";
        
        $result = mysqli_query($this->conn, $query);
        $stats['recent_activity'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['recent_activity'][] = $row;
        }
        
        return $stats;
    }
    
    /**
     * Clean up old failed queue items
     */
    public function cleanupFailedQueue($days_old = 7) {
        $query = "DELETE FROM image_generation_queue 
                 WHERE status = 'failed' 
                 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $days_old);
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Get image settings
     */
    public function getSettings() {
        $query = "SELECT setting_key, setting_value FROM image_settings";
        $result = mysqli_query($this->conn, $query);
        
        $settings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * Update image settings
     */
    public function updateSetting($key, $value) {
        $query = "INSERT INTO image_settings (setting_key, setting_value) 
                 VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
        
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Validate image file
     */
    public function validateImage($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        $file_size = filesize($file_path);
        if ($file_size > $this->maxFileSize) {
            return false;
        }
        
        $image_info = getimagesize($file_path);
        if (!$image_info) {
            return false;
        }
        
        if (!in_array($image_info['mime'], $this->allowedTypes)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete generated image
     */
    public function deleteGeneratedImage($news_id) {
        // Get image info
        $query = "SELECT local_path FROM generated_images WHERE news_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            // Delete physical file
            $full_path = $_SERVER['DOCUMENT_ROOT'] . $row['local_path'];
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }
        
        // Delete database records
        $delete_query = "DELETE FROM generated_images WHERE news_id = ?";
        $stmt = mysqli_prepare($this->conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        
        // Update news article
        $update_query = "UPDATE news SET image = NULL, image_provider = NULL, 
                         image_prompt = NULL, image_generated_at = NULL 
                         WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        
        return mysqli_stmt_execute($stmt);
    }
}

// Helper functions
function get_image_manager() {
    global $conn;
    static $manager = null;
    
    if ($manager === null) {
        $manager = new ImageManager($conn);
    }
    
    return $manager;
}

function queue_article_for_image($news_id, $priority = 'medium') {
    $manager = get_image_manager();
    
    $query = "INSERT INTO image_generation_queue (news_id, priority) 
             VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE priority = VALUES(priority)";
    
    $stmt = mysqli_prepare($manager->conn, $query);
    mysqli_stmt_bind_param($stmt, 'is', $news_id, $priority);
    
    return mysqli_stmt_execute($stmt);
}
?>
