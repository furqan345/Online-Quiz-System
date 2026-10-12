<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];

if (!isset($_GET['quiz_id'])) {
    header("Location: manage_quizzes.php");
    exit();
}

$quiz_id = (int) $_GET['quiz_id'];

// Verify this quiz belongs to the logged-in admin
$quiz_result = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_id = $quiz_id AND teacher_id = $admin_id");
if (mysqli_num_rows($quiz_result) == 0) {
    header("Location: manage_quizzes.php");
    exit();
}
$quiz = mysqli_fetch_assoc($quiz_result);

// Fetch all questions for this quiz
$questions = mysqli_query($conn, "SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY question_id ASC");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2><?php echo htmlspecialchars($quiz['title']); ?> &mdash; Questions</h2>
    <a href="add_question.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-success">+ Add Question</a>
</div>

<p style="color: var(--muted); margin-bottom: 15px;">
    Total Marks: <strong style="color:var(--navy);"><?php echo $quiz['total_marks']; ?></strong> &nbsp;|&nbsp;
    Duration: <strong style="color:var(--navy);"><?php echo $quiz['duration_minutes']; ?> min</strong> &nbsp;|&nbsp;
    Total Questions: <strong style="color:var(--navy);"><?php echo mysqli_num_rows($questions); ?></strong>
</p>

<?php if (mysqli_num_rows($questions) > 0): ?>
    <?php $i = 1; while ($q = mysqli_fetch_assoc($questions)): ?>
        <div class="question-box">
            <h4>Q<?php echo $i++; ?>. <?php echo htmlspecialchars($q['question_text']); ?>
                <span style="float:right; font-size:13px; color:var(--muted);"><?php echo strtoupper($q['question_type']); ?> &bull; <?php echo $q['marks']; ?> marks</span>
            </h4>

            <?php if ($q['question_type'] != 'short'): ?>
                <?php $options = mysqli_query($conn, "SELECT * FROM options WHERE question_id = " . $q['question_id']); ?>
                <ul style="list-style:none; margin-top:8px;">
                <?php while ($opt = mysqli_fetch_assoc($options)): ?>
                    <li style="padding:6px 0; <?php echo $opt['is_correct'] ? 'color: var(--success); font-weight:600;' : 'color: var(--muted);'; ?>">
                        <?php echo $opt['is_correct'] ? '✓ ' : '&nbsp;&nbsp;'; ?><?php echo htmlspecialchars($opt['option_text']); ?>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p style="color: var(--muted); font-size:13px; margin-top:6px;">(Short answer question &mdash; graded manually or by exact match)</p>
            <?php endif; ?>

            <div style="margin-top:12px;">
                <a href="edit_question.php?question_id=<?php echo $q['question_id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-edit">Edit</a>
                <a href="delete_question.php?question_id=<?php echo $q['question_id']; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-danger" onclick="return confirm('Delete this question?');">Delete</a>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="question-box">
        <p>No questions added yet. Click "+ Add Question" to start building this quiz.</p>
    </div>
<?php endif; ?>

<p class="foot-link" style="text-align:left; margin-top:20px;"><a href="manage_quizzes.php">&larr; Back to Manage Quizzes</a></p>

<?php include '../includes/footer.php'; ?>
