<?php
require_once 'config.php';

// Verify administrator privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: login.php");
    exit();
}

try {
    // Get payment statistics
    $paymentStatsStmt = $conn->query("
        SELECT 
            payment_method,
            COUNT(*) AS transactions,
            SUM(amount) AS total_amount
        FROM payments
        WHERE status = 'completed'
        GROUP BY payment_method
    ");
    $paymentStats = $paymentStatsStmt->fetchAll();

    // Get daily revenue data for chart
    $dailyRevenueStmt = $conn->query("
        SELECT 
            DATE(payment_date) AS day,
            SUM(amount) AS revenue
        FROM payments
        WHERE status = 'completed'
        AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(payment_date)
        ORDER BY day
    ");
    $dailyRevenue = $dailyRevenueStmt->fetchAll();

    // Prepare data for chart
    $chartLabels = [];
    $chartData = [];
    foreach ($dailyRevenue as $day) {
        $chartLabels[] = date('M j', strtotime($day['day']));
        $chartData[] = $day['revenue'];
    }

} catch(PDOException $e) {
    error_log("Financial reports error: " . $e->getMessage());
    $error = "Error loading financial data";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            margin-bottom: 20px;
        }
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4">Financial Reports</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Daily Revenue (Last 30 Days)</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Revenue Summary</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalRevenue = 0;
                        $totalTransactions = 0;
                        foreach ($paymentStats as $stat) {
                            $totalRevenue += $stat['total_amount'];
                            $totalTransactions += $stat['transactions'];
                        }
                        ?>
                        <div class="mb-3">
                            <h5>Total Revenue</h5>
                            <p class="h4 text-success">KES <?= number_format($totalRevenue, 2) ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h5>Total Transactions</h5>
                            <p class="h4 text-primary"><?= number_format($totalTransactions) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Payment Methods</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Transactions</th>
                                <th>Total Amount</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paymentStats as $stat): ?>
                            <tr>
                                <td><?= ucfirst(str_replace('_', ' ', $stat['payment_method'])) ?></td>
                                <td><?= number_format($stat['transactions']) ?></td>
                                <td>KES <?= number_format($stat['total_amount'], 2) ?></td>
                                <td><?= $totalRevenue > 0 ? number_format(($stat['total_amount'] / $totalRevenue) * 100, 1) : 0 ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: 'Daily Revenue (KES)',
                        data: <?= json_encode($chartData) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'KES ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>