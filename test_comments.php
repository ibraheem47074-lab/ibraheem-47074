<?php
require_once "config/database.php";

// Get a news article for testing
$news_query = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
$news_result = mysqli_query($conn, $news_query);
$news = mysqli_fetch_assoc($news_result);

$page_title = "Comment System Test";
include "includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">🧪 Comment System Test</h4>
                </div>
                <div class="card-body">
                    <h5>Testing Article: <?php echo htmlspecialchars($news["title"]); ?></h5>
                    
                    <?php
                    // Handle comment submission
                    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["test_comment"])) {
                        $name = clean_input($_POST["name"]);
                        $email = clean_input($_POST["email"]);
                        $comment = clean_input($_POST["comment"]);
                        
                        if (!empty($name) && !empty($email) && !empty($comment)) {
                            $insert = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, 'approved')";
                            $stmt = mysqli_prepare($conn, $insert);
                            mysqli_stmt_bind_param($stmt, "isss", $news["id"], $name, $email, $comment);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                echo '<div class="alert alert-success">✅ Comment submitted successfully!</div>';
                            } else {
                                echo '<div class="alert alert-danger">❌ Error submitting comment</div>';
                            }
                        } else {
                            echo '<div class="alert alert-warning">⚠️ Please fill all fields</div>';
                        }
                    }
                    
                    // Get existing comments
                    $comments_query = "SELECT * FROM comments WHERE news_id = ? AND status = 'approved' ORDER BY created_at DESC";
                    $stmt = mysqli_prepare($conn, $comments_query);
                    mysqli_stmt_bind_param($stmt, "i", $news["id"]);
                    mysqli_stmt_execute($stmt);
                    $comments_result = mysqli_stmt_get_result($stmt);
                    ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Test Comment Form</h6>
                            <form method="POST">
                                <div class="mb-2">
                                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                                </div>
                                <div class="mb-2">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="mb-2">
                                    <textarea name="comment" class="form-control" placeholder="Your comment" required></textarea>
                                </div>
                                <button type="submit" name="test_comment" class="btn btn-danger btn-sm">Submit Test Comment</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h6>Existing Comments (<?php echo mysqli_num_rows($comments_result); ?>)</h6>
                            <?php if (mysqli_num_rows($comments_result) > 0): ?>
                                <?php while ($comment = mysqli_fetch_assoc($comments_result)): ?>
                                    <div class="border p-2 mb-2 small">
                                        <strong><?php echo htmlspecialchars($comment["name"]); ?></strong>
                                        <br><small><?php echo $comment["created_at"]; ?></small>
                                        <br><?php echo htmlspecialchars($comment["comment"]); ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted small">No comments yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>