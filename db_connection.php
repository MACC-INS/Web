<?php

function OpenCon()
{
    // Environment variables for credentials
    $dbhost = getenv('DB_HOST') ?: "db";
    $dbuser = getenv('DB_USER') ?: "app_user";
    $dbpass = getenv('DB_APP_PASSWORD') ?: "StrongPassword123!";
    $dbname = getenv('DB_NAME') ?: "flights";
    
    // Check if mysqli extension is loaded
    if (!extension_loaded('mysqli')) {
        error_log("MySQLi extension not loaded");
        die('Database connection error');
    }
    
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        die('Database connection error');
    }
    
    return $conn;
}

function CloseCon($conn)
{
    $conn->close();
}

// Enhanced security headers
if (!headers_sent()) {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");  # When using HTTPS
}

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
?>