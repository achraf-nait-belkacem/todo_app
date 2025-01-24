<?php
require_once 'session_config.php';
session_start();
include 'db.php';

$message = '';
$messageType = '';
$validToken = false;

// Check if token is provided
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) {
    header("Location: login.php");
    exit;
}

// Verify token and check expiration
$stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $message = "Invalid or expired reset link. Please request a new one.";
    $messageType = "danger";
} else {
    $validToken = true;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate password
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $message = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
            $messageType = "danger";
        } elseif ($password !== $confirm_password) {
            $message = "Passwords do not match";
            $messageType = "danger";
        } else {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            
            if ($update->execute([$hashedPassword, $reset['email']])) {
                // Delete used token
                $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $delete->execute([$token]);
                
                $message = "Password has been reset successfully. You can now login with your new password.";
                $messageType = "success";
                $validToken = false; // Hide the form
            } else {
                $message = "Failed to reset password. Please try again.";
                $messageType = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="text-center mb-4">Reset Password</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php if ($messageType === 'success'): ?>
                    <div class="text-center">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($validToken): ?>
                <div class="card">
                    <div class="card-body">
                        <form action="reset_password.php?token=<?php echo urlencode($token); ?>" method="POST">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                    required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                                <div class="form-text">Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>