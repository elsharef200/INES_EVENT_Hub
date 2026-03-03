<?php require_once 'config/database.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>INES Event Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 INES Event Hub</h1>
            <a href="admin-login.php" class="admin-link">Admin Login</a>
        </div>
        
        <div class="events-grid">
            <?php
            $events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll();
            foreach($events as $event):
            ?>
            <div class="event-card">
                <div class="event-date">📅 <?php echo date('M j, Y', strtotime($event['event_date'])); ?></div>
                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                <p><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                <div class="event-footer">
                    <span class="price">RWF <?php echo number_format($event['price']); ?></span>
                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn">View</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>