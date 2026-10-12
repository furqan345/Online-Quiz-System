<?php
require_once 'config/db.php';

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'student') {
        header("Location: student/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == 'superadmin') {
        header("Location: superadmin/dashboard.php");
        exit();
    }
} else {
    // Not logged in -> go to login page
    header("Location: auth/login.php");
    exit();
}
?>
