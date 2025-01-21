<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Using root temporarily
define('DB_PASS', ''); // Update this if your root has a password
define('DB_NAME', 'tasks_db');

// Application configuration
define('APP_NAME', 'Todo App');
define('APP_URL', 'http://localhost/todo_app'); // Change in production
define('APP_ENV', 'development'); // Change to 'production' in production

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', ''); // Add your Gmail address
define('SMTP_PASSWORD', ''); // Add your Gmail App Password
define('SMTP_FROM_EMAIL', ''); // Add your Gmail address

// Security configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes
define('PASSWORD_MIN_LENGTH', 8);

// Error reporting - change in production
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Time zone
date_default_timezone_set('UTC');
?> 