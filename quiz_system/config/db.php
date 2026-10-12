<?php
// ============================================================
// Database Connection File
// Online Quiz and Result Management System
// ============================================================

$host = "localhost";      // Server name (XAMPP/WAMP default)
$username = "root";       // Default MySQL username in XAMPP
$password = "";           // Default MySQL password in XAMPP (blank)
$database = "quiz_system"; // Database name (from database.sql)

// Create connection using MySQLi
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Start session on every page that includes this file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
