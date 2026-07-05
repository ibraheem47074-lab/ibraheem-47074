<?php
/**
 * Fixed Automatic News Import System
 */

require_once __DIR__ . "/../config/database.php";
require_once "enhanced_rss_parser.php";

class FixedAutoNewsImporter {
    private $conn;
    private $parser;
    private $maxArticlesPerFeed = 5;
    private $downloadImages = false; // Disable for testing
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->parser = new EnhancedRSSParser();
        // Set reasonable timeouts
        $this->parser->setTimeout(15, 10);
    }
    
    public function importFromAllSources() {
        $results = [
            "total_feeds" => 0,
            "successful_feeds" => 0,
            "total_articles" => 0,
            "imported_articles" => 0,
            "duplicate_articles" => 0,
            "error_feeds" => 0,
            "details" => []
        ];
        
        // Get active RSS sources
        $sources_query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' ORDER BY name ASC";
        $sources_result = mysqli_query($this->conn, $sources_query);
        
        if (!$sources_result) {
            throw new Exception("Failed to fetch news sources: " . mysqli_error($this->conn));
        }
        
        $results["total_feeds"] = mysqli_num_rows($sources_result);
        
        while ($source = mysqli_fetch_assoc($sources_result)) {
            try {
                $feedResult = $this->importFromSource($source);
                $results["successful_feeds"]++;
                $results["total_articles"] += $feedResult["total_articles"];
                $results["imported_articles"] += $feedResult["imported_articles"];
                $results["duplicate_articles"] += $feedResult["duplicate_articles"];
                $results["details"][] = $feedResult;
                
            } catch (Exception $e) {
                $results["error_feeds"]++;
                $results["details"][] = [
                    "source_name" => $source["name"] ?? "Unknown",
                    "source_url" => $source["rss_url"] ?? "Unknown",
                    "error" => $e->getMessage(),
                    "total_articles" => 0,
                    "imported_articles" => 0,
                    "duplicate_articles" => 0
                ];
                
                error_log("RSS Import Error for " . ($source["name"] ?? "Unknown") . ": " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    public function importFromSource($source) {
        $result = [
            "source_name" => $source["name"],
            "source_url" => $source["rss_url"],
            "total_articles" => 0,
            "imported_articles" => 0,
            "duplicate_articles" => 0,
            "skipped_articles" => 0,
            "articles" => []
        ];
        
        // Use rss_url field, fallback to url
        $rss_url = !empty($source["rss_url"]) ? $source["rss_url"] : $source["url"];
        
        // Parse RSS feed
        $articles = $this->parser->parseRSS($rss_url);
        $result["total_articles"] = count($articles);
        
        // Limit articles
        $articles = array_slice($articles, 0, $this->maxArticlesPerFeed);
        
        foreach ($articles as $article) {
            try {
                $importResult = $this->importArticle($article, $source);
                
                if ($importResult["status"] === "imported") {
                    $result["imported_articles"]++;
                } elseif ($importResult["status"] === "duplicate") {
                    $result["duplicate_articles"]++;
                } else {
                    $result["skipped_articles"]++;
                }
                
                $result["articles"][] = $importResult;
                
            } catch (Exception $e) {
                $result["articles"][] = [
                    "title" => $article["title"] ?? "Unknown",
                    "status" => "error",
                    "error" => $e->getMessage()
                ];
                error_log("Article Import Error: " . $e->getMessage());
            }
        }
        
        // Update source timestamp
        if (isset($source["id"])) {
            $updateQuery = "UPDATE news_sources SET last_scraped = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "i", $source["id"]);
            mysqli_stmt_execute($stmt);
        }
        
        return $result;
    }
    
    private function importArticle($article, $source) {
        $result = [
            "title" => $article["title"],
            "status" => "",
            "image_downloaded" => false
        ];
        
        // Validate required fields
        if (empty($article["title"])) {
            $result["status"] = "skipped";
            $result["error"] = "Missing title";
            return $result;
        }
        
        // Check for duplicates
        if ($this->isDuplicate($article["title"])) {
            $result["status"] = "duplicate";
            return $result;
        }
        
        // Generate slug
        $slug = $this->generateSlug($article["title"]);
        
        // Clean content
        $content = !empty($article["content"]) ? $article["content"] : $article["excerpt"] ?? "";
        $excerpt = substr(strip_tags($content), 0, 300);
        
        // Add source attribution
        $attribution = "\n\n<p><em><strong>Source:</strong> <a href=\"" . ($article["link"] ?? "#") . "\" target=\"_blank\" rel=\"noopener\">" . htmlspecialchars($source["name"]) . "</a></em></p>";
        $content .= $attribution;
        
        // Insert into database
        $insertQuery = "INSERT INTO news (title, slug, content, excerpt, category_id, 
                        status, source_url, news_type, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'draft', ?, 'rss_import', NOW())";
        
        $stmt = mysqli_prepare($this->conn, $insertQuery);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        
        mysqli_stmt_bind_param($stmt, "ssssis", 
            $article["title"], 
            $slug, 
            $content, 
            $excerpt, 
            $source["category_id"], 
            $article["link"] ?? ""
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $result["status"] = "imported";
            $result["news_id"] = mysqli_insert_id($this->conn);
        } else {
            throw new Exception("Failed to insert article: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    private function isDuplicate($title) {
        $checkQuery = "SELECT id FROM news WHERE title LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = mysqli_prepare($this->conn, $checkQuery);
        $similarTitle = "%$title%";
        mysqli_stmt_bind_param($stmt, "s", $similarTitle);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_num_rows($result) > 0;
    }
    
    private function generateSlug($title) {
        $slug = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "-", $title));
        $slug = trim($slug, "-");
        
        // Check if slug exists
        $checkQuery = "SELECT id FROM news WHERE slug = ?";
        $stmt = mysqli_prepare($this->conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $slug);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $slug .= "-" . time();
        }
        
        return $slug;
    }
    
    public function setMaxArticlesPerFeed($max) {
        $this->maxArticlesPerFeed = (int)$max;
    }
}

// Global function
function fixed_auto_import_news($conn) {
    $importer = new FixedAutoNewsImporter($conn);
    return $importer->importFromAllSources();
}
?>