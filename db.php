<?php
// db.php
// Load configuration from a separate file
require_once 'config.php';

try {
    // Create PDO connection
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->exec("USE " . DB_NAME);

    // Create tables if they don't exist
    $tables = [
        // Users table
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            login_attempts INT DEFAULT 0,
            last_attempt DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // Tasks table
        "CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            task_name VARCHAR(255) NOT NULL,
            is_completed BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",

        // Password resets table
        "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (email),
            INDEX (token)
        )"
    ];

    // Execute each table creation query
    foreach ($tables as $sql) {
        $conn->exec($sql);
    }

} catch (PDOException $e) {
    // Log error (in production, use proper logging)
    error_log($e->getMessage());
    
    // Show generic error to user
    die("A database error occurred. Please try again later.");
}

// Function to safely close database connection
function closeDb() {
    global $conn;
    $conn = null;
}

// Register shutdown function to ensure DB connection is closed
register_shutdown_function('closeDb');
?>