<?php
// Fix path for includes when called from admin directory
$basePath = dirname(__DIR__) . '/';

// Check if database tables exist first
$database_ready = false;
if (file_exists($basePath . 'config/database.php')) {
    require_once $basePath . 'config/database.php';
    if ($conn) {
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'languages'");
        $database_ready = mysqli_num_rows($table_check) > 0;
    }
}

if ($database_ready) {
    // Use full language functions
    require_once $basePath . 'includes/language_functions.php';
    $current_lang = get_current_language();
    $get_active_languages = 'get_active_languages';
    $get_setting = 'get_setting';
    $get_language_url = 'get_language_url';
    $generate_hreflang_tags = 'generate_hreflang_tags';
    $get_news_title = 'get_news_title';
    $get_news_content = 'get_news_content';
} else {
    // Use minimal language functions
    require_once $basePath . 'includes/language_functions_minimal.php';
    $current_lang = get_current_language_minimal();
    $get_active_languages = 'get_active_languages_minimal';
    $get_setting = 'get_setting_minimal';
    $get_language_url = 'get_language_url_minimal';
    $generate_hreflang_tags = 'generate_hreflang_tags_minimal';
    $get_news_title = 'get_news_title_minimal';
    $get_news_content = 'get_news_content_minimal';
}

$page_title = 'Home';
require_once $basePath . 'includes/header_minimal.php';
?>

<!-- Hero Section -->
<section class="hero-section py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-body text-center">
                        <h2 class="mb-4">🌍 Multi-Language News System</h2>
                        
                        <?php if ($database_ready): ?>
                            <div class="alert alert-success">
                                <h4>✅ Database Setup Complete!</h4>
                                <p>Your multi-language system is ready to use.</p>
                                <div class="mt-3">
                                    <a href="admin/" class="btn btn-primary me-2">
                                        <i class="fas fa-cog me-2"></i>Admin Panel
                                    </a>
                                    <a href="admin/add_news_multilang.php" class="btn btn-success me-2">
                                        <i class="fas fa-plus me-2"></i>Add Multilingual News
                                    </a>
                                    <a href="admin/manage_languages.php" class="btn btn-info">
                                        <i class="fas fa-language me-2"></i>Manage Languages
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h4>⚠️ Database Setup Required</h4>
                                <p>The multi-language system needs database tables to be created.</p>
                                <p class="mb-3">Please run the setup script to complete installation:</p>
                                <div class="text-center">
                                    <a href="simple_setup.php" class="btn btn-danger btn-lg">
                                        <i class="fas fa-database me-2"></i>Run Database Setup
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="row text-start">
                            <div class="col-md-6">
                                <h5>🌐 Available Languages</h5>
                                <div class="list-group">
                                    <?php 
                                    $languages = $get_active_languages();
                                    foreach ($languages as $lang): 
                                    ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <?php echo $lang['flag_icon']; ?>
                                                <?php echo htmlspecialchars($lang['native_name']); ?>
                                            </span>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($lang['name']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>⚙️ Current Settings</h5>
                                <div class="list-group">
                                    <div class="list-group-item">
                                        <strong>Current Language:</strong> 
                                        <?php echo $get_active_languages()[array_search($current_lang, array_column($get_active_languages(), 'code'))]['native_name'] ?? 'English'; ?>
                                    </div>
                                    <div class="list-group-item">
                                        <strong>Language Switcher:</strong> 
                                        <span class="badge bg-<?php echo get_setting('enable_language_switcher') == '1' ? 'success' : 'secondary'; ?>">
                                            <?php echo get_setting('enable_language_switcher') == '1' ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </div>
                                    <div class="list-group-item">
                                        <strong>Show Flags:</strong> 
                                        <span class="badge bg-<?php echo get_setting('show_language_flags') == '1' ? 'success' : 'secondary'; ?>">
                                            <?php echo get_setting('show_language_flags') == '1' ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </div>
                                    <div class="list-group-item">
                                        <strong>Auto-Detect:</strong> 
                                        <span class="badge bg-<?php echo get_setting('auto_detect_language') == '1' ? 'success' : 'secondary'; ?>">
                                            <?php echo get_setting('auto_detect_language') == '1' ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($database_ready): ?>
                            <hr class="my-4">
                            <h4>📰 Sample News Articles</h4>
                            <p>These are sample articles to demonstrate multi-language functionality.</p>
                            
                            <div class="row g-4">
                                <?php
                                // Create sample news articles for demonstration
                                $sample_news = [
                                    [
                                        'id' => 1,
                                        'title' => 'Breaking: Major Technology Announcement',
                                        'title_ur' => 'توڑ: اہم ٹیکنالوجی کا اہم اعلان',
                                        'title_hi' => 'तोड़: प्रमुख तकनी घोषणा',
                                        'title_zh' => '突发：重大技术公告',
                                        'title_ps' => 'ژغورنی: لوی ټکنالوژي اعلان',
                                        'summary' => 'A revolutionary new technology has been announced today...',
                                        'slug' => 'sample-tech-news',
                                        'published_at' => date('Y-m-d H:i:s')
                                    ],
                                    [
                                        'id' => 2,
                                        'title' => 'Sports Championship Finals This Weekend',
                                        'title_ur' => 'کھیل چیمپئن شپ فائنلز اس ہفتے',
                                        'title_hi' => 'खेल चैंपियनशिप फाइनल इस सप्ताह',
                                        'title_zh' => '体育锦标赛本周末决赛',
                                        'title_ps' => 'د سپورټ چیمپئن شپ فائنلز ددې',
                                        'summary' => 'The championship finals are scheduled for this weekend...',
                                        'slug' => 'sample-sports-news',
                                        'published_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
                                    ],
                                    [
                                        'id' => 3,
                                        'title' => 'New Economic Policy Revealed',
                                        'title_ur' => 'نئی معاشیاتی پالیسی کا انکشاف',
                                        'title_hi' => 'नई आर्थिक नीति का खुलासा',
                                        'title_zh' => '新经济政策公布',
                                        'title_ps' => 'نوې اقتصادي پالیسي رابړل کړ',
                                        'summary' => 'Government announces new economic measures...',
                                        'slug' => 'sample-business-news',
                                        'published_at' => date('Y-m-d H:i:s', strtotime('+2 days'))
                                    ]
                                ];
                                
                                foreach ($sample_news as $news):
                                    $title = $get_news_title($news);
                                ?>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow news-card">
                                            <div class="card-body">
                                                <div class="position-relative">
                                                    <img src="https://via.placeholder.com/300x200/007bff/ffffff?text=News" 
                                                         class="card-img-top" alt="<?php echo htmlspecialchars($title); ?>" 
                                                         style="height: 200px; object-fit: cover; width: 100%;">
                                                    
                                                    <div class="position-absolute top-0 start-0 m-2">
                                                        <span class="badge bg-primary">Sample</span>
                                                    </div>
                                                </div>
                                                
                                                <h6 class="card-title mt-3">
                                                    <a href="#" class="text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($title); ?>
                                                    </a>
                                                </h6>
                                                
                                                <div class="text-muted small">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    <?php echo date('M j, Y • g:i A', strtotime($news['published_at'])); ?>
                                                </div>
                                                
                                                <p class="card-text text-muted mt-2">
                                                    <?php echo htmlspecialchars(substr($news['summary'], 0, 80)) . '...'; ?>
                                                </p>
                                                
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>Read More
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links Section -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-cog fa-3x text-primary mb-3"></i>
                        <h6>Database Setup</h6>
                        <p class="small">Configure multi-language database tables</p>
                        <a href="simple_setup.php" class="btn btn-sm btn-primary">Run Setup</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-language fa-3x text-success mb-3"></i>
                        <h6>Language Management</h6>
                        <p class="small">Manage available languages and settings</p>
                        <a href="admin/manage_languages.php" class="btn btn-sm btn-success">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-newspaper fa-3x text-info mb-3"></i>
                        <h6>Multi-Language News</h6>
                        <p class="small">Create news in multiple languages</p>
                        <a href="admin/add_news_multilang.php" class="btn btn-sm btn-info">Create</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-tachometer-alt fa-3x text-warning mb-3"></i>
                        <h6>System Status</h6>
                        <p class="small">Check system configuration</p>
                        <a href="test_multilang.php" class="btn btn-sm btn-warning">Test</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once $basePath . 'includes/footer.php'; ?>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.hero-section .card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.news-card {
    transition: transform 0.2s ease;
}

.news-card:hover {
    transform: translateY(-5px);
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    font-size: 0.875rem;
}

.card-img-top {
    border-radius: 8px 8px 0 0;
}
</style>
