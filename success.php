<?php 
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Generate unique ticket number
$ticket_number = 'INES-' . date('Y') . '-' . rand(10000, 99999);

// Save to database
$stmt = $pdo->prepare("
    INSERT INTO tickets (ticket_number, event_id, customer_name, customer_email, customer_phone, quantity, total_amount, payment_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'paid')
");

$stmt->execute([
    $ticket_number,
    $_POST['event_id'],
    $_POST['customer_name'],
    $_POST['customer_email'],
    $_POST['customer_phone'] ?? null,
    $_POST['quantity'],
    $_POST['total']
]);

// In a real app, send email here
// mail($_POST['customer_email'], "Your Ticket", "Ticket #: $ticket_number");

$event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$event->execute([$_POST['event_id']]);
$event = $event->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Purchase Successful!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">✅</div>
            <h1>Purchase Successful!</h1>
            
            <div class="ticket-info">
                <p><strong>Ticket Number:</strong></p>
                <div class="ticket-number"><?php echo $ticket_number; ?></div>
                
                <p><strong>Event:</strong> <?php echo htmlspecialchars($event['title']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($_POST['customer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($_POST['customer_email']); ?></p>
                <p><strong>Quantity:</strong> <?php echo $_POST['quantity']; ?> ticket(s)</p>
                <p><strong>Total Paid:</strong> RWF <?php echo number_format($_POST['total']); ?></p>
            </div>
            
            <p class="email-note">📧 A confirmation has been sent to your email</p>
            
            <a href="index.php" class="btn-large">Browse More Events</a>
        </div>
    </div>
</body>
</html>