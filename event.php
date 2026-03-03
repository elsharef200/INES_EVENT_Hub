<?php 
require_once 'config/database.php';
$id = $_GET['id'] ?? 0;
$event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$event->execute([$id]);
$event = $event->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($event['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Back to Events</a>
        
        <div class="event-detail">
            <h1><?php echo htmlspecialchars($event['title']); ?></h1>
            
            <div class="event-info">
                <p>📅 Date: <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                <p>⏰ Time: <?php echo date('g:i A', strtotime($event['event_time'])); ?></p>
                <p>📍 Venue: <?php echo htmlspecialchars($event['venue']); ?></p>
                <p class="price-tag">RWF <?php echo number_format($event['price']); ?></p>
            </div>
            
            <div class="event-description">
                <h3>About this event</h3>
                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
            
            <form action="checkout.php" method="POST" class="buy-form">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                <div class="form-group">
                    <label>Number of Tickets:</label>
                    <input type="number" name="quantity" min="1" max="10" value="1" required>
                </div>
                <button type="submit" class="btn-large">Buy Now</button>
            </form>
        </div>
    </div>
</body>
</html>