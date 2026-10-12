<?php
require_once '../includes/auth_check.php';
checkRole('student');

$student_id = $_SESSION['user_id'];

$sql = "SELECT sq.student_quiz_id, q.title, q.total_marks, r.total_score, r.percentage, r.grade, r.result_date
        FROM student_quiz sq
        INNER JOIN quizzes q ON sq.quiz_id = q.quiz_id
        INNER JOIN results r ON r.student_quiz_id = sq.student_quiz_id
        WHERE sq.student_id = $student_id
        ORDER BY r.result_date DESC";
$results = mysqli_query($conn, $sql);
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>My Result History</h2>
</div>

<table>
    <tr>
        <th>Quiz</th>
        <th>Score</th>
        <th>Percentage</th>
        <th>Grade</th>
        <th>Date</th>
        <th>Action</th>
    </tr>
    <?php if (mysqli_num_rows($results) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($results)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['total_score']; ?> / <?php echo $row['total_marks']; ?></td>
            <td><?php echo $row['percentage']; ?>%</td>
            <td><?php echo $row['grade']; ?></td>
            <td><?php echo date('d M Y, h:i A', strtotime($row['result_date'])); ?></td>
            <td><a href="view_result.php?student_quiz_id=<?php echo $row['student_quiz_id']; ?>" class="btn btn-primary">View Details</a></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">You haven't attempted any quizzes yet.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
