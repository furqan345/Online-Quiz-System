<?php
// ============================================================
// auth_check.php
// Include this at the TOP of every protected page.
// Usage: require_once '../includes/auth_check.php';
//        checkRole('student');   // or 'admin' or 'superadmin'
// ============================================================

require_once __DIR__ . '/../config/db.php';

function checkRole($allowedRole) {
    // Not logged in at all -> send to login page
    if (!isset($_SESSION['user_id'])) {
        header("Location: /quiz_system/auth/login.php");
        exit();
    }

    // Logged in but wrong role -> block access
    if ($_SESSION['role'] != $allowedRole) {
        echo "<script>alert('Access Denied: You are not authorized to view this page.'); window.location.href='/quiz_system/index.php';</script>";
        exit();
    }
}
?>
