<?php
// RSS Connectivity Configuration
// Add this to your main configuration file

// Optimized settings for RSS fetching
ini_set("default_socket_timeout", 45);
ini_set("max_execution_time", 180);
ini_set("allow_url_fopen", "On");

// Enhanced RSS Fetcher Class
class RSSFetcher {
    private $timeout = 60;
    private $connect_timeout = 15;
    private $user_agent = "PK-LIVE-NEWS-RSS-Reader/2.0";
    
    public function fetchRSS($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_USER_AGENT => $this->user_agent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => "gzip, deflate"
        ]);
        
        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            $xml = @simplexml_load_string($result);
            return $xml !== false ? $xml : false;
        }
        
        return false;
    }
}

// Usage example:
// $fetcher = new RSSFetcher();
// $rss = $fetcher->fetchRSS("https://feeds.bbci.co.uk/news/rss.xml");
?>