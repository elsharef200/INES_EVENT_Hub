<?php 
require_once 'config/database.php';
session_start();

// Check if organizer is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_type'] != 'organizer') {
    header('Location: admin-login.php');
    exit;
}

$organizer_name = $_SESSION['admin_name'];

// Get earnings by event
$earnings = $pdo->query("
    SELECT 
        e.title,
        e.price,
        e.event_date,
        COUNT(t.id) as tickets_sold,
        COALESCE(SUM(t.total_amount), 0) as gross_revenue,
        COALESCE(SUM(t.total_amount) * 0.95, 0) as net_revenue,
        COALESCE(SUM(t.total_amount) * 0.05, 0) as platform_fee
    FROM events e
    LEFT JOIN tickets t ON e.id = t.event_id
    GROUP BY e.id
    ORDER BY e.event_date DESC
")->fetchAll();

// Calculate totals
$total_gross = 0;
$total_net = 0;
$total_fees = 0;
$total_tickets = 0;

foreach ($earnings as $event) {
    $total_gross += $event['gross_revenue'];
    $total_net += $event['net_revenue'];
    $total_fees += $event['platform_fee'];
    $total_tickets += $event['tickets_sold'];
}

// Get recent payouts (if you have a payouts table)
$payouts = $pdo->query("
    SELECT * FROM payouts 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Earnings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .earnings-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .earnings-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
        }
        
        .earnings-card .amount {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .fee-breakdown {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .fee-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .fee-item.total {
            font-weight: bold;
            font-size: 18px;
            border-bottom: none;
        }
        
        .payout-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .payout-completed { background: #d4edda; color: #155724; }
        .payout-pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header organizer-header">
            <div>
                <span class="role-badge organizer">🎪 ORGANIZER</span>
                <h1>My Earnings</h1>
            </div>
            <a href="organizer-dashboard.php" class="logout-link">← Back to Dashboard</a>
        </div>
        
        <!-- Summary Cards -->
        <div class="earnings-summary">
            <div class="earnings-card">
                <div>Total Tickets Sold</div>
                <div class="amount"><?php echo $total_tickets; ?></div>
            </div>
            <div class="earnings-card">
                <div>Gross Revenue</div>
                <div class="amount">RWF <?php echo number_format($total_gross); ?></div>
            </div>
            <div class="earnings-card">
                <div>Platform Fees (5%)</div>
                <div class="amount">RWF <?php echo number_format($total_fees); ?></div>
            </div>
            <div class="earnings-card">
                <div>Your Earnings</div>
                <div class="amount">RWF <?php echo number_format($total_net); ?></div>
            </div>
        </div>
        
        <!-- Fee Breakdown -->
        <div class="fee-breakdown">
            <h3>How It Works</h3>
            <div class="fee-item">
                <span>Ticket Sales (Gross)</span>
                <span>RWF <?php echo number_format($total_gross); ?></span>
            </div>
            <div class="fee-item">
                <span>Platform Fee (5%)</span>
                <span>- RWF <?php echo number_format($total_fees); ?></span>
            </div>
            <div class="fee-item total">
                <span>Your Net Earnings</span>
                <span>RWF <?php echo number_format($total_net); ?></span>
            </div>
        </div>
        
        <!-- Earnings by Event -->
        <div class="dashboard-card">
            <h3>Earnings by Event</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Tickets</th>
                            <th>Gross</th>
                            <th>Fee (5%)</th>
                            <th>Your Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($earnings as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                            <td><?php echo $event['tickets_sold']; ?></td>
                            <td>RWF <?php echo number_format($event['gross_revenue']); ?></td>
                            <td>RWF <?php echo number_format($event['platform_fee']); ?></td>
                            <td><strong>RWF <?php echo number_format($event['net_revenue']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Payouts -->
        <div class="dashboard-card">
            <h3>Recent Payouts</h3>
            <?php if(empty($payouts)): ?>
                <p class="no-data">No payouts yet. Withdrawals are processed every Monday.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payouts as $payout): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($payout['created_at'])); ?></td>
                            <td>RWF <?php echo number_format($payout['amount']); ?></td>
                            <td><?php echo $payout['payment_method']; ?></td>
                            <td>
                                <span class="payout-badge payout-<?php echo $payout['status']; ?>">
                                    <?php echo ucfirst($payout['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <button class="btn-primary" onclick="requestPayout()">Request Payout</button>
                <p style="color: #666; font-size: 13px; margin-top: 10px;">
                    Minimum payout: RWF 10,000 • Processed within 2-3 business days
                </p>
            </div>
        </div>
    </div>
    
    <script>
    function requestPayout() {
        alert('Payout request feature coming soon!');
    }
    </script>
</body>
</html>