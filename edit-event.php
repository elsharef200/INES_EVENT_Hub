<?php 
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}

$event_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Get event details
$event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$event->execute([$event_id]);
$event = $event->fetch();

if (!$event) {
    header('Location: manage-events.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $venue = $_POST['venue'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    
    $update = $pdo->prepare("
        UPDATE events 
        SET title=?, description=?, price=?, venue=?, event_date=?, event_time=?
        WHERE id=?
    ");
    
    if ($update->execute([$title, $description, $price, $venue, $event_date, $event_time, $event_id])) {
        $success = 'Event updated successfully';
        // Refresh event data
        $event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $event->execute([$event_id]);
        $event = $event->fetch();
    } else {
        $error = 'Failed to update event';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <h1>Edit Event</h1>
            <a href="manage-events.php" class="logout-link">← Back to Events</a>
        </div>
        
        <div class="create-event-card">
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success-message">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Event Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label>Price (RWF)</label>
                        <input type="number" name="price" value="<?php echo $event['price']; ?>" required>
                    </div>
                    
                    <div class="form-group half">
                        <label>Venue</label>
                        <input type="text" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label>Event Date</label>
                        <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                    </div>
                    
                    <div class="form-group half">
                        <label>Event Time</label>
                        <input type="time" name="event_time" value="<?php echo $event['event_time']; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn-large">Update Event</button>
            </form>
        </div>
    </div>
</body>
</html>