<?php
require_once 'session_config.php';
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'db.php';
include 'send_mailer.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
        $messageType = "danger";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Delete any existing reset tokens for this email
            $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete->execute([$email]);

            // Insert new reset token
            $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            
            if ($insert->execute([$email, $token, $expiry])) {
                $resetLink = APP_URL . "/reset_password.php?token=" . urlencode($token);
                $subject = APP_NAME . " - Password Reset Request";
                $body = "Hello,<br><br>Someone requested a password reset for your account. If this wasn't you, please ignore this email.<br><br>
                        To reset your password, click the following link (valid for 1 hour):<br><br>
                        <a href='$resetLink'>Reset Password</a><br><br>
                        If the link doesn't work, copy and paste this URL into your browser:<br>
                        $resetLink<br><br>
                        Best regards,<br>" . APP_NAME;

                if (sendEmail($email, $subject, $body)) {
                    $message = "Password reset instructions have been sent to your email";
                    $messageType = "success";
                } else {
                    $message = "Failed to send reset email. Please try again later";
                    $messageType = "danger";
                }
            } else {
                $message = "An error occurred. Please try again later";
                $messageType = "danger";
            }
        } else {
            // For security, show the same message even if email doesn't exist
            $message = "If your email exists in our system, you will receive password reset instructions";
            $messageType = "info";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="text-center mb-4">Forgot Password</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="forgot_password.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <p>Remember your password? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>