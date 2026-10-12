<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];

// Stats
$total_quizzes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM quizzes WHERE teacher_id = $admin_id"))['cnt'];
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE role = 'student'"))['cnt'];
$total_questions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM questions q INNER JOIN quizzes qz ON q.quiz_id = qz.quiz_id WHERE qz.teacher_id = $admin_id"))['cnt'];
$total_attempts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM student_quiz sq INNER JOIN quizzes qz ON sq.quiz_id = qz.quiz_id WHERE qz.teacher_id = $admin_id AND sq.status = 'completed'"))['cnt'];

// Recent quizzes
$recent_quizzes = mysqli_query($conn, "SELECT * FROM quizzes WHERE teacher_id = $admin_id ORDER BY created_date DESC LIMIT 5");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Admin Dashboard</h2>
    <a href="create_quiz.php" class="btn btn-success">+ Create New Quiz</a>
</div>

<div class="dashboard-cards">
    <div class="card">
        <h3><?php echo $total_quizzes; ?></h3>
        <p>My Quizzes</p>
    </div>
    <div class="card">
        <h3><?php echo $total_questions; ?></h3>
        <p>Total Questions</p>
    </div>
    <div class="card">
        <h3><?php echo $total_students; ?></h3>
        <p>Registered Students</p>
    </div>
    <div class="card">
        <h3><?php echo $total_attempts; ?></h3>
        <p>Quiz Attempts Completed</p>
    </div>
</div>

<h3 style="margin-top:30px; color: var(--navy);">Recent Quizzes</h3>
<table>
    <tr>
        <th>Title</th>
        <th>Total Marks</th>
        <th>Duration</th>
        <th>Status</th>
        <th>Created On</th>
        <th>Action</th>
    </tr>
    <?php if (mysqli_num_rows($recent_quizzes) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($recent_quizzes)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['total_marks']; ?></td>
            <td><?php echo $row['duration_minutes']; ?> min</td>
            <td><?php echo ucfirst($row['status']); ?></td>
            <td><?php echo date('d M Y', strtotime($row['created_date'])); ?></td>
            <td><a href="manage_questions.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-primary">Manage</a></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No quizzes created yet. Click "Create New Quiz" to get started.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
