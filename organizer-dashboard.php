<?php
require_once 'config/database.php';
session_start();

// Check if organizer is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_type'] != 'organizer') {
    header('Location: admin-login.php');
    exit;
}

// Get organizer's events and stats
$organizer_name = $_SESSION['admin_name'];

// Get events created by this organizer
$events = $pdo->query("
    SELECT e.*, 
           (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) as tickets_sold,
           (SELECT SUM(total_amount) FROM tickets WHERE event_id = e.id) as revenue
    FROM events e
    ORDER BY e.created_at DESC
")->fetchAll();

// Get total sales for this organizer
$total_tickets = 0;
$total_revenue = 0;
foreach ($events as $event) {
    $total_tickets += $event['tickets_sold'];
    $total_revenue += $event['revenue'];
}

// Get recent sales for this organizer's events
$recent_sales = $pdo->query("
    SELECT t.*, e.title 
    FROM tickets t 
    JOIN events e ON t.event_id = e.id 
    ORDER BY t.created_at DESC 
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Organizer Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="dashboard-header organizer-header">
            <div>
                <span class="role-badge organizer">🎪 ORGANIZER</span>
                <h1>Welcome, <?php echo htmlspecialchars($organizer_name); ?></h1>
            </div>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-number"><?php echo count($events); ?></div>
                <div class="stat-label">My Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎫</div>
                <div class="stat-number"><?php echo $total_tickets; ?></div>
                <div class="stat-label">Tickets Sold</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number">RWF <?php echo number_format($total_revenue ?: 0); ?></div>
                <div class="stat-label">My Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-number">4.5</div>
                <div class="stat-label">Average Rating</div>
            </div>
        </div>

        <!-- My Events -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>My Events</h3>
                <a href="create-event.php" class="btn-small">+ Add New</a>
            </div>

            <div class="events-list">
                <?php foreach ($events as $event): ?>
                    <div class="event-item">
                        <div class="event-info">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <div class="event-meta">
                                <span>📅 <?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                <span>📍 <?php echo htmlspecialchars($event['venue']); ?></span>
                            </div>
                        </div>
                        <div class="event-stats">
                            <div class="stat-badge">
                                <span>🎫 <?php echo $event['tickets_sold']; ?></span>
                            </div>
                            <div class="stat-badge">
                                <span>💰 RWF <?php echo number_format($event['revenue'] ?: 0); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Sales for My Events -->
        <div class="dashboard-card">
            <h3>Recent Ticket Sales</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Event</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_sales as $sale): ?>
                            <tr>
                                <td><code><?php echo $sale['ticket_number']; ?></code></td>
                                <td><?php echo htmlspecialchars(substr($sale['title'], 0, 20)); ?></td>
                                <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                <td>RWF <?php echo number_format($sale['total_amount']); ?></td>
                                <td><?php echo date('M j, H:i', strtotime($sale['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions for Organizer -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="create-event.php" class="action-btn">
                    <span>➕</span>
                    Create New Event
                </a>
                <a href="my-events.php" class="action-btn">
                    <span>📋</span>
                    Manage Events
                </a>
                <a href="earnings.php" class="action-btn">
                    <span>💰</span>
                    View Earnings
                </a>
            </div>
        </div>
        <!-- Quick Actions for Organizer -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="create-event.php" class="action-btn">
                    <span>➕</span>
                    Create New Event
                </a>
                <a href="my-events.php" class="action-btn">
                    <span>📋</span>
                    My Events
                </a>
                <a href="earnings.php" class="action-btn">
                    <span>💰</span>
                    View Earnings
                </a>
            </div>
        </div>
    </div>
</body>

</html>