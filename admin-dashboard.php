<?php
require_once 'config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_type'] != 'admin') {
    header('Location: admin-login.php');
    exit;
}

// Get system-wide stats
$total_tickets = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM tickets")->fetchColumn();
$total_events = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$total_organizers = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'organizer'")->fetchColumn();

// Get recent sales
$recent_sales = $pdo->query("
    SELECT t.*, e.title 
    FROM tickets t 
    JOIN events e ON t.event_id = e.id 
    ORDER BY t.created_at DESC 
    LIMIT 10
")->fetchAll();

// Get top events
$top_events = $pdo->query("
    SELECT e.title, COUNT(t.id) as tickets_sold, SUM(t.total_amount) as revenue
    FROM events e
    LEFT JOIN tickets t ON e.id = t.event_id
    GROUP BY e.id
    ORDER BY tickets_sold DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="dashboard-header admin-header">
            <div>
                <span class="role-badge admin">👑 ADMIN</span>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
            </div>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎫</div>
                <div class="stat-number"><?php echo $total_tickets; ?></div>
                <div class="stat-label">Total Tickets Sold</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number">RWF <?php echo number_format($total_revenue ?: 0); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-number"><?php echo $total_events; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎪</div>
                <div class="stat-number"><?php echo $total_organizers; ?></div>
                <div class="stat-label">Organizers</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Recent Sales -->
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td><code><?php echo $sale['ticket_number']; ?></code></td>
                                    <td><?php echo htmlspecialchars(substr($sale['title'], 0, 20)); ?>..</td>
                                    <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                    <td>RWF <?php echo number_format($sale['total_amount']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Events -->
            <div class="dashboard-card">
                <h3>Top Performing Events</h3>
                <div class="top-events-list">
                    <?php foreach ($top_events as $event): ?>
                        <div class="top-event-item">
                            <div class="event-rank">🎯</div>
                            <div class="event-details">
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <div class="event-metrics">
                                    <span>🎫 <?php echo $event['tickets_sold'] ?: 0; ?> tickets</span>
                                    <span>💰 RWF <?php echo number_format($event['revenue'] ?: 0); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="create-event.php" class="action-btn">
                    <span>➕</span>
                    Add New Event
                </a>
                <a href="manage-organizers.php" class="action-btn">
                    <span>👥</span>
                    Manage Organizers
                </a>
                <a href="view-reports.php" class="action-btn">
                    <span>📊</span>
                    View Reports
                </a>
            </div>
        </div>
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="create-event.php" class="action-btn">
                    <span>➕</span>
                    Create New Event
                </a>
                <a href="manage-events.php" class="action-btn">
                    <span>📋</span>
                    Manage Events
                </a>
                <a href="view-reports.php" class="action-btn">
                    <span>📊</span>
                    View Reports
                </a>
            </div>
        </div>
    </div>
</body>

</html>