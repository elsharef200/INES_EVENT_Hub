<?php 
require_once 'config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_type'] != 'admin') {
    header('Location: admin-login.php');
    exit;
}

$error = '';
$success = '';

// Handle delete organizer
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Don't allow deleting yourself
    if ($id != $_SESSION['admin_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'organizer'");
        if ($stmt->execute([$id])) {
            $success = 'Organizer deleted successfully';
        } else {
            $error = 'Failed to delete organizer';
        }
    } else {
        $error = 'You cannot delete yourself';
    }
}

// Handle add new organizer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_organizer'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];
    
    // Check if username exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    
    if ($check->fetch()) {
        $error = 'Username already exists';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, user_type) VALUES (?, ?, ?, 'organizer')");
        
        if ($stmt->execute([$username, $hash, $full_name])) {
            $success = 'Organizer added successfully';
        } else {
            $error = 'Failed to add organizer';
        }
    }
}

// Get all organizers
$organizers = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM events e WHERE e.organizer_id = u.id) as total_events,
           (SELECT COUNT(*) FROM tickets t JOIN events e ON t.event_id = e.id WHERE e.organizer_id = u.id) as total_tickets
    FROM users u 
    WHERE u.user_type = 'organizer'
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Organizers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .organizer-stats {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-btn {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .delete-btn:hover {
            background: #c82333;
        }
        
        .role-badge.organizer {
            background: #764ba2;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header admin-header">
            <div>
                <span class="role-badge admin">👑 ADMIN</span>
                <h1>Manage Organizers</h1>
            </div>
            <a href="admin-dashboard.php" class="logout-link">← Back to Dashboard</a>
        </div>
        
        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success-message">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Add Organizer Button -->
        <div style="margin-bottom: 20px;">
            <button onclick="showAddModal()" class="btn-primary">➕ Add New Organizer</button>
        </div>
        
        <!-- Organizers List -->
        <div class="dashboard-card">
            <h3>All Organizers</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Events</th>
                            <th>Tickets Sold</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($organizers as $org): ?>
                        <tr>
                            <td>#<?php echo $org['id']; ?></td>
                            <td><code><?php echo htmlspecialchars($org['username']); ?></code></td>
                            <td><?php echo htmlspecialchars($org['full_name']); ?></td>
                            <td><?php echo $org['total_events']; ?></td>
                            <td><?php echo $org['total_tickets'] ?: 0; ?></td>
                            <td><?php echo date('M j, Y', strtotime($org['created_at'])); ?></td>
                            <td>
                                <a href="?delete=<?php echo $org['id']; ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Delete this organizer? This will also delete all their events.')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Organizer Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Organizer</h2>
                <span class="close-btn" onclick="hideAddModal()">&times;</span>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                
                <button type="submit" name="add_organizer" class="btn-large">Add Organizer</button>
            </form>
        </div>
    </div>
    
    <script>
    function showAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    
    function hideAddModal() {
        document.getElementById('addModal').classList.remove('active');
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('addModal');
        if (event.target == modal) {
            modal.classList.remove('active');
        }
    }
    </script>
</body>
</html>