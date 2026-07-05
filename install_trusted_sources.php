<?php
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

try {
    // Read and execute the SQL file
    $sql_file = __DIR__ . '/create_trusted_sources_table.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executed = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (mysqli_query($conn, $statement)) {
                $executed++;
            } else {
                throw new Exception("Error executing SQL: " . mysqli_error($conn));
            }
        }
    }
    
    $success = "Trusted sources table created successfully! Executed $executed SQL statements.";
    
    // Verify table was created
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'trusted_sources'");
    if (mysqli_num_rows($check_table) > 0) {
        $count_query = "SELECT COUNT(*) as count FROM trusted_sources";
        $result = mysqli_query($conn, $count_query);
        $count = mysqli_fetch_assoc($result)['count'];
        $success .= " Inserted $count trusted sources.";
    }
    
} catch (Exception $e) {
    $error = "Error installing trusted sources: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Trusted Sources - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .install-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Install Trusted Sources</h1>
                        <small>PK Live News - AI Fake News Detection</small>
                    </div>
                    <div>
                        <a href="admin-dashboard.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Installation Result -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card install-card">
                            <div class="card-body p-5">
                                <?php if ($success): ?>
                                    <div class="text-center success-animation">
                                        <div class="feature-icon bg-success text-white mx-auto mb-4">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <h2 class="text-success mb-3">Installation Successful!</h2>
                                        <p class="lead mb-4"><?php echo $success; ?></p>
                                        
                                        <div class="alert alert-info">
                                            <h5><i class="fas fa-info-circle me-2"></i>What was installed?</h5>
                                            <p class="mb-2">The <code>trusted_sources</code> table was created with pre-populated trusted news sources for AI fake news detection.</p>
                                            <p class="mb-2">This table includes:</p>
                                            <ul class="mb-0">
                                                <li>International news agencies (Reuters, AP, BBC, NPR)</li>
                                                <li>Major US news outlets (NY Times, Washington Post, WSJ)</li>
                                                <li>TV news networks (CNN, Fox News, CBS, NBC, ABC)</li>
                                                <li>International sources (Guardian, Al Jazeera, Deutsche Welle)</li>
                                                <li>South Asian sources (Dawn, Geo, Express Tribune, ARY)</li>
                                                <li>Business and technology sources</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="d-flex gap-3 justify-content-center mt-4">
                                            <a href="index.php" class="btn btn-primary btn-lg">
                                                <i class="fas fa-home me-2"></i>View Website
                                            </a>
                                            <a href="admin-dashboard.php" class="btn btn-outline-primary btn-lg">
                                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <div class="feature-icon bg-danger text-white mx-auto mb-4">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <h2 class="text-danger mb-3">Installation Failed</h2>
                                        <p class="lead mb-4"><?php echo $error; ?></p>
                                        
                                        <div class="d-flex gap-3 justify-content-center mt-4">
                                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                                                <i class="fas fa-arrow-left me-2"></i>Go Back
                                            </a>
                                            <a href="admin-dashboard.php" class="btn btn-primary btn-lg">
                                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Trusted Sources</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-shield-alt me-2 text-success"></i>Trust Scores</h6>
                                        <p class="small text-muted">Each source is rated on trustworthiness (0.00-1.00) based on journalistic standards, accuracy, and reliability.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-balance-scale me-2 text-info"></i>Bias Detection</h6>
                                        <p class="small text-muted">Sources are rated for political bias to help users understand perspective and potential slant.</p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-check-circle me-2 text-primary"></i>Verification</h6>
                                        <p class="small text-muted">Verified sources have undergone additional fact-checking and credibility assessment.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-globe me-2 text-warning"></i>Global Coverage</h6>
                                        <p class="small text-muted">Includes sources from multiple countries and regions to provide diverse perspectives.</p>
                                    </div>
                                </div>
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
