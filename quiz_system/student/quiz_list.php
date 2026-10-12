<?php
require_once '../includes/auth_check.php';
checkRole('student');

$student_id = $_SESSION['user_id'];

// Show all active quizzes, with a flag whether the student already completed each one
$sql = "SELECT q.*, 
        (SELECT COUNT(*) FROM questions WHERE quiz_id = q.quiz_id) as question_count,
        (SELECT status FROM student_quiz WHERE quiz_id = q.quiz_id AND student_id = $student_id ORDER BY student_quiz_id DESC LIMIT 1) as my_status,
        (SELECT student_quiz_id FROM student_quiz WHERE quiz_id = q.quiz_id AND student_id = $student_id ORDER BY student_quiz_id DESC LIMIT 1) as my_attempt_id
        FROM quizzes q
        WHERE q.status = 'active'
        ORDER BY q.created_date DESC";
$quizzes = mysqli_query($conn, $sql);
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Available Quizzes</h2>
</div>

<table>
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Questions</th>
        <th>Total Marks</th>
        <th>Duration</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php if (mysqli_num_rows($quizzes) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($quizzes)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars(substr($row['description'], 0, 45)); ?><?php echo strlen($row['description']) > 45 ? '...' : ''; ?></td>
            <td><?php echo $row['question_count']; ?></td>
            <td><?php echo $row['total_marks']; ?></td>
            <td><?php echo $row['duration_minutes']; ?> min</td>
            <td>
                <?php if ($row['my_status'] == 'completed'): ?>
                    <span style="color: var(--success); font-weight:600;">Completed</span>
                <?php elseif ($row['my_status'] == 'in_progress'): ?>
                    <span style="color: #E7A93C; font-weight:600;">In Progress</span>
                <?php else: ?>
                    <span style="color: var(--muted);">Not Attempted</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($row['question_count'] == 0): ?>
                    <span style="color: var(--muted); font-size:13px;">No questions yet</span>
                <?php elseif ($row['my_status'] == 'completed'): ?>
                    <a href="view_result.php?student_quiz_id=<?php echo $row['my_attempt_id']; ?>" class="btn btn-primary">View Result</a>
                <?php elseif ($row['my_status'] == 'in_progress'): ?>
                    <a href="attempt_quiz.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-edit">Resume</a>
                <?php else: ?>
                    <a href="attempt_quiz.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-success">Start Quiz</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">No quizzes available right now.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
