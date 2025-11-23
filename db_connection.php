<?php

function OpenCon()
{
    $dbhost = "db";
    $dbuser = getenv('MYSQL_APP_USER');
    $dbpass = getenv('MYSQL_APP_PASSWORD');
    $dbname = getenv('MYSQL_DATABASE');

    // Check if mysqli extension is loaded
    if (!extension_loaded('mysqli')) {
        error_log("MySQLi extension not loaded");
        die('Database connection error');
    }

    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbn

    if (!$conn) {
        error_log("Database connection failed: " . mysqli_
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
