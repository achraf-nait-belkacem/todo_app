<?php
require_once 'session_config.php';
session_start();
include 'db.php';
include 'send_mailer.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Add new task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = $_POST['task_name'];
    if (!empty($task_name)) {
        $stmt = $conn->prepare("INSERT INTO tasks (task_name, user_id) VALUES (?, ?)");
        $stmt->execute([$task_name, $_SESSION['user_id']]);
    }
}

// Get open and closed tasks
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND is_completed = 0");
$stmt->execute([$_SESSION['user_id']]);
$open_tasks = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND is_completed = 1");
$stmt->execute([$_SESSION['user_id']]);
$completed_tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="index.php">To-Do App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


<div class="container mt-5">
    <h1 class="text-center">To-Do List</h1>
    <form action="index.php" method="POST" class="mb-4">
        <div class="input-group">
            <input type="text" name="task_name" class="form-control" placeholder="New task..." required>
            <button type="submit" class="btn btn-primary">Add Task</button>
        </div>
    </form>

    <div class="row">
        <!-- Open Tasks Column -->
        <div class="col-md-6">
            <h2 class="text-center">Open Tasks</h2>
            <ul class="list-group">
                <?php if (!empty($open_tasks)): ?>
                    <?php foreach ($open_tasks as $task): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($task['task_name']); ?>
                            <div>
                                <a href="complete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-success btn-sm">Complete</a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item">No open tasks found</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Completed Tasks Column -->
        <div class="col-md-6">
            <h2 class="text-center">Completed Tasks</h2>
            <ul class="list-group">
                <?php if (!empty($completed_tasks)): ?>
                    <?php foreach ($completed_tasks as $task): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($task['task_name']); ?>
                            <div>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item">No completed tasks found</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>