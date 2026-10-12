<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$message = "";

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $delete_id AND role = 'student'");
    $message = "Student account deleted.";
}

$students = mysqli_query($conn, "SELECT u.*, 
            (SELECT COUNT(*) FROM student_quiz WHERE student_id = u.user_id AND status='completed') as attempts,
            (SELECT ROUND(AVG(r.percentage),1) FROM results r INNER JOIN student_quiz sq ON r.student_quiz_id = sq.student_quiz_id WHERE sq.student_id = u.user_id) as avg_score
            FROM users u WHERE role = 'student' ORDER BY u.created_at DESC");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Manage Students</h2>
</div>

<?php if ($message != "") echo "<p class='alert-success'>$message</p>"; ?>

<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Contact</th>
        <th>Quizzes Taken</th>
        <th>Avg Score</th>
        <th>Joined</th>
        <th>Action</th>
    </tr>
    <?php if (mysqli_num_rows($students) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($students)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['department'] ?: '-'); ?></td>
            <td><?php echo htmlspecialchars($row['contact_number'] ?: '-'); ?></td>
            <td><?php echo $row['attempts']; ?></td>
            <td><?php echo $row['avg_score'] ? $row['avg_score'] . '%' : '-'; ?></td>
            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
            <td>
                <a href="manage_users.php?delete_id=<?php echo $row['user_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this student account? This will also remove their quiz history.');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8">No students registered yet.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
