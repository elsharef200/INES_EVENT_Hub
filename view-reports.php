<?php 
require_once 'config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_type'] != 'admin') {
    header('Location: admin-login.php');
    exit;
}

// Get date range
$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end'] ?? date('Y-m-d');

// Get summary stats - FIXED: Proper prepare, execute, fetch
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT t.id) as total_transactions,
        COUNT(t.id) as total_tickets,
        COALESCE(SUM(t.total_amount), 0) as total_revenue,
        COUNT(DISTINCT e.id) as events_with_sales,
        COUNT(DISTINCT t.customer_email) as unique_customers
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE DATE(t.created_at) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();

// Get daily sales for chart - FIXED
$stmt = $pdo->prepare("
    SELECT 
        DATE(t.created_at) as date,
        COUNT(*) as tickets,
        SUM(t.total_amount) as revenue
    FROM tickets t
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY DATE(t.created_at)
    ORDER BY date
");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll();

// Get top events - FIXED
$stmt = $pdo->prepare("
    SELECT 
        e.title,
        COUNT(t.id) as tickets_sold,
        COALESCE(SUM(t.total_amount), 0) as revenue,
        COUNT(DISTINCT t.customer_email) as unique_buyers
    FROM events e
    LEFT JOIN tickets t ON e.id = t.event_id AND DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY e.id
    ORDER BY revenue DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$top_events = $stmt->fetchAll();

// Get top customers - FIXED
$stmt = $pdo->prepare("
    SELECT 
        t.customer_name,
        t.customer_email,
        COUNT(*) as purchases,
        SUM(t.total_amount) as total_spent
    FROM tickets t
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY t.customer_email
    ORDER BY total_spent DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$top_customers = $stmt->fetchAll();

// If no data, set defaults
if (!$summary) {
    $summary = [
        'total_transactions' => 0,
        'total_tickets' => 0,
        'total_revenue' => 0,
        'events_with_sales' => 0,
        'unique_customers' => 0
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-filters {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .report-stat {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .report-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .export-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .export-btn:hover {
            background: #218838;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header admin-header">
            <div>
                <span class="role-badge admin">👑 ADMIN</span>
                <h1>Sales Reports</h1>
            </div>
            <a href="admin-dashboard.php" class="logout-link">← Back to Dashboard</a>
        </div>
        
        <!-- Date Filter -->
        <div class="report-filters">
            <form method="GET" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>Start Date</label>
                    <input type="date" name="start" value="<?php echo $start_date; ?>" class="form-control">
                </div>
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>End Date</label>
                    <input type="date" name="end" value="<?php echo $end_date; ?>" class="form-control">
                </div>
                <button type="submit" class="btn-primary" style="height: 45px;">Apply</button>
                <button type="button" class="export-btn" onclick="exportReport()">📥 Export CSV</button>
            </form>
        </div>
        
        <!-- Summary Stats -->
        <div class="report-grid">
            <div class="report-stat">
                <div class="stat-value">RWF <?php echo number_format($summary['total_revenue']); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="report-stat">
                <div class="stat-value"><?php echo $summary['total_tickets']; ?></div>
                <div class="stat-label">Tickets Sold</div>
            </div>
            <div class="report-stat">
                <div class="stat-value"><?php echo $summary['total_transactions']; ?></div>
                <div class="stat-label">Transactions</div>
            </div>
            <div class="report-stat">
                <div class="stat-value"><?php echo $summary['unique_customers']; ?></div>
                <div class="stat-label">Unique Customers</div>
            </div>
        </div>
        
        <!-- Sales Chart -->
        <div class="chart-container">
            <h3 style="margin-bottom: 20px;">Daily Sales</h3>
            <?php if(empty($daily_sales)): ?>
                <div class="no-data">No sales data available for this period</div>
            <?php else: ?>
                <canvas id="salesChart" style="width:100%; height:300px;"></canvas>
            <?php endif; ?>
        </div>
        
        <!-- Top Events -->
        <div class="report-section">
            <h3 style="margin-bottom: 20px;">Top Performing Events</h3>
            <?php if(empty($top_events)): ?>
                <div class="no-data">No event data available</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Tickets Sold</th>
                                <th>Revenue</th>
                                <th>Unique Buyers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo $event['tickets_sold'] ?: 0; ?></td>
                                <td>RWF <?php echo number_format($event['revenue'] ?: 0); ?></td>
                                <td><?php echo $event['unique_buyers'] ?: 0; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Top Customers -->
        <div class="report-section">
            <h3 style="margin-bottom: 20px;">Top Customers</h3>
            <?php if(empty($top_customers)): ?>
                <div class="no-data">No customer data available</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Purchases</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['customer_email']); ?></td>
                                <td><?php echo $customer['purchases']; ?></td>
                                <td>RWF <?php echo number_format($customer['total_spent']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    <?php if(!empty($daily_sales)): ?>
    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [<?php 
                $labels = [];
                foreach($daily_sales as $day) {
                    $labels[] = "'" . date('M j', strtotime($day['date'])) . "'";
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Revenue (RWF)',
                data: [<?php 
                    $revenues = [];
                    foreach($daily_sales as $day) {
                        $revenues[] = $day['revenue'];
                    }
                    echo implode(',', $revenues);
                ?>],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RWF ' + value;
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    function exportReport() {
        // Simple CSV export
        let csv = "Date,Revenue,Tickets\n";
        <?php foreach($daily_sales as $day): ?>
        csv += "<?php echo $day['date']; ?>,<?php echo $day['revenue']; ?>,<?php echo $day['tickets']; ?>\n";
        <?php endforeach; ?>
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'sales-report-<?php echo $start_date; ?>-to-<?php echo $end_date; ?>.csv';
        a.click();
    }
    </script>
</body>
</html>