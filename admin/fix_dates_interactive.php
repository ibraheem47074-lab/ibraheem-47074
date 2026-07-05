<?php
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Invalid Dates - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .invalid-date { background-color: #fff3cd; }
        .fixed-date { background-color: #d1e7dd; }
        .btn-fix { background-color: #28a745; color: white; }
        .btn-fix:hover { background-color: #218838; color: white; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.875rem; }
    </style>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-12'>
                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        <h4><i class='fas fa-calendar-alt me-2'></i>Fix Invalid Dates in News Articles</h4>
                    </div>
                    <div class='card-body'>";

// Handle individual fix requests
if (isset($_POST['fix_article_id']) && is_numeric($_POST['fix_article_id'])) {
    $article_id = intval($_POST['fix_article_id']);
    $current_time = date('Y-m-d H:i:s');
    
    // Get current article data
    $get_article = "SELECT id, title, created_at, published_at, updated_at FROM news WHERE id = $article_id";
    $article_result = mysqli_query($conn, $get_article);
    
    if ($article = mysqli_fetch_assoc($article_result)) {
        // Determine which dates need fixing
        $updates = [];
        if (empty($article['created_at']) || $article['created_at'] === '0000-00-00 00:00:00') {
            $updates[] = "created_at = '$current_time'";
        }
        if (empty($article['published_at']) || $article['published_at'] === '0000-00-00 00:00:00') {
            $updates[] = "published_at = '$current_time'";
        }
        if (empty($article['updated_at']) || $article['updated_at'] === '0000-00-00 00:00:00') {
            $updates[] = "updated_at = '$current_time'";
        }
        
        if (!empty($updates)) {
            $update_query = "UPDATE news SET " . implode(', ', $updates) . " WHERE id = $article_id";
            
            if (mysqli_query($conn, $update_query)) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <i class='fas fa-check-circle me-2'></i>
                    <strong>Success!</strong> Fixed dates for article: " . htmlspecialchars(substr($article['title'], 0, 50)) . "...
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
            } else {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-circle me-2'></i>
                    <strong>Error!</strong> Could not fix article: " . mysqli_error($conn) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
            }
        }
    }
}

// Handle fix all request
if (isset($_POST['fix_all'])) {
    $current_time = date('Y-m-d H:i:s');
    $fixed_count = 0;
    
    // Fix all invalid dates
    $fix_all_query = "UPDATE news SET 
        created_at = CASE 
            WHEN created_at IS NULL OR created_at = '0000-00-00 00:00:00' THEN '$current_time'
            ELSE created_at
        END,
        published_at = CASE 
            WHEN published_at IS NULL OR published_at = '0000-00-00 00:00:00' THEN '$current_time'
            ELSE published_at
        END,
        updated_at = CASE 
            WHEN updated_at IS NULL OR updated_at = '0000-00-00 00:00:00' THEN '$current_time'
            ELSE updated_at
        END
        WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'
           OR published_at IS NULL OR published_at = '0000-00-00 00:00:00'
           OR updated_at IS NULL OR updated_at = '0000-00-00 00:00:00'";
    
    if (mysqli_query($conn, $fix_all_query)) {
        $fixed_count = mysqli_affected_rows($conn);
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            <i class='fas fa-check-circle me-2'></i>
            <strong>Success!</strong> Fixed $fixed_count articles with invalid dates.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    } else {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <i class='fas fa-exclamation-circle me-2'></i>
            <strong>Error!</strong> Could not fix all articles: " . mysqli_error($conn) . "
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }
}

// Check for invalid dates
$invalid_dates_query = "SELECT id, title, created_at, published_at, updated_at,
                       CASE 
                           WHEN (created_at IS NULL OR created_at = '0000-00-00 00:00:00' OR created_at = '') AND
                                (published_at IS NULL OR published_at = '0000-00-00 00:00:00' OR published_at = '') AND
                                (updated_at IS NULL OR updated_at = '0000-00-00 00:00:00' OR updated_at = '') THEN 'All Invalid'
                           WHEN created_at IS NULL OR created_at = '0000-00-00 00:00:00' OR created_at = '' THEN 'Created Invalid'
                           WHEN published_at IS NULL OR published_at = '0000-00-00 00:00:00' OR published_at = '' THEN 'Published Invalid'
                           WHEN updated_at IS NULL OR updated_at = '0000-00-00 00:00:00' OR updated_at = '' THEN 'Updated Invalid'
                           ELSE 'Valid'
                       END as date_status
                       FROM news 
                       WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00' OR created_at = '' OR
                             published_at IS NULL OR published_at = '0000-00-00 00:00:00' OR published_at = '' OR
                             updated_at IS NULL OR updated_at = '0000-00-00 00:00:00' OR updated_at = ''
                       ORDER BY id DESC";

$result = mysqli_query($conn, $invalid_dates_query);
$invalid_count = mysqli_num_rows($result);

// Get total articles count
$total_query = "SELECT COUNT(*) as total FROM news";
$total_result = mysqli_query($conn, $total_query);
$total_articles = mysqli_fetch_assoc($total_result)['total'];

echo "<div class='row mb-4'>
        <div class='col-md-6'>
            <div class='card border-danger'>
                <div class='card-body text-center'>
                    <h5 class='card-title text-danger'>Articles with Invalid Dates</h5>
                    <h2 class='text-danger'>$invalid_count</h2>
                </div>
            </div>
        </div>
        <div class='col-md-6'>
            <div class='card border-info'>
                <div class='card-body text-center'>
                    <h5 class='card-title text-info'>Total Articles</h5>
                    <h2 class='text-info'>$total_articles</h2>
                </div>
            </div>
        </div>
    </div>";

if ($invalid_count > 0) {
    echo "<div class='mb-3'>
            <form method='post' class='d-inline'>
                <button type='submit' name='fix_all' class='btn btn-success btn-lg' onclick='return confirm(\"Are you sure you want to fix all $invalid_count articles?\")'>
                    <i class='fas fa-magic me-2'></i>Fix All Articles
                </button>
            </form>
            <a href='fix-invalid-dates.php' class='btn btn-info btn-lg ms-2'>
                <i class='fas fa-tools me-2'></i>Advanced Fix Tool
            </a>
          </div>";
    
    echo "<div class='table-responsive'>
            <table class='table table-striped table-hover'>
                <thead class='table-dark'>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Created At</th>
                        <th>Published At</th>
                        <th>Updated At</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row_class = ($row['date_status'] === 'Valid') ? '' : 'invalid-date';
        $status_class = ($row['date_status'] === 'Valid') ? 'bg-success' : 'bg-warning';
        
        echo "<tr class='$row_class'>
                <td><strong>{$row['id']}</strong></td>
                <td>" . htmlspecialchars(substr($row['title'], 0, 60)) . (strlen($row['title']) > 60 ? '...' : '') . "</td>
                <td>" . ($row['created_at'] ?: '<span class="text-muted">NULL</span>') . "</td>
                <td>" . ($row['published_at'] ?: '<span class="text-muted">NULL</span>') . "</td>
                <td>" . ($row['updated_at'] ?: '<span class="text-muted">NULL</span>') . "</td>
                <td><span class='status-badge $status_class text-white'>{$row['date_status']}</span></td>
                <td>";
        
        if ($row['date_status'] !== 'Valid') {
            echo "<form method='post' class='d-inline'>
                    <input type='hidden' name='fix_article_id' value='{$row['id']}'>
                    <button type='submit' class='btn btn-sm btn-fix' onclick='return confirm(\"Fix dates for article ID {$row['id']}?\")'>
                        <i class='fas fa-wrench me-1'></i>Fix
                    </button>
                  </form>";
        } else {
            echo "<span class='text-success'><i class='fas fa-check-circle'></i> OK</span>";
        }
        
        echo "</td>
              </tr>";
    }
    
    echo "</tbody>
          </table>
        </div>";
} else {
    echo "<div class='alert alert-success text-center'>
            <h4><i class='fas fa-check-circle me-2'></i>No Invalid Dates Found!</h4>
            <p>All articles have valid date information.</p>
          </div>";
}

echo "<div class='text-center mt-4'>
        <a href='manage-news.php' class='btn btn-primary btn-lg me-2'>
            <i class='fas fa-newspaper me-2'></i>Manage News
        </a>
        <a href='../index.php' class='btn btn-secondary btn-lg'>
            <i class='fas fa-home me-2'></i>View Website
        </a>
      </div>
    </div>
</div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";

mysqli_close($conn);
?>
