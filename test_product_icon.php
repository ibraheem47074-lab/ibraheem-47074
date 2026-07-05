<?php
$page_title = 'Product Icon Test';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Header Product Icon Test</h3>
                </div>
                <div class="card-body">
                    <p>This page tests the product icon in the header.</p>
                    
                    <h4>What to check:</h4>
                    <ul>
                        <li>✓ Product icon (shopping bag) should be visible in the header</li>
                        <li>✓ Icon should have green styling to match design</li>
                        <li>✓ Dropdown should show product categories when clicked</li>
                        <li>✓ Should be positioned correctly with other header icons</li>
                        <li>✓ Should be responsive on mobile devices</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <strong>Note:</strong> The product icon appears in the main header area (top right) 
                        next to the language switcher and user menu. It uses a green shopping bag icon 
                        to distinguish it from other elements.
                    </div>
                    
                    <h4>Features Added:</h4>
                    <ul>
                        <li>🛍️ Shopping bag icon in header</li>
                        <li>🎨 Green gradient styling matching site theme</li>
                        <li>📋 Dropdown menu with product categories</li>
                        <li>📱 Mobile responsive design</li>
                        <li>✨ Hover effects and animations</li>
                    </ul>
                    
                    <a href="index.php" class="btn btn-primary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
