<?php
// PK Live News - Manage News Testing Guide
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Manage News Test Guide - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .test-container { max-width: 1200px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .test-step { border-left: 4px solid #007bff; padding: 15px; margin: 10px 0; background: #f8f9fa; border-radius: 5px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .btn-test { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 5px; margin: 5px; }
        .btn-fixed { background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 5px; margin: 5px; }
        .btn-buggy { background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 5px; margin: 5px; }
        .code-block { background: #000; color: #00ff00; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='test-container'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h3 class='mb-0'><i class='fas fa-vial me-2'></i>Manage News Testing Guide</h3>
            </div>
            <div class='card-body'>";

echo "<h4><i class='fas fa-clipboard-list me-2'></i>Testing Instructions</h4>
<p>Follow these steps to test if your manage news functionality works correctly:</p>";

// Step 1: Test Deletion
echo "<div class='test-step'>
    <h5><i class='fas fa-trash me-2'></i>Step 1: Test Article Deletion</h5>
    <p><strong>Objective:</strong> Verify that deleting ONE article only deletes that specific article.</p>
    
    <div class='row'>
        <div class='col-md-6'>
            <h6>Test Procedure:</h6>
            <ol>
                <li>Go to <a href='manage-news.php' class='btn btn-buggy btn-sm'>Buggy Version</a></li>
                <li>Count how many articles you see (take note)</li>
                <li>Click delete on ONE article (confirm deletion)</li>
                <li>Refresh page and count articles again</li>
                <li><strong>Expected:</strong> Only 1 article should be deleted</li>
                <li><strong>Problem:</strong> If ALL articles disappear → Bug exists</li>
            </ol>
        </div>
        <div class='col-md-6'>
            <h6>Fixed Version Test:</h6>
            <ol>
                <li>Go to <a href='manage-news-fixed.php' class='btn btn-fixed btn-sm'>Fixed Version</a></li>
                <li>Repeat same test</li>
                <li><strong>Expected:</strong> Only selected article deleted</li>
                <li><strong>Success:</strong> Other articles remain intact</li>
            </ol>
        </div>
    </div>
</div>";

// Step 2: Test Status Change
echo "<div class='test-step'>
    <h5><i class='fas fa-edit me-2'></i>Step 2: Test Status Change</h5>
    <p><strong>Objective:</strong> Verify that changing status of ONE article only affects that article.</p>
    
    <div class='row'>
        <div class='col-md-6'>
            <h6>Test Procedure:</h6>
            <ol>
                <li>Go to <a href='manage-news.php' class='btn btn-buggy btn-sm'>Buggy Version</a></li>
                <li>Find a 'Published' article</li>
                <li>Click the checkmark to change to 'Published' status</li>
                <li>Refresh page</li>
                <li><strong>Expected:</strong> Only that article changes status</li>
                <li><strong>Problem:</strong> If ALL articles become 'Draft' → Bug exists</li>
            </ol>
        </div>
        <div class='col-md-6'>
            <h6>Fixed Version Test:</h6>
            <ol>
                <li>Go to <a href='manage-news-fixed.php' class='btn btn-fixed btn-sm'>Fixed Version</a></li>
                <li>Repeat same test</li>
                <li><strong>Expected:</strong> Only selected article changes status</li>
                <li><strong>Success:</strong> Other articles keep their status</li>
            </ol>
        </div>
    </div>
</div>";

// Step 3: Test Article Posting
echo "<div class='test-step'>
    <h5><i class='fas fa-plus me-2'></i>Step 3: Test Article Posting</h5>
    <p><strong>Objective:</strong> Verify that posting ONE article creates only ONE entry.</p>
    
    <div class='row'>
        <div class='col-md-6'>
            <h6>Test Procedure:</h6>
            <ol>
                <li>Go to <a href='add-news.php' class='btn btn-test btn-sm'>Add News</a></li>
                <li>Fill in article details</li>
                <li>Submit the article</li>
                <li>Go back to manage news</li>
                <li><strong>Expected:</strong> Only 1 new article appears</li>
                <li><strong>Problem:</strong> If 2 identical articles appear → Bug exists</li>
            </ol>
        </div>
        <div class='col-md-6'>
            <h6>What to Check:</h6>
            <ul>
                <li>Article titles should be unique</li>
                <li>Only ONE new entry in database</li>
                <li>No duplicate slugs</li>
                <li>Proper image upload</li>
            </ul>
        </div>
    </div>
</div>";

// Step 4: Quick Test Summary
echo "<div class='test-step'>
    <h5><i class='fas fa-clipboard-check me-2'></i>Quick Test Summary</h5>
    
    <div class='table-responsive'>
        <table class='table table-bordered'>
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Buggy Version</th>
                    <th>Fixed Version</th>
                    <th>Expected Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Delete 1 Article</td>
                    <td class='error'>Deletes ALL articles</td>
                    <td class='success'>Deletes ONLY selected</td>
                    <td>Only selected article removed</td>
                </tr>
                <tr>
                    <td>Change Status</td>
                    <td class='error'>Changes ALL articles</td>
                    <td class='success'>Changes ONLY selected</td>
                    <td>Only selected article affected</td>
                </tr>
                <tr>
                    <td>Post Article</td>
                    <td class='error'>Creates 2 duplicates</td>
                    <td class='success'>Creates 1 article</td>
                    <td>Single unique article</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>";

// Step 5: Access Links
echo "<div class='test-step'>
    <h5><i class='fas fa-link me-2'></i>Quick Access Links</h5>
    
    <div class='row'>
        <div class='col-md-6'>
            <h6><span class='error'>Buggy Versions (For Comparison)</span></h6>
            <ul>
                <li><a href='manage-news.php' class='btn btn-buggy'>Manage News (Buggy)</a></li>
                <li><a href='add-news.php' class='btn btn-buggy'>Add News (Original)</a></li>
            </ul>
        </div>
        <div class='col-md-6'>
            <h6><span class='success'>Fixed Versions (Use These)</span></h6>
            <ul>
                <li><a href='manage-news-fixed.php' class='btn btn-fixed'>Manage News (FIXED)</a></li>
                <li><a href='add-news.php' class='btn btn-fixed'>Add News (Fixed)</a></li>
            </ul>
        </div>
    </div>
</div>";

// Step 6: Database Check
echo "<div class='test-step'>
    <h5><i class='fas fa-database me-2'></i>Database Verification</h5>
    <p>You can also verify directly in database:</p>
    
    <div class='code-block'>
-- Count articles before test
SELECT COUNT(*) FROM news;

-- Check for duplicates (should be 0)
SELECT title, COUNT(*) as count 
FROM news 
GROUP BY title 
HAVING count > 1;

-- Check recent articles
SELECT id, title, status, created_at 
FROM news 
ORDER BY created_at DESC 
LIMIT 5;
    </div>
</div>";

echo "<div class='alert alert-info mt-4'>
    <h5><i class='fas fa-info-circle me-2'></i>Important Notes</h5>
    <ul class='mb-0'>
        <li>Test the <strong>fixed versions</strong> to verify bugs are resolved</li>
        <li>Compare with <strong>buggy versions</strong> to see the difference</li>
        <li>If fixed versions work correctly, replace the original files</li>
        <li>Always backup your database before testing</li>
    </ul>
</div>";

echo "
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
