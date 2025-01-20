<?php
require_once 'session_config.php';
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    // Check if task exists and belongs to user
    $stmt = $conn->prepare("UPDATE tasks SET is_completed = TRUE WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}

header("Location: index.php");
exit;
?>