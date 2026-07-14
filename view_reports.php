<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];

// Quiz-wise report: attempts, average, highest, lowest
$quiz_report = mysqli_query($conn, "SELECT q.quiz_id, q.title, q.total_marks,
            COUNT(r.result_id) as attempts,
            ROUND(AVG(r.percentage),1) as avg_pct,
            MAX(r.percentage) as max_pct,
            MIN(r.percentage) as min_pct
            FROM quizzes q
            LEFT JOIN student_quiz sq ON sq.quiz_id = q.quiz_id AND sq.status = 'completed'
            LEFT JOIN results r ON r.student_quiz_id = sq.student_quiz_id
            WHERE q.teacher_id = $admin_id
            GROUP BY q.quiz_id
            ORDER BY q.created_date DESC");

// Selected quiz detail (student-wise) if requested
$selected_quiz_id = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : null;
$student_detail = null;
if ($selected_quiz_id) {
    $student_detail = mysqli_query($conn, "SELECT u.name, u.email, r.total_score, r.percentage, r.grade, r.result_date
                FROM results r
                INNER JOIN student_quiz sq ON r.student_quiz_id = sq.student_quiz_id
                INNER JOIN users u ON sq.student_id = u.user_id
                WHERE sq.quiz_id = $selected_quiz_id
                ORDER BY r.percentage DESC");
    $quiz_title_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT title FROM quizzes WHERE quiz_id = $selected_quiz_id AND teacher_id = $admin_id"));
}
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Reports &amp; Analytics</h2>
</div>

<h3 style="color: var(--navy); margin-bottom:8px;">Quiz-wise Performance</h3>
<table>
    <tr>
        <th>Quiz</th>
        <th>Attempts</th>
        <th>Average %</th>
        <th>Highest %</th>
        <th>Lowest %</th>
        <th>Action</th>
    </tr>
    <?php if (mysqli_num_rows($quiz_report) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($quiz_report)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['attempts']; ?></td>
            <td><?php echo $row['avg_pct'] !== null ? $row['avg_pct'] . '%' : '-'; ?></td>
            <td><?php echo $row['max_pct'] !== null ? $row['max_pct'] . '%' : '-'; ?></td>
            <td><?php echo $row['min_pct'] !== null ? $row['min_pct'] . '%' : '-'; ?></td>
            <td><a href="view_reports.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-primary">View Details</a></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No quizzes created yet.</td></tr>
    <?php endif; ?>
</table>

<?php if ($selected_quiz_id && $student_detail): ?>
    <h3 style="color: var(--navy); margin-top:35px; margin-bottom:8px;">
        Student Results &mdash; <?php echo htmlspecialchars($quiz_title_row['title'] ?? ''); ?>
    </h3>
    <table>
        <tr>
            <th>Student</th>
            <th>Email</th>
            <th>Score</th>
            <th>Percentage</th>
            <th>Grade</th>
            <th>Date</th>
        </tr>
        <?php if (mysqli_num_rows($student_detail) > 0): ?>
            <?php while ($s = mysqli_fetch_assoc($student_detail)): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['name']); ?></td>
                <td><?php echo htmlspecialchars($s['email']); ?></td>
                <td><?php echo $s['total_score']; ?></td>
                <td><?php echo $s['percentage']; ?>%</td>
                <td><?php echo $s['grade']; ?></td>
                <td><?php echo date('d M Y', strtotime($s['result_date'])); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No students have attempted this quiz yet.</td></tr>
        <?php endif; ?>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
