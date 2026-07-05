<?php
/**
 * GD Extension Fallback System
 * Provides alternative image processing when GD extension is not available
 */

class GDFallback {
    private static $gd_available = null;
    
    /**
     * Check if GD extension is available
     */
    public static function isGDAvailable() {
        if (self::$gd_available === null) {
            self::$gd_available = extension_loaded('gd');
        }
        return self::$gd_available;
    }
    
    /**
     * Get image information without GD
     */
    public static function getImageInfo($image_path) {
        if (!file_exists($image_path)) {
            return false;
        }
        
        $image_info = getimagesize($image_path);
        if ($image_info === false) {
            return false;
        }
        
        return [
            'width' => $image_info[0],
            'height' => $image_info[1],
            'type' => $image_info[2],
            'mime' => $image_info['mime'] ?? 'unknown',
            'size' => filesize($image_path)
        ];
    }
    
    /**
     * Validate image file without GD
     */
    public static function validateImage($image_path, $max_size = 5242880) {
        if (!file_exists($image_path)) {
            return ['valid' => false, 'error' => 'File does not exist'];
        }
        
        $file_size = filesize($image_path);
        if ($file_size > $max_size) {
            return ['valid' => false, 'error' => 'File size too large'];
        }
        
        $image_info = self::getImageInfo($image_path);
        if ($image_info === false) {
            return ['valid' => false, 'error' => 'Invalid image file'];
        }
        
        $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
        if (!in_array($image_info['type'], $allowed_types)) {
            return ['valid' => false, 'error' => 'Unsupported image type'];
        }
        
        return ['valid' => true, 'info' => $image_info];
    }
    
    /**
     * Create thumbnail placeholder (returns original path)
     */
    public static function createThumbnail($source_path, $thumb_path, $width = 300, $height = 200) {
        if (self::isGDAvailable()) {
            return self::createThumbnailWithGD($source_path, $thumb_path, $width, $height);
        }
        
        // Fallback: just copy the original file
        if (!copy($source_path, $thumb_path)) {
            return false;
        }
        
        return $thumb_path;
    }
    
    /**
     * Create thumbnail with GD extension
     */
    private static function createThumbnailWithGD($source_path, $thumb_path, $width = 300, $height = 200) {
        $image_info = self::getImageInfo($source_path);
        if ($image_info === false) {
            return false;
        }
        
        // Create image resource based on type
        switch ($image_info['type']) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($source_path);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }
        
        if ($source === false) {
            return false;
        }
        
        // Calculate aspect ratio
        $source_width = $image_info['width'];
        $source_height = $image_info['height'];
        $aspect_ratio = $source_width / $source_height;
        
        // Calculate new dimensions
        if ($width / $height > $aspect_ratio) {
            $new_width = $height * $aspect_ratio;
            $new_height = $height;
        } else {
            $new_width = $width;
            $new_height = $width / $aspect_ratio;
        }
        
        // Create thumbnail
        $thumb = imagecreatetruecolor($new_width, $new_height);
        if ($thumb === false) {
            imagedestroy($source);
            return false;
        }
        
        // Preserve transparency for PNG and GIF
        if ($image_info['type'] == IMAGETYPE_PNG) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, $transparent);
        } elseif ($image_info['type'] == IMAGETYPE_GIF) {
            $transparent_index = imagecolortransparent($source);
            if ($transparent_index >= 0) {
                $transparent_color = imagecolorsforindex($source, $transparent_index);
                $transparent = imagecolorallocate($thumb, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagecolortransparent($thumb, $transparent);
            }
        }
        
        // Resize image
        if (!imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height)) {
            imagedestroy($source);
            imagedestroy($thumb);
            return false;
        }
        
        // Save thumbnail
        $success = false;
        switch ($image_info['type']) {
            case IMAGETYPE_JPEG:
                $success = imagejpeg($thumb, $thumb_path, 85);
                break;
            case IMAGETYPE_PNG:
                $success = imagepng($thumb, $thumb_path, 9);
                break;
            case IMAGETYPE_GIF:
                $success = imagegif($thumb, $thumb_path);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumb);
        
        return $success ? $thumb_path : false;
    }
    
    /**
     * Resize image
     */
    public static function resizeImage($source_path, $dest_path, $max_width = 800, $max_height = 600) {
        if (self::isGDAvailable()) {
            return self::resizeImageWithGD($source_path, $dest_path, $max_width, $max_height);
        }
        
        // Fallback: just copy the original file
        return copy($source_path, $dest_path);
    }
    
    /**
     * Resize image with GD extension
     */
    private static function resizeImageWithGD($source_path, $dest_path, $max_width, $max_height) {
        $image_info = self::getImageInfo($source_path);
        if ($image_info === false) {
            return false;
        }
        
        // If image is already within limits, just copy it
        if ($image_info['width'] <= $max_width && $image_info['height'] <= $max_height) {
            return copy($source_path, $dest_path);
        }
        
        return self::createThumbnailWithGD($source_path, $dest_path, $max_width, $max_height);
    }
    
    /**
     * Add watermark to image
     */
    public static function addWatermark($image_path, $watermark_text = 'PK Live News') {
        if (!self::isGDAvailable()) {
            return true; // Skip watermarking if GD not available
        }
        
        $image_info = self::getImageInfo($image_path);
        if ($image_info === false) {
            return false;
        }
        
        // Create image resource
        switch ($image_info['type']) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($image_path);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($image_path);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($image_path);
                break;
            default:
                return false;
        }
        
        if ($image === false) {
            return false;
        }
        
        // Add watermark
        $text_color = imagecolorallocatealpha($image, 255, 255, 255, 127);
        $font_size = min($image_info['width'], $image_info['height']) / 25;
        $x = 10;
        $y = $image_info['height'] - 10;
        
        imagettftext($image, $font_size, 0, $x, $y, $text_color, 'arial.ttf', $watermark_text);
        
        // Save image
        $success = false;
        switch ($image_info['type']) {
            case IMAGETYPE_JPEG:
                $success = imagejpeg($image, $image_path, 90);
                break;
            case IMAGETYPE_PNG:
                $success = imagepng($image, $image_path, 9);
                break;
            case IMAGETYPE_GIF:
                $success = imagegif($image, $image_path);
                break;
        }
        
        imagedestroy($image);
        return $success;
    }
    
    /**
     * Get supported image formats
     */
    public static function getSupportedFormats() {
        $formats = ['jpeg', 'jpg', 'png', 'gif'];
        
        if (self::isGDAvailable()) {
            $formats[] = 'webp';
            $formats[] = 'bmp';
        }
        
        return $formats;
    }
    
    /**
     * Get system status
     */
    public static function getSystemStatus() {
        return [
            'gd_available' => self::isGDAvailable(),
            'supported_formats' => self::getSupportedFormats(),
            'fallback_mode' => !self::isGDAvailable(),
            'recommendations' => self::getRecommendations()
        ];
    }
    
    /**
     * Get recommendations for missing GD
     */
    private static function getRecommendations() {
        if (self::isGDAvailable()) {
            return [];
        }
        
        return [
            'Install GD extension for full image processing capabilities',
            'Without GD: Limited image processing (no thumbnails, resizing, or watermarks)',
            'See GD_EXTENSION_SETUP_GUIDE.md for installation instructions',
            'Current mode: Basic image uploads with original file sizes'
        ];
    }
}

// Helper functions for backward compatibility
if (!function_exists('create_thumbnail')) {
    function create_thumbnail($source, $dest, $width = 300, $height = 200) {
        return GDFallback::createThumbnail($source, $dest, $width, $height);
    }
}

if (!function_exists('resize_image')) {
    function resize_image($source, $dest, $max_width = 800, $max_height = 600) {
        return GDFallback::resizeImage($source, $dest, $max_width, $max_height);
    }
}

if (!function_exists('validate_image')) {
    function validate_image($image_path, $max_size = 5242880) {
        return GDFallback::validateImage($image_path, $max_size);
    }
}
?>
