<?php 
require_once 'config/database.php';
session_start();

// Check if organizer is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_type'] != 'organizer') {
    header('Location: admin-login.php');
    exit;
}

$organizer_id = $_SESSION['admin_id'];

// Get only this organizer's events - FIXED: Proper prepare, execute, fetch
$stmt = $pdo->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) as tickets_sold,
           COALESCE((SELECT SUM(total_amount) FROM tickets WHERE event_id = e.id), 0) as revenue
    FROM events e
    ORDER BY e.event_date DESC
");
$stmt->execute();
$events = $stmt->fetchAll();

// If you want to filter by organizer_id (if you have that column)
// Add: WHERE e.organizer_id = ? to the query and pass parameter
// $stmt->execute([$organizer_id]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
        }
        
        .event-card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
        }
        
        .event-card-header h3 {
            margin-bottom: 5px;
            font-size: 18px;
        }
        
        .event-card-body {
            padding: 15px;
        }
        
        .event-details {
            margin-bottom: 15px;
        }
        
        .event-details p {
            margin-bottom: 5px;
            color: #666;
        }
        
        .event-stats {
            display: flex;
            justify-content: space-around;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        
        .event-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .event-actions a {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        
        .event-actions a:hover {
            opacity: 0.8;
        }
        
        .view-btn {
            background: #667eea;
            color: white;
        }
        
        .edit-btn {
            background: #28a745;
            color: white;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            margin-top: 20px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .btn-primary {
            display: inline-block;
            background: #667eea;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
        }
        
        .btn-primary:hover {
            background: #764ba2;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            color: white;
        }
        
        .organizer-header {
            background: linear-gradient(135deg, #764ba2, #9f7aea);
        }
        
        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .role-badge.organizer {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .logout-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .logout-link:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header organizer-header">
            <div>
                <span class="role-badge organizer">🎪 ORGANIZER</span>
                <h1>My Events</h1>
            </div>
            <div>
                <a href="create-event.php" class="logout-link" style="margin-right: 10px;">+ Create New</a>
                <a href="organizer-dashboard.php" class="logout-link">← Back</a>
            </div>
        </div>
        
        <?php if(empty($events)): ?>
            <div class="empty-state">
                <p>You haven't created any events yet.</p>
                <a href="create-event.php" class="btn-primary">Create Your First Event</a>
            </div>
        <?php else: ?>
            <div class="event-grid">
                <?php foreach($events as $event): ?>
                <div class="event-card">
                    <div class="event-card-header">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <small><?php echo date('M j, Y', strtotime($event['event_date'])); ?></small>
                    </div>
                    <div class="event-card-body">
                        <div class="event-details">
                            <p><strong>📍 Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                            <p><strong>💰 Price:</strong> RWF <?php echo number_format($event['price']); ?></p>
                        </div>
                        
                        <div class="event-stats">
                            <div class="stat">
                                <div class="stat-value"><?php echo $event['tickets_sold'] ?: 0; ?></div>
                                <div class="stat-label">Tickets Sold</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">RWF <?php echo number_format($event['revenue'] ?: 0); ?></div>
                                <div class="stat-label">Revenue</div>
                            </div>
                        </div>
                        
                        <div class="event-actions">
                            <a href="../event.php?id=<?php echo $event['id']; ?>" class="view-btn" target="_blank">View</a>
                            <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="edit-btn">Edit</a>
                            <a href="manage-events.php?delete=<?php echo $event['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>