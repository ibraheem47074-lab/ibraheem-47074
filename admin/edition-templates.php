<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Check if edition_templates table exists, if not redirect to installation
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'edition_templates'");
if (mysqli_num_rows($table_check) === 0) {
    redirect('../install_now.php');
}

// Handle template deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $template_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM edition_templates WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $template_id);
    mysqli_stmt_execute($stmt);
    redirect('edition-templates.php?deleted=1');
}

// Handle default template setting
if (isset($_GET['set_default']) && is_numeric($_GET['set_default'])) {
    $template_id = (int)$_GET['set_default'];
    $template_type = isset($_GET['type']) ? clean_input($_GET['type']) : '';
    
    // Remove default flag from all templates of this type
    $reset_query = "UPDATE edition_templates SET is_default = 0 WHERE template_type = ?";
    $reset_stmt = mysqli_prepare($conn, $reset_query);
    mysqli_stmt_bind_param($reset_stmt, 's', $template_type);
    mysqli_stmt_execute($reset_stmt);
    
    // Set new default
    $set_query = "UPDATE edition_templates SET is_default = 1 WHERE id = ?";
    $set_stmt = mysqli_prepare($conn, $set_query);
    mysqli_stmt_bind_param($set_stmt, 'i', $template_id);
    mysqli_stmt_execute($set_stmt);
    
    redirect('edition-templates.php?default_set=1');
}

// Get templates
$templates_query = "SELECT et.*, ec.name as edition_category_name, ec.icon as edition_icon 
                   FROM edition_templates et
                   LEFT JOIN edition_categories ec ON et.template_type = ec.slug
                   ORDER BY et.template_type, et.name ASC";
$templates_result = mysqli_query($conn, $templates_query);

// Get edition categories
$edition_categories = mysqli_query($conn, "SELECT * FROM edition_categories WHERE status = 'active' ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edition Templates - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .admin-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .admin-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .template-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .template-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .template-preview {
            background: #f8f9fa;
            border: 1px dashed #dee2e6;
            border-radius: 5px;
            padding: 15px;
            max-height: 200px;
            overflow-y: auto;
        }
        .default-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="edition-templates.php">
                                <i class="fas fa-palette me-2"></i>Templates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-polls.php">
                                <i class="fas fa-poll me-2"></i>Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-ads.php">
                                <i class="fas fa-ad me-2"></i>Advertisements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Edition Templates</h1>
                        <small>Manage templates for different news editions</small>
                    </div>
                    <div>
                        <a href="add-template.php" class="btn btn-light">
                            <i class="fas fa-plus me-2"></i>Add Template
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Template deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['default_set'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Default template updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Templates by Category -->
                <?php 
                mysqli_data_seek($edition_categories, 0);
                while ($category = mysqli_fetch_assoc($edition_categories)): 
                    // Get templates for this category
                    $category_templates = [];
                    mysqli_data_seek($templates_result, 0);
                    while ($template = mysqli_fetch_assoc($templates_result)) {
                        if ($template['template_type'] === $category['slug']) {
                            $category_templates[] = $template;
                        }
                    }
                ?>
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas <?php echo $category['icon']; ?> fa-2x me-3" style="color: <?php echo $category['color']; ?>;"></i>
                            <h3><?php echo htmlspecialchars($category['name']); ?> Templates</h3>
                        </div>
                        
                        <?php if (!empty($category_templates)): ?>
                            <div class="row g-4">
                                <?php foreach ($category_templates as $template): ?>
                                    <div class="col-lg-6">
                                        <div class="template-card card h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?php echo htmlspecialchars($template['name']); ?></h5>
                                                    <?php if ($template['is_default']): ?>
                                                        <span class="default-badge">
                                                            <i class="fas fa-star me-1"></i>DEFAULT
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="template-actions">
                                                    <?php if (!$template['is_default']): ?>
                                                        <a href="edition-templates.php?set_default=<?php echo $template['id']; ?>&type=<?php echo $template['template_type']; ?>" 
                                                           class="btn btn-sm btn-outline-success" title="Set as Default">
                                                            <i class="fas fa-star"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="edit-template.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="edition-templates.php?delete=<?php echo $template['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this template?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="template-preview">
                                                    <?php echo htmlspecialchars(substr($template['template_content'], 0, 200)) . '...'; ?>
                                                </div>
                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>Created: <?php echo format_date($template['created_at']); ?>
                                                        <?php if ($template['updated_at'] !== $template['created_at']): ?>
                                                            <br><i class="fas fa-edit me-1"></i>Updated: <?php echo format_date($template['updated_at']); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No templates found for <?php echo htmlspecialchars($category['name']); ?> editions.
                                <a href="add-template.php?type=<?php echo $category['slug']; ?>" class="btn btn-sm btn-primary ms-2">
                                    <i class="fas fa-plus me-1"></i>Add First Template
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>

                <!-- Template Usage Guide -->
                <div class="card mt-5">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>Template Usage Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Available Variables:</h6>
                                <ul class="list-unstyled">
                                    <li><code>{date}</code> - Current date</li>
                                    <li><code>{time}</code> - Current time</li>
                                    <li><code>{content}</code> - Edition content</li>
                                    <li><code>{edition_name}</code> - Edition name</li>
                                    <li><code>{news_title}</code> - News article title</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Best Practices:</h6>
                                <ul>
                                    <li>Use responsive design with Bootstrap classes</li>
                                    <li>Include proper semantic HTML structure</li>
                                    <li>Test templates on different screen sizes</li>
                                    <li>Keep designs clean and readable</li>
                                    <li>Use consistent branding colors</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
