<?php
require_once '../config/database.php';
require_once 'includes/admin-header.php';
require_once '../includes/affiliate-functions.php';

// Check if user is logged in and is admin
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Affiliate Analytics';
$success_message = '';
$error_message = '';

// Date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get overall statistics
$stats_query = "SELECT 
    COUNT(DISTINCT ac.id) as total_clicks,
    COUNT(DISTINCT CASE WHEN ac.converted = 1 THEN ac.id END) as total_conversions,
    COUNT(DISTINCT ac.product_id) as products_with_clicks,
    AVG(p.price) as avg_product_price
    FROM affiliate_clicks ac
    LEFT JOIN affiliate_products p ON ac.product_id = p.id
    WHERE DATE(ac.click_date) BETWEEN ? AND ?";

$stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
mysqli_stmt_execute($stmt);
$stats_result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($stats_result);

// Calculate conversion rate
$conversion_rate = $stats['total_clicks'] > 0 ? round(($stats['total_conversions'] / $stats['total_clicks']) * 100, 2) : 0;

// Get top performing products
$top_products_query = "SELECT p.*, c.name as category_name, COUNT(ac.id) as clicks, 
                      COUNT(CASE WHEN ac.converted = 1 THEN ac.id END) as conversions
                      FROM affiliate_products p
                      LEFT JOIN affiliate_categories c ON p.category_id = c.id
                      LEFT JOIN affiliate_clicks ac ON p.id = ac.product_id
                      WHERE DATE(ac.click_date) BETWEEN ? AND ? OR ac.click_date IS NULL
                      GROUP BY p.id, p.title, c.name
                      ORDER BY clicks DESC, conversions DESC
                      LIMIT 10";

$stmt = mysqli_prepare($conn, $top_products_query);
mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
mysqli_stmt_execute($stmt);
$top_products_result = mysqli_stmt_get_result($stmt);

// Get daily clicks data
$daily_clicks_query = "SELECT DATE(click_date) as date, COUNT(*) as clicks, 
                      COUNT(CASE WHEN converted = 1 THEN 1 END) as conversions
                      FROM affiliate_clicks 
                      WHERE DATE(click_date) BETWEEN ? AND ?
                      GROUP BY DATE(click_date)
                      ORDER BY date";

$stmt = mysqli_prepare($conn, $daily_clicks_query);
mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
mysqli_stmt_execute($stmt);
$daily_clicks_result = mysqli_stmt_get_result($stmt);

// Get category performance
$category_performance_query = "SELECT c.name, COUNT(ac.id) as clicks,
                              COUNT(CASE WHEN ac.converted = 1 THEN ac.id END) as conversions,
                              AVG(p.price) as avg_price
                              FROM affiliate_categories c
                              LEFT JOIN affiliate_products p ON c.id = p.category_id
                              LEFT JOIN affiliate_clicks ac ON p.id = ac.product_id
                              WHERE DATE(ac.click_date) BETWEEN ? AND ? OR ac.click_date IS NULL
                              GROUP BY c.id, c.name
                              HAVING clicks > 0
                              ORDER BY clicks DESC";

$stmt = mysqli_prepare($conn, $category_performance_query);
mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
mysqli_stmt_execute($stmt);
$category_performance_result = mysqli_stmt_get_result($stmt);

// Get network performance
$network_performance_query = "SELECT p.affiliate_network, COUNT(ac.id) as clicks,
                             COUNT(CASE WHEN ac.converted = 1 THEN ac.id END) as conversions,
                             AVG(p.price) as avg_price
                             FROM affiliate_products p
                             LEFT JOIN affiliate_clicks ac ON p.id = ac.product_id
                             WHERE DATE(ac.click_date) BETWEEN ? AND ? OR ac.click_date IS NULL
                             GROUP BY p.affiliate_network
                             ORDER BY clicks DESC";

$stmt = mysqli_prepare($conn, $network_performance_query);
mysqli_stmt_bind_param($stmt, 'ss', $start_date, $end_date);
mysqli_stmt_execute($stmt);
$network_performance_result = mysqli_stmt_get_result($stmt);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Affiliate Analytics Dashboard
                    </h3>
                    <div class="card-tools">
                        <form method="GET" class="d-flex gap-2">
                            <input type="date" class="form-control form-control-sm" name="start_date" 
                                   value="<?php echo htmlspecialchars($start_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                            <input type="date" class="form-control form-control-sm" name="end_date" 
                                   value="<?php echo htmlspecialchars($end_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="affiliate-analytics.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <!-- Key Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="card-title"><?php echo number_format($stats['total_clicks']); ?></h4>
                                            <p class="card-text">Total Clicks</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-mouse-pointer fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="card-title"><?php echo number_format($stats['total_conversions']); ?></h4>
                                            <p class="card-text">Conversions</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-shopping-cart fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="card-title"><?php echo $conversion_rate; ?>%</h4>
                                            <p class="card-text">Conversion Rate</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-percentage fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="card-title"><?php echo number_format($stats['products_with_clicks']); ?></h4>
                                            <p class="card-text">Active Products</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-box fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-area"></i> Daily Performance
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="dailyChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie"></i> Network Performance
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="networkChart" width="200" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products Table -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-trophy"></i> Top Performing Products
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Category</th>
                                                    <th>Clicks</th>
                                                    <th>Conversions</th>
                                                    <th>Conversion Rate</th>
                                                    <th>Revenue</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($product = mysqli_fetch_assoc($top_products_result)): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if (!empty($product['image_url'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                                                         style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px; border-radius: 4px;">
                                                                <?php endif; ?>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                                                    <?php if (!empty($product['brand'])): ?>
                                                                        <br><small class="text-muted"><?php echo htmlspecialchars($product['brand']); ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></td>
                                                        <td>
                                                            <span class="badge badge-primary"><?php echo number_format($product['clicks']); ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-success"><?php echo number_format($product['conversions']); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $product_conversion_rate = $product['clicks'] > 0 ? round(($product['conversions'] / $product['clicks']) * 100, 2) : 0;
                                                            echo $product_conversion_rate . '%';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $estimated_revenue = $product['conversions'] * ($product['price'] * 0.05); // Assuming 5% commission
                                                            echo format_product_price($estimated_revenue, $product['currency'] ?: 'USD');
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="../products.php?edit=<?php echo $product['id']; ?>" 
                                                                   class="btn btn-outline-primary btn-sm" 
                                                                   target="_blank" title="View Product">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="<?php echo generate_affiliate_link($product['id'], $product['affiliate_url']); ?>" 
                                                                   class="btn btn-success btn-sm" 
                                                                   target="_blank" 
                                                                   title="Buy Product">
                                                                    <i class="fas fa-shopping-cart"></i> Buy
                                                                </a>
                                                                <a href="manage-affiliate-products.php?edit=<?php echo $product['id']; ?>" 
                                                                   class="btn btn-outline-warning btn-sm" 
                                                                   title="Edit Product">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Category Performance -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-tags"></i> Category Performance
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Clicks</th>
                                                    <th>Conv.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($category = mysqli_fetch_assoc($category_performance_result)): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                        <td><?php echo number_format($category['clicks']); ?></td>
                                                        <td><?php echo number_format($category['conversions']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Network Performance -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-network-wired"></i> Network Performance
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Network</th>
                                                    <th>Clicks</th>
                                                    <th>Conv.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($network = mysqli_fetch_assoc($network_performance_result)): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge badge-<?php echo ($network['affiliate_network'] === 'amazon') ? 'warning' : 'danger'; ?>">
                                                                <?php echo ucfirst($network['affiliate_network']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo number_format($network['clicks']); ?></td>
                                                        <td><?php echo number_format($network['conversions']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Daily Performance Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
const dailyData = <?php
    $data = [];
    while ($row = mysqli_fetch_assoc($daily_clicks_result)) {
        $data[] = [
            date('M j', strtotime($row['date'])),
            $row['clicks'],
            $row['conversions']
        ];
    }
    echo json_encode($data);
?>;

new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailyData.map(item => item[0]),
        datasets: [{
            label: 'Clicks',
            data: dailyData.map(item => item[1]),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Conversions',
            data: dailyData.map(item => item[2]),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Network Performance Pie Chart
const networkCtx = document.getElementById('networkChart').getContext('2d');
const networkData = <?php
    mysqli_data_seek($network_performance_result, 0);
    $data = [];
    while ($row = mysqli_fetch_assoc($network_performance_result)) {
        if ($row['clicks'] > 0) {
            $data[] = [
                ucfirst($row['affiliate_network']),
                $row['clicks']
            ];
        }
    }
    echo json_encode($data);
?>;

new Chart(networkCtx, {
    type: 'doughnut',
    data: {
        labels: networkData.map(item => item[0]),
        datasets: [{
            data: networkData.map(item => item[1]),
            backgroundColor: [
                'rgba(255, 206, 86, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>
