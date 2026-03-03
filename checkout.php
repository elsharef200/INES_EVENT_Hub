<?php 
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$event_id = $_POST['event_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

$event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$event->execute([$event_id]);
$event = $event->fetch();

if (!$event) {
    header('Location: index.php');
    exit;
}

$total = $event['price'] * $quantity;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="event.php?id=<?php echo $event_id; ?>" class="back-link">← Back</a>
        
        <div class="checkout-card">
            <h2>Complete Your Purchase</h2>
            
            <div class="order-summary">
                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                <p>Quantity: <?php echo $quantity; ?> ticket(s)</p>
                <p>Price per ticket: RWF <?php echo number_format($event['price']); ?></p>
                <hr>
                <p class="total"><strong>Total: RWF <?php echo number_format($total); ?></strong></p>
            </div>
            
            <form action="success.php" method="POST" class="checkout-form">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                <input type="hidden" name="total" value="<?php echo $total; ?>">
                
                <div class="form-group">
                    <label>Your Full Name *</label>
                    <input type="text" name="customer_name" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="customer_email" required>
                    <small>Your ticket will be sent here</small>
                </div>
                
                <div class="form-group">
                    <label>Phone Number (optional)</label>
                    <input type="tel" name="customer_phone">
                </div>
                
                <button type="submit" class="btn-large">Confirm Purchase</button>
            </form>
        </div>
    </div>
</body>
</html>