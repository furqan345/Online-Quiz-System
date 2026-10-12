<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Quiz and Result Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/quiz_system/assets/css/style.css">
</head>
<body>

<header class="main-header">
    <div class="logo">Online Quiz System</div>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>

            <?php if ($_SESSION['role'] == 'student'): ?>
                <a href="/quiz_system/student/dashboard.php">Dashboard</a>
                <a href="/quiz_system/student/quiz_list.php">Available Quizzes</a>
                <a href="/quiz_system/student/result_history.php">My Results</a>
            <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <a href="/quiz_system/admin/dashboard.php">Dashboard</a>
                <a href="/quiz_system/admin/manage_quizzes.php">Manage Quizzes</a>
                <a href="/quiz_system/admin/manage_users.php">Manage Users</a>
                <a href="/quiz_system/admin/view_reports.php">Reports</a>
            <?php elseif ($_SESSION['role'] == 'superadmin'): ?>
                <a href="/quiz_system/superadmin/dashboard.php">Dashboard</a>
                <a href="/quiz_system/superadmin/manage_admins.php">Manage Admins</a>
            <?php endif; ?>

            <a href="/quiz_system/auth/logout.php">Logout</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
