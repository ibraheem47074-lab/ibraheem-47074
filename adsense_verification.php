<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdSense Verification Tool - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0"><i class="fab fa-google me-2"></i>AdSense Verification Tool</h1>
                    </div>
                    <div class="card-body">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This tool helps you check if AdSense ads are properly configured and displaying on your website.
                        </div>

                        <!-- AdSense Code Check -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-code me-2"></i>AdSense Code Installation Check</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $header_file = 'includes/header.php';
                                $header_content = file_get_contents($header_file);
                                $has_adsense_code = strpos($header_content, 'pagead2.googlesyndication.com') !== false;
                                $has_publisher_id = strpos($header_content, 'ca-pub-3649343603124512') !== false;
                                ?>
                                
                                <div class="alert alert-<?php echo $has_adsense_code ? 'success' : 'danger'; ?>">
                                    <h6 class="alert-heading">AdSense Script:</h6>
                                    <?php if ($has_adsense_code): ?>
                                        ✅ <strong>PASS:</strong> AdSense script is installed in header.php
                                    <?php else: ?>
                                        ❌ <strong>FAIL:</strong> AdSense script not found in header.php
                                    <?php endif; ?>
                                </div>
                                
                                <div class="alert alert-<?php echo $has_publisher_id ? 'success' : 'warning'; ?>">
                                    <h6 class="alert-heading">Publisher ID:</h6>
                                    <?php if ($has_publisher_id): ?>
                                        ✅ <strong>PASS:</strong> Publisher ID (ca-pub-3649343603124512) found
                                    <?php else: ?>
                                        ⚠️ <strong>WARNING:</strong> Publisher ID not found or incorrect
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Ad Placement Test -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-ad me-2"></i>Ad Placement Test</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">This is a test ad unit. If you see a blank space or "Advertisement" label, ads are configured but may not be showing due to approval status.</p>
                                
                                <div class="border p-4 text-center bg-light mb-3">
                                    <h6 class="text-muted">Test Ad Unit - Leaderboard (728x90)</h6>
                                    <div style="width: 728px; height: 90px; margin: 0 auto; border: 1px dashed #ccc; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                        <span class="text-muted">Ad Space (728x90)</span>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="border p-4 text-center bg-light mb-3">
                                            <h6 class="text-muted">Test Ad Unit - Rectangle (300x250)</h6>
                                            <div style="width: 300px; height: 250px; margin: 0 auto; border: 1px dashed #ccc; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                <span class="text-muted">Ad Space (300x250)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border p-4 text-center bg-light mb-3">
                                            <h6 class="text-muted">Test Ad Unit - Square (250x250)</h6>
                                            <div style="width: 250px; height: 250px; margin: 0 auto; border: 1px dashed #ccc; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                <span class="text-muted">Ad Space (250x250)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current AdSense Status -->
                        <div class="card mb-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Current AdSense Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">⚠️ Site Not Yet Approved</h6>
                                    <p class="mb-0">Your site "pk-news.com" is currently <strong>NOT APPROVED</strong> for AdSense. Ads will not display until approval is granted.</p>
                                </div>
                                
                                <h6>Why Ads Aren't Showing:</h6>
                                <ul>
                                    <li>❌ Site approval pending - AdSense team is reviewing your site</li>
                                    <li>❌ Content issues need to be fixed (see diagnostic report)</li>
                                    <li>❌ Publisher ID not yet activated</li>
                                </ul>
                                
                                <h6 class="mt-3">When Will Ads Start Showing?</h6>
                                <ul>
                                    <li>After you fix the identified issues</li>
                                    <li>After you request a review</li>
                                    <li>After AdSense team approves your site</li>
                                    <li>Typically 2-4 weeks after reapplication</li>
                                </ul>
                            </div>
                        </div>

                        <!-- How to Check When Approved -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>How to Verify Ads Are Showing (After Approval)</h5>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li class="mb-3">
                                        <strong>Check AdSense Dashboard:</strong>
                                        <br>Go to AdSense dashboard → Performance reports to see impressions and clicks
                                    </li>
                                    <li class="mb-3">
                                        <strong>Use Google Ad Inspector:</strong>
                                        <br>Right-click on your site → Inspect → Console → Run: <code>googletag.cmd.push(function() { googletag.display('div-gpt-ad-1234567890-0'); });</code>
                                    </li>
                                    <li class="mb-3">
                                        <strong>View Page Source:</strong>
                                        <br>Right-click → View Page Source → Search for "adsbygoogle" to verify code is present
                                    </li>
                                    <li class="mb-3">
                                        <strong>Check Browser Console:</strong>
                                        <br>Open Developer Tools (F12) → Console → Look for AdSense-related messages
                                    </li>
                                    <li class="mb-3">
                                        <strong>Use AdSense Preview Tool:</strong>
                                        <br>AdSense dashboard → Tools → AdSense Preview Tool to test ad placements
                                    </li>
                                    <li>
                                        <strong>Test from Different Locations:</strong>
                                        <br>Use VPN to test from different countries (ads may vary by location)
                                    </li>
                                </ol>
                            </div>
                        </div>

                        <!-- Common Issues -->
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-wrench me-2"></i>Common Issues Preventing Ads from Showing</h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="issuesAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#issue1">
                                                Site Not Approved
                                            </button>
                                        </h2>
                                        <div id="issue1" class="accordion-collapse collapse show" data-bs-parent="#issuesAccordion">
                                            <div class="accordion-body">
                                                <strong>Solution:</strong> Fix content issues, request review, wait for approval. This is your current status.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue2">
                                                Ad Blocker Enabled
                                            </button>
                                        </h2>
                                        <div id="issue2" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                            <div class="accordion-body">
                                                <strong>Solution:</strong> Disable ad blockers when testing. Use incognito/private mode to test without extensions.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue3">
                                                Ad Code Not Placed Correctly
                                            </button>
                                        </h2>
                                        <div id="issue3" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                            <div class="accordion-body">
                                                <strong>Solution:</strong> Ensure AdSense code is in the <code>&lt;head&gt;</code> section of all pages. Check header.php file.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue4">
                                                No Ad Inventory Available
                                            </button>
                                        </h2>
                                        <div id="issue4" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                            <div class="accordion-body">
                                                <strong>Solution:</strong> Sometimes no ads are available for your content or location. This is normal and doesn't indicate a problem.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue5">
                                                Auto Ads Not Enabled
                                            </button>
                                        </h2>
                                        <div id="issue5" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                            <div class="accordion-body">
                                                <strong>Solution:</strong> Enable Auto Ads in AdSense dashboard for automatic ad placement, or manually place ad units.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Next Steps -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-arrow-right me-2"></i>Next Steps to Get Ads Showing</h5>
                                <ol class="mb-0">
                                    <li class="mb-2">Fix content issues using the diagnostic report</li>
                                    <li class="mb-2">Ensure you have 30+ high-quality original articles</li>
                                    <li class="mb-2">Add images to all articles</li>
                                    <li class="mb-2">Maintain consistent publishing (2-3 articles/week)</li>
                                    <li class="mb-2">Wait 2-3 weeks after fixes</li>
                                    <li class="mb-2">Request AdSense review from dashboard</li>
                                    <li class="mb-2">Wait for approval email</li>
                                    <li>Ads will start showing automatically after approval</li>
                                </ol>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
