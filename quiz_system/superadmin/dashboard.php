<?php
require_once '../includes/auth_check.php';
checkRole('superadmin');

$total_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE role='admin'"))['cnt'];
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE role='student'"))['cnt'];
$total_quizzes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM quizzes"))['cnt'];
$total_attempts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM student_quiz WHERE status='completed'"))['cnt'];

$recent_admins = mysqli_query($conn, "SELECT * FROM users WHERE role='admin' ORDER BY created_at DESC LIMIT 5");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Super Admin Dashboard</h2>
    <a href="manage_admins.php" class="btn btn-success">+ Add Admin</a>
</div>

<div class="dashboard-cards">
    <div class="card">
        <h3><?php echo $total_admins; ?></h3>
        <p>Total Admins</p>
    </div>
    <div class="card">
        <h3><?php echo $total_students; ?></h3>
        <p>Total Students</p>
    </div>
    <div class="card">
        <h3><?php echo $total_quizzes; ?></h3>
        <p>Total Quizzes (System-wide)</p>
    </div>
    <div class="card">
        <h3><?php echo $total_attempts; ?></h3>
        <p>Total Quiz Attempts</p>
    </div>
</div>

<h3 style="margin-top:30px; color: var(--navy);">Recently Added Admins</h3>
<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Joined</th>
    </tr>
    <?php if (mysqli_num_rows($recent_admins) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($recent_admins)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['department'] ?: '-'); ?></td>
            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">No admins added yet.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
