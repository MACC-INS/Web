<?php

function OpenCon()
{
    $dbhost = "db";
    $dbuser = "app_user";  // Use limited privilege user
    $dbpass = "StrongPassword123!";
    $dbname = "flights";
    
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

// Set security headers if not already set by nginx
if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
}
?>