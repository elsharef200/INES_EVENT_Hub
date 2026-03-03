<?php 
require_once 'config/database.php';
session_start();

// Check if user is logged in (either admin or organizer)
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}

$user_type = $_SESSION['admin_type'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $venue = $_POST['venue'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    
    // Simple validation
    if (empty($title) || empty($price) || empty($venue) || empty($event_date)) {
        $error = 'Please fill in all required fields';
    } else {
        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO events (title, description, price, venue, event_date, event_time) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$title, $description, $price, $venue, $event_date, $event_time])) {
            $success = 'Event created successfully!';
        } else {
            $error = 'Failed to create event';
        }
    }
}

// Get today's date for min attribute
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create New Event</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div>
                <span class="role-badge <?php echo $user_type; ?>">
                    <?php echo $user_type == 'admin' ? '👑 ADMIN' : '🎪 ORGANIZER'; ?>
                </span>
                <h1>Create New Event</h1>
            </div>
            <a href="<?php echo $user_type == 'admin' ? 'admin-dashboard.php' : 'organizer-dashboard.php'; ?>" class="logout-link">← Back to Dashboard</a>
        </div>
        
        <div class="create-event-card">
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success-message">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="create-event-form">
                <div class="form-group">
                    <label>Event Title *</label>
                    <input type="text" name="title" placeholder="e.g., Kigali Music Festival" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Describe your event..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label>Price (RWF) *</label>
                        <input type="number" name="price" min="0" step="100" placeholder="25000" required>
                    </div>
                    
                    <div class="form-group half">
                        <label>Venue *</label>
                        <input type="text" name="venue" placeholder="BK Arena" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label>Event Date *</label>
                        <input type="date" name="event_date" min="<?php echo $today; ?>" required>
                    </div>
                    
                    <div class="form-group half">
                        <label>Event Time</label>
                        <input type="time" name="event_time" value="18:00">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-large">Create Event</button>
                    <a href="<?php echo $user_type == 'admin' ? 'admin-dashboard.php' : 'organizer-dashboard.php'; ?>" class="btn-secondary">Cancel</a>
                </div>
            </form>
            
            <div class="form-tips">
                <h4>📝 Tips:</h4>
                <ul>
                    <li>Use a clear, descriptive title</li>
                    <li>Add details about what attendees can expect</li>
                    <li>Make sure the date is correct</li>
                    <li>You can't edit events after creation (for now)</li>
                </ul>
            </div>
        </div>
    </div>
    
    <style>
    .create-event-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 600px;
        margin: 20px auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .form-group.half {
        flex: 1;
    }
    
    textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-family: inherit;
        font-size: 16px;
    }
    
    textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .btn-secondary {
        flex: 1;
        background: #6c757d;
        color: white;
        text-decoration: none;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        font-weight: bold;
        transition: 0.3s;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    .form-tips {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    
    .form-tips ul {
        margin-top: 10px;
        padding-left: 20px;
        color: #666;
    }
    
    .form-tips li {
        margin-bottom: 5px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .form-actions {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>