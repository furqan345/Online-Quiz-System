<?php
require_once '../includes/auth_check.php';
checkRole('student');

$student_id = $_SESSION['user_id'];

if (!isset($_GET['student_quiz_id'])) {
    header("Location: quiz_list.php");
    exit();
}
$student_quiz_id = (int) $_GET['student_quiz_id'];

// Verify ownership and fetch result
$sql = "SELECT sq.*, q.title, q.total_marks, r.total_score, r.percentage, r.grade, r.result_date
        FROM student_quiz sq
        INNER JOIN quizzes q ON sq.quiz_id = q.quiz_id
        INNER JOIN results r ON r.student_quiz_id = sq.student_quiz_id
        WHERE sq.student_quiz_id = $student_quiz_id AND sq.student_id = $student_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: quiz_list.php");
    exit();
}
$data = mysqli_fetch_assoc($result);

// Grade color
$grade_color = "var(--success)";
if ($data['grade'] == 'D') $grade_color = "#E7A93C";
if ($data['grade'] == 'F') $grade_color = "var(--danger)";

// Answer review
$answers = mysqli_query($conn, "SELECT a.*, q.question_text, q.question_type, q.marks 
            FROM answers a INNER JOIN questions q ON a.question_id = q.question_id
            WHERE a.student_quiz_id = $student_quiz_id ORDER BY a.question_id ASC");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2><?php echo htmlspecialchars($data['title']); ?> &mdash; Result</h2>
</div>

<div class="dashboard-cards">
    <div class="card">
        <h3><?php echo $data['total_score']; ?> / <?php echo $data['total_marks']; ?></h3>
        <p>Score</p>
    </div>
    <div class="card">
        <h3><?php echo $data['percentage']; ?>%</h3>
        <p>Percentage</p>
    </div>
    <div class="card">
        <h3 style="color:<?php echo $grade_color; ?>;"><?php echo $data['grade']; ?></h3>
        <p>Grade</p>
    </div>
</div>

<h3 style="margin-top:30px; color: var(--navy);">Answer Review</h3>

<?php $i = 1; while ($a = mysqli_fetch_assoc($answers)): ?>
    <div class="question-box" style="border-left-color: <?php echo $a['marks_obtained'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
        <h4>Q<?php echo $i++; ?>. <?php echo htmlspecialchars($a['question_text']); ?>
            <span style="float:right; font-size:13px; color:<?php echo $a['marks_obtained'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                <?php echo $a['marks_obtained']; ?> / <?php echo $a['marks']; ?> marks
            </span>
        </h4>

        <?php if ($a['question_type'] == 'short'): ?>
            <p style="font-size:14px; margin-top:8px;">Your answer: <strong><?php echo htmlspecialchars($a['answer_text'] ?? '(not answered)'); ?></strong></p>
        <?php else: ?>
            <?php
            $selected = null;
            if ($a['selected_option_id']) {
                $selected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM options WHERE option_id = " . $a['selected_option_id']));
            }
            ?>
            <p style="font-size:14px; margin-top:8px;">
                Your answer: <strong><?php echo $selected ? htmlspecialchars($selected['option_text']) : '(not answered)'; ?></strong>
            </p>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

<p class="foot-link" style="text-align:left; margin-top:20px;"><a href="quiz_list.php">&larr; Back to Quiz List</a></p>

<?php include '../includes/footer.php'; ?>
