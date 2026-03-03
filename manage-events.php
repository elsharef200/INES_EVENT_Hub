<?php 
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}

$user_type = $_SESSION['admin_type'];
$error = '';
$success = '';

// Handle delete event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // First delete related tickets
    $pdo->prepare("DELETE FROM tickets WHERE event_id = ?")->execute([$id]);
    // Then delete event
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        $success = 'Event deleted successfully';
    } else {
        $error = 'Failed to delete event';
    }
}

// Get events
if ($user_type == 'admin') {
    // Admin sees all events
    $events = $pdo->query("
        SELECT e.*, 
               (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) as tickets_sold,
               (SELECT SUM(total_amount) FROM tickets WHERE event_id = e.id) as revenue
        FROM events e
        ORDER BY e.event_date DESC
    ")->fetchAll();
} else {
    // Organizer sees only their events
    $events = $pdo->query("
        SELECT e.*, 
               (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) as tickets_sold,
               (SELECT SUM(total_amount) FROM tickets WHERE event_id = e.id) as revenue
        FROM events e
        ORDER BY e.event_date DESC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .event-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-upcoming { background: #d4edda; color: #155724; }
        .status-ongoing { background: #fff3cd; color: #856404; }
        .status-past { background: #f8d7da; color: #721c24; }
        
        .edit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            margin-right: 5px;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div>
                <span class="role-badge <?php echo $user_type; ?>">
                    <?php echo $user_type == 'admin' ? '👑 ADMIN' : '🎪 ORGANIZER'; ?>
                </span>
                <h1>Manage Events</h1>
            </div>
            <div>
                <a href="create-event.php" class="logout-link" style="margin-right: 10px;">+ New Event</a>
                <a href="<?php echo $user_type == 'admin' ? 'admin-dashboard.php' : 'organizer-dashboard.php'; ?>" class="logout-link">← Back</a>
            </div>
        </div>
        
        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success-message">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="dashboard-card">
            <h3>All Events</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Price</th>
                            <th>Tickets Sold</th>
                            <th>Revenue</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($events as $event): 
                            $today = date('Y-m-d');
                            if ($event['event_date'] > $today) {
                                $status = 'upcoming';
                                $status_text = 'Upcoming';
                            } elseif ($event['event_date'] == $today) {
                                $status = 'ongoing';
                                $status_text = 'Today';
                            } else {
                                $status = 'past';
                                $status_text = 'Past';
                            }
                        ?>
                        <tr>
                            <td>#<?php echo $event['id']; ?></td>
                            <td><?php echo htmlspecialchars(substr($event['title'], 0, 30)); ?>..</td>
                            <td><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                            <td><?php echo htmlspecialchars(substr($event['venue'], 0, 20)); ?></td>
                            <td>RWF <?php echo number_format($event['price']); ?></td>
                            <td><?php echo $event['tickets_sold']; ?></td>
                            <td>RWF <?php echo number_format($event['revenue'] ?: 0); ?></td>
                            <td><span class="event-status status-<?php echo $status; ?>"><?php echo $status_text; ?></span></td>
                            <td>
                                <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="edit-btn">Edit</a>
                                <a href="?delete=<?php echo $event['id']; ?>" class="delete-btn" onclick="return confirm('Delete this event? All ticket data will be lost.')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>