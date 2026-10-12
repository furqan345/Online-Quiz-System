<?php
require_once '../includes/auth_check.php';
checkRole('student');

$student_id = $_SESSION['user_id'];

$available_quizzes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM quizzes WHERE status='active'"))['cnt'];
$completed_quizzes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM student_quiz WHERE student_id=$student_id AND status='completed'"))['cnt'];
$avg_percentage = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(r.percentage) as avg_pct FROM results r 
                    INNER JOIN student_quiz sq ON r.student_quiz_id = sq.student_quiz_id 
                    WHERE sq.student_id = $student_id"))['avg_pct'];
$avg_percentage = $avg_percentage ? round($avg_percentage, 1) : 0;

// Recent results
$recent = mysqli_query($conn, "SELECT r.*, q.title FROM results r
            INNER JOIN student_quiz sq ON r.student_quiz_id = sq.student_quiz_id
            INNER JOIN quizzes q ON sq.quiz_id = q.quiz_id
            WHERE sq.student_id = $student_id
            ORDER BY r.result_date DESC LIMIT 5");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Student Dashboard</h2>
    <a href="quiz_list.php" class="btn btn-success">Take a Quiz</a>
</div>

<div class="dashboard-cards">
    <div class="card">
        <h3><?php echo $available_quizzes; ?></h3>
        <p>Available Quizzes</p>
    </div>
    <div class="card">
        <h3><?php echo $completed_quizzes; ?></h3>
        <p>Quizzes Completed</p>
    </div>
    <div class="card">
        <h3><?php echo $avg_percentage; ?>%</h3>
        <p>Average Score</p>
    </div>
</div>

<h3 style="margin-top:30px; color: var(--navy);">Recent Results</h3>
<table>
    <tr>
        <th>Quiz</th>
        <th>Score</th>
        <th>Percentage</th>
        <th>Grade</th>
        <th>Date</th>
    </tr>
    <?php if (mysqli_num_rows($recent) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($recent)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['total_score']; ?></td>
            <td><?php echo $row['percentage']; ?>%</td>
            <td><?php echo $row['grade']; ?></td>
            <td><?php echo date('d M Y', strtotime($row['result_date'])); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No quizzes attempted yet.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
