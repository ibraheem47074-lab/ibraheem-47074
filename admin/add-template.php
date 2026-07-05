<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $template_type = clean_input($_POST['template_type']);
    $template_content = $_POST['template_content'];
    $css_styles = clean_input($_POST['css_styles']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($template_type) || empty($template_content)) {
        $error = 'Name, template type, and template content are required fields';
    } else {
        // If setting as default, remove default flag from other templates of same type
        if ($is_default) {
            $reset_query = "UPDATE edition_templates SET is_default = 0 WHERE template_type = ?";
            $reset_stmt = mysqli_prepare($conn, $reset_query);
            mysqli_stmt_bind_param($reset_stmt, 's', $template_type);
            mysqli_stmt_execute($reset_stmt);
        }
        
        // Insert template
        $query = "INSERT INTO edition_templates (name, template_type, template_content, css_styles, is_default) 
                 VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', 
            $name, $template_type, $template_content, $css_styles, $is_default
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Template added successfully!";
            // Clear form
            $_POST = array();
        } else {
            $error = "Error adding template: " . mysqli_error($conn);
        }
    }
}

// Get edition categories
$edition_categories = mysqli_query($conn, "SELECT * FROM edition_categories WHERE status = 'active' ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Template - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CodeMirror for template editing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
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
        .template-type-card {
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .template-type-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .template-type-card.selected {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .preview-pane {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            min-height: 200px;
        }
        .CodeMirror {
            height: 400px;
            border: 1px solid #ddd;
        }
        .variable-chip {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-family: monospace;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .variable-chip:hover {
            background: #1976d2;
            color: white;
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
                        <h1 class="h3 mb-0">Add Template</h1>
                        <small>Create a new template for news editions</small>
                    </div>
                    <div>
                        <a href="edition-templates.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Templates
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Add Template Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="templateForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Template Name -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Template Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                               placeholder="e.g., Morning Edition Template" required>
                                    </div>

                                    <!-- Template Type Selection -->
                                    <div class="mb-3">
                                        <label class="form-label">Template Type *</label>
                                        <div class="row">
                                            <?php while ($category = mysqli_fetch_assoc($edition_categories)): ?>
                                                <div class="col-md-4 mb-3">
                                                    <div class="card template-type-card" onclick="selectTemplateType('<?php echo $category['slug']; ?>', this)">
                                                        <div class="card-body text-center">
                                                            <i class="fas <?php echo $category['icon']; ?> fa-2x mb-2" style="color: <?php echo $category['color']; ?>;"></i>
                                                            <h6><?php echo htmlspecialchars($category['name']); ?></h6>
                                                            <small><?php echo htmlspecialchars($category['description']); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        <input type="hidden" id="template_type" name="template_type" required>
                                    </div>

                                    <!-- Template Content -->
                                    <div class="mb-3">
                                        <label for="template_content" class="form-label">Template Content *</label>
                                        <div class="mb-2">
                                            <small class="text-muted">Available variables: 
                                                <span class="variable-chip" onclick="insertVariable('{date}')">{date}</span>
                                                <span class="variable-chip" onclick="insertVariable('{time}')">{time}</span>
                                                <span class="variable-chip" onclick="insertVariable('{content}')">{content}</span>
                                                <span class="variable-chip" onclick="insertVariable('{edition_name}')">{edition_name}</span>
                                                <span class="variable-chip" onclick="insertVariable('{news_title}')">{news_title}</span>
                                            </small>
                                        </div>
                                        <textarea class="form-control" id="template_content" name="template_content" rows="15" required><?php echo isset($_POST['template_content']) ? htmlspecialchars($_POST['template_content']) : ''; ?></textarea>
                                    </div>

                                    <!-- CSS Styles -->
                                    <div class="mb-3">
                                        <label for="css_styles" class="form-label">CSS Styles (Optional)</label>
                                        <textarea class="form-control" id="css_styles" name="css_styles" rows="8" placeholder="Custom CSS styles for this template"><?php echo isset($_POST['css_styles']) ? htmlspecialchars($_POST['css_styles']) : ''; ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Set as Default -->
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" 
                                                   <?php echo isset($_POST['is_default']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_default">
                                                Set as Default Template
                                            </label>
                                        </div>
                                        <small class="text-muted">This will be the default template for this edition type</small>
                                    </div>

                                    <!-- Live Preview -->
                                    <div class="mb-3">
                                        <label class="form-label">Live Preview</label>
                                        <div class="preview-pane" id="previewPane">
                                            <p class="text-muted">Template preview will appear here as you type...</p>
                                        </div>
                                    </div>

                                    <!-- Template Examples -->
                                    <div class="mb-3">
                                        <label class="form-label">Quick Examples</label>
                                        <div class="list-group">
                                            <button type="button" class="list-group-item list-group-item-action" onclick="loadExample('morning')">
                                                <i class="fas fa-sun me-2"></i>Morning Edition Example
                                            </button>
                                            <button type="button" class="list-group-item list-group-item-action" onclick="loadExample('breaking')">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Breaking News Example
                                            </button>
                                            <button type="button" class="list-group-item list-group-item-action" onclick="loadExample('evening')">
                                                <i class="fas fa-moon me-2"></i>Evening Edition Example
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Template
                                        </button>
                                        <a href="edition-templates.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script>
        // Initialize CodeMirror
        const editor = CodeMirror.fromTextArea(document.getElementById('template_content'), {
            lineNumbers: true,
            mode: 'xml',
            theme: 'default',
            autoCloseTags: true,
            autoCloseBrackets: true
        });

        // Template type selection
        function selectTemplateType(type, element) {
            // Remove previous selection
            document.querySelectorAll('.template-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            element.classList.add('selected');
            
            // Set hidden input value
            document.getElementById('template_type').value = type;
        }

        // Insert variable into editor
        function insertVariable(variable) {
            editor.replaceSelection(variable);
            editor.focus();
        }

        // Load example templates
        function loadExample(type) {
            const examples = {
                morning: `<div class="edition-header">
    <h2><i class="fas fa-sun"></i> Morning Edition</h2>
    <p class="edition-date">{date}</p>
</div>
<div class="edition-content">
    {content}
</div>
<div class="edition-footer">
    <p>Good morning! Here are today's top stories.</p>
</div>`,
                breaking: `<div class="breaking-alert">
    <div class="breaking-icon">
        <i class="fas fa-exclamation-triangle fa-2x"></i>
    </div>
    <div class="breaking-content">
        <h3>BREAKING NEWS</h3>
        <div class="edition-content">
            {content}
        </div>
        <p class="breaking-time">Updated: {time}</p>
    </div>
</div>`,
                evening: `<div class="edition-header">
    <h2><i class="fas fa-moon"></i> Evening Edition</h2>
    <p class="edition-date">{date}</p>
</div>
<div class="edition-content">
    {content}
</div>
<div class="edition-footer">
    <p>Evening roundup of today's important news.</p>
</div>`
            };

            if (examples[type]) {
                editor.setValue(examples[type]);
                updatePreview();
            }
        }

        // Update preview
        function updatePreview() {
            const content = editor.getValue();
            const preview = document.getElementById('previewPane');
            
            // Replace variables with sample data
            let previewContent = content
                .replace(/{date}/g, new Date().toLocaleDateString())
                .replace(/{time}/g, new Date().toLocaleTimeString())
                .replace(/{content}/g, 'This is sample content for your template preview.')
                .replace(/{edition_name}/g, 'Sample Edition')
                .replace(/{news_title}/g, 'Sample News Article Title');
            
            preview.innerHTML = previewContent;
        }

        // Update preview on content change
        editor.on('change', updatePreview);

        // Form validation
        document.getElementById('templateForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const templateType = document.getElementById('template_type').value;
            const content = editor.getValue();
            
            if (!name || !templateType || !content) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });

        // Set initial template type from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const initialType = urlParams.get('type');
        if (initialType) {
            const cards = document.querySelectorAll('.template-type-card');
            cards.forEach(card => {
                if (card.onclick.toString().includes(initialType)) {
                    card.click();
                }
            });
        }
    </script>
</body>
</html>
