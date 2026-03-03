<?php
require_once 'config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin-login.php');
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'] ?? 'admin';

// Get user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND user_type = ?");
$stmt->execute([$username, $role]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['full_name'];
    $_SESSION['admin_type'] = $user['user_type'];
    
    // Redirect based on role
    if ($user['user_type'] == 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: organizer-dashboard.php');
    }
    exit;
} else {
    // Failed login - go back with error
    header('Location: admin-login.php?role=' . $role . '&error=1');
    exit;
}
?>