<?php
require_once '../config/database.php';
require_once '../config/helpers.php';
require_once '../includes/language_functions.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../login.php');
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_language'])) {
        $code = clean_input($_POST['code']);
        $name = clean_input($_POST['name']);
        $native_name = clean_input($_POST['native_name']);
        $flag_icon = clean_input($_POST['flag_icon']);
        $sort_order = (int)$_POST['sort_order'];
        
        if (empty($code) || empty($name) || empty($native_name)) {
            $error = 'Language code, name, and native name are required';
        } else {
            $query = "INSERT INTO languages (code, name, native_name, flag_icon, sort_order) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssssi', $code, $name, $native_name, $flag_icon, $sort_order);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'Language added successfully';
            } else {
                $error = 'Error adding language: ' . mysqli_error($conn);
            }
        }
    }
    
    if (isset($_POST['update_language'])) {
        $id = (int)$_POST['id'];
        $code = clean_input($_POST['code']);
        $name = clean_input($_POST['name']);
        $native_name = clean_input($_POST['native_name']);
        $flag_icon = clean_input($_POST['flag_icon']);
        $sort_order = (int)$_POST['sort_order'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $query = "UPDATE languages SET code = ?, name = ?, native_name = ?, flag_icon = ?, 
                 sort_order = ?, is_active = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssiii', $code, $name, $native_name, $flag_icon, 
                              $sort_order, $is_active, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Language updated successfully';
        } else {
            $error = 'Error updating language: ' . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['delete_language'])) {
        $id = (int)$_POST['id'];
        
        // Don't allow deletion of English (default language)
        $check_query = "SELECT code FROM languages WHERE id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $lang = mysqli_fetch_assoc($result);
        
        if ($lang && $lang['code'] === 'en') {
            $error = 'Cannot delete English (default language)';
        } else {
            $query = "DELETE FROM languages WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'Language deleted successfully';
            } else {
                $error = 'Error deleting language: ' . mysqli_error($conn);
            }
        }
    }
    
    if (isset($_POST['update_settings'])) {
        $default_language = clean_input($_POST['default_language']);
        $enable_switcher = isset($_POST['enable_switcher']) ? 1 : 0;
        $show_flags = isset($_POST['show_flags']) ? 1 : 0;
        $auto_detect = isset($_POST['auto_detect']) ? 1 : 0;
        $multilingual_seo = isset($_POST['multilingual_seo']) ? 1 : 0;
        
        update_setting('default_language', $default_language);
        update_setting('enable_language_switcher', $enable_switcher);
        update_setting('show_language_flags', $show_flags);
        update_setting('auto_detect_language', $auto_detect);
        update_setting('multilingual_seo', $multilingual_seo);
        
        $message = 'Settings updated successfully';
    }
}

// Check if languages table exists, create it if not
$languages_check = mysqli_query($conn, "SHOW TABLES LIKE 'languages'");
if (mysqli_num_rows($languages_check) == 0) {
    $create_languages_sql = "CREATE TABLE `languages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(10) NOT NULL,
        `name` varchar(100) NOT NULL,
        `native_name` varchar(100) NOT NULL,
        `flag_icon` varchar(50) DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT 1,
        `sort_order` int(11) DEFAULT 0,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_languages_sql);
    
    // Insert default languages
    $insert_languages_sql = "INSERT INTO `languages` (`code`, `name`, `native_name`, `flag_icon`, `is_active`, `sort_order`) VALUES
    ('en', 'English', 'English', 'us', 1, 1),
    ('ur', 'Urdu', ' Urdu', 'pk', 1, 2),
    ('hi', 'Hindi', ' ', 'in', 1, 3),
    ('zh', 'Chinese', ' ', 'cn', 1, 4),
    ('ps', 'Pashto', ' ', 'af', 1, 5)";
    mysqli_query($conn, $insert_languages_sql);
}

// Get all languages
$languages_query = "SELECT * FROM languages ORDER BY sort_order ASC, name ASC";
$languages_result = mysqli_query($conn, $languages_query);

// Get settings
$settings = [
    'default_language' => get_site_setting('default_language', 'en'),
    'enable_switcher' => get_site_setting('enable_language_switcher', '1'),
    'show_flags' => get_site_setting('show_language_flags', '1'),
    'auto_detect' => get_site_setting('auto_detect_language', '1'),
    'multilingual_seo' => get_site_setting('multilingual_seo', '1')
];
?>

<?php include 'includes/admin-header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Language Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLanguageModal">
                    <i class="fas fa-plus me-2"></i>Add Language
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Settings Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Language Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_language" class="form-label">Default Language</label>
                                    <select class="form-select" id="default_language" name="default_language">
                                        <?php while ($lang = mysqli_fetch_assoc($languages_result)): ?>
                                            <option value="<?php echo $lang['code']; ?>" 
                                                <?php echo $lang['code'] === $settings['default_language'] ? 'selected' : ''; ?>>
                                                <?php echo $lang['flag_icon'] . ' ' . $lang['name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                        <?php mysqli_data_seek($languages_result, 0); // Reset pointer ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="enable_switcher" 
                                               name="enable_switcher" <?php echo $settings['enable_switcher'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable_switcher">
                                            Enable Language Switcher
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="show_flags" 
                                           name="show_flags" <?php echo $settings['show_flags'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="show_flags">
                                        Show Country Flags
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_detect" 
                                           name="auto_detect" <?php echo $settings['auto_detect'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto_detect">
                                        Auto-detect Browser Language
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="multilingual_seo" 
                                           name="multilingual_seo" <?php echo $settings['multilingual_seo'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="multilingual_seo">
                                        Enable Multilingual SEO
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Settings
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Languages Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Languages</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Flag</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Native Name</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($language = mysqli_fetch_assoc($languages_result)): ?>
                                    <tr>
                                        <td><?php echo $language['flag_icon']; ?></td>
                                        <td><code><?php echo $language['code']; ?></code></td>
                                        <td><?php echo htmlspecialchars($language['name']); ?></td>
                                        <td><?php echo htmlspecialchars($language['native_name']); ?></td>
                                        <td><?php echo $language['sort_order']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $language['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $language['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editLanguageModal<?php echo $language['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <?php if ($language['code'] !== 'en'): ?>
                                                <form method="POST" style="display: inline-block;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this language?')">
                                                    <input type="hidden" name="id" value="<?php echo $language['id']; ?>">
                                                    <button type="submit" name="delete_language" 
                                                            class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editLanguageModal<?php echo $language['id']; ?>" 
                                         tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Language</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?php echo $language['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_code<?php echo $language['id']; ?>" class="form-label">Language Code</label>
                                                            <input type="text" class="form-control" id="edit_code<?php echo $language['id']; ?>" 
                                                                   name="code" value="<?php echo $language['code']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_name<?php echo $language['id']; ?>" class="form-label">Name</label>
                                                            <input type="text" class="form-control" id="edit_name<?php echo $language['id']; ?>" 
                                                                   name="name" value="<?php echo $language['name']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_native_name<?php echo $language['id']; ?>" class="form-label">Native Name</label>
                                                            <input type="text" class="form-control" id="edit_native_name<?php echo $language['id']; ?>" 
                                                                   name="native_name" value="<?php echo $language['native_name']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_flag_icon<?php echo $language['id']; ?>" class="form-label">Flag Icon</label>
                                                            <input type="text" class="form-control" id="edit_flag_icon<?php echo $language['id']; ?>" 
                                                                   name="flag_icon" value="<?php echo $language['flag_icon']; ?>" 
                                                                   placeholder="🇺🇸 or emoji">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_sort_order<?php echo $language['id']; ?>" class="form-label">Sort Order</label>
                                                            <input type="number" class="form-control" id="edit_sort_order<?php echo $language['id']; ?>" 
                                                                   name="sort_order" value="<?php echo $language['sort_order']; ?>" min="0">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" id="edit_is_active<?php echo $language['id']; ?>" 
                                                                       name="is_active" <?php echo $language['is_active'] ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="edit_is_active<?php echo $language['id']; ?>">
                                                                    Active
                                                                </label>
                                                            </div>
                                                        </div>
                                                        
                                                        <button type="submit" name="update_language" class="btn btn-primary">
                                                            <i class="fas fa-save me-2"></i>Update Language
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Language Modal -->
<div class="modal fade" id="addLanguageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="code" class="form-label">Language Code</label>
                        <input type="text" class="form-control" id="code" name="code" 
                               placeholder="en, ur, hi, zh, ps" required>
                        <div class="form-text">2-letter language code (ISO 639-1)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="English, Urdu, Hindi, Chinese, Pashto" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="native_name" class="form-label">Native Name</label>
                        <input type="text" class="form-control" id="native_name" name="native_name" 
                               placeholder="English, اردو, हिन्दी, 中文, پښتو" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="flag_icon" class="form-label">Flag Icon</label>
                        <input type="text" class="form-control" id="flag_icon" name="flag_icon" 
                               placeholder="🇺🇸 or emoji">
                        <div class="form-text">Country flag emoji or icon</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                               value="0" min="0">
                        <div class="form-text">Lower numbers appear first</div>
                    </div>
                    
                    <button type="submit" name="add_language" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Language
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
