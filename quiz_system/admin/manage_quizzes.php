<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];
$quizzes = mysqli_query($conn, "SELECT q.*, 
            (SELECT COUNT(*) FROM questions WHERE quiz_id = q.quiz_id) as question_count
            FROM quizzes q WHERE teacher_id = $admin_id ORDER BY created_date DESC");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Manage Quizzes</h2>
    <a href="create_quiz.php" class="btn btn-success">+ Create New Quiz</a>
</div>

<table>
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Questions</th>
        <th>Total Marks</th>
        <th>Duration</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php if (mysqli_num_rows($quizzes) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($quizzes)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars(substr($row['description'], 0, 40)); ?><?php echo strlen($row['description']) > 40 ? '...' : ''; ?></td>
            <td><?php echo $row['question_count']; ?></td>
            <td><?php echo $row['total_marks']; ?></td>
            <td><?php echo $row['duration_minutes']; ?> min</td>
            <td>
                <?php if ($row['status'] == 'active'): ?>
                    <span style="color: var(--success); font-weight:600;">Active</span>
                <?php else: ?>
                    <span style="color: var(--muted); font-weight:600;">Inactive</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="manage_questions.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-primary">Questions</a>
                <a href="edit_quiz.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-edit">Edit</a>
                <a href="delete_quiz.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this quiz and all its questions?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">No quizzes found. Create your first quiz.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
