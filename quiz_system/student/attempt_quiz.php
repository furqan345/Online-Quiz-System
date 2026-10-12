<?php
require_once '../includes/auth_check.php';
checkRole('student');

$student_id = $_SESSION['user_id'];

if (!isset($_GET['quiz_id'])) {
    header("Location: quiz_list.php");
    exit();
}
$quiz_id = (int) $_GET['quiz_id'];

// Get quiz info
$quiz_result = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_id = $quiz_id AND status = 'active'");
if (mysqli_num_rows($quiz_result) == 0) {
    header("Location: quiz_list.php");
    exit();
}
$quiz = mysqli_fetch_assoc($quiz_result);

// Check if already completed
$check = mysqli_query($conn, "SELECT * FROM student_quiz WHERE quiz_id = $quiz_id AND student_id = $student_id ORDER BY student_quiz_id DESC LIMIT 1");
$existing = mysqli_num_rows($check) > 0 ? mysqli_fetch_assoc($check) : null;

if ($existing && $existing['status'] == 'completed') {
    header("Location: view_result.php?student_quiz_id=" . $existing['student_quiz_id']);
    exit();
}

// Create a new attempt if none in progress, otherwise resume the existing one
if ($existing && $existing['status'] == 'in_progress') {
    $student_quiz_id = $existing['student_quiz_id'];
    $attempt_start = strtotime($existing['attempt_date']);
} else {
    mysqli_query($conn, "INSERT INTO student_quiz (quiz_id, student_id, status) VALUES ($quiz_id, $student_id, 'in_progress')");
    $student_quiz_id = mysqli_insert_id($conn);
    $attempt_start = time();
}

// Calculate remaining seconds based on when the attempt started
$total_seconds = $quiz['duration_minutes'] * 60;
$elapsed = time() - $attempt_start;
$remaining_seconds = max($total_seconds - $elapsed, 0);

// Fetch questions with their options
$questions = mysqli_query($conn, "SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY question_id ASC");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
    <div class="quiz-timer">⏱ <span id="timerDisplay">--:--</span></div>
</div>

<form method="POST" action="submit_quiz.php" id="quizForm">
    <input type="hidden" name="student_quiz_id" value="<?php echo $student_quiz_id; ?>">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">

    <?php $i = 1; while ($q = mysqli_fetch_assoc($questions)): ?>
        <div class="question-box">
            <h4>Q<?php echo $i++; ?>. <?php echo htmlspecialchars($q['question_text']); ?>
                <span style="float:right; font-size:13px; color:var(--muted);"><?php echo $q['marks']; ?> marks</span>
            </h4>

            <?php if ($q['question_type'] == 'short'): ?>
                <input type="text" name="answer[<?php echo $q['question_id']; ?>]" placeholder="Type your answer">
            <?php else: ?>
                <?php $options = mysqli_query($conn, "SELECT * FROM options WHERE question_id = " . $q['question_id']); ?>
                <?php while ($opt = mysqli_fetch_assoc($options)): ?>
                    <label>
                        <input type="radio" name="answer[<?php echo $q['question_id']; ?>]" value="<?php echo $opt['option_id']; ?>" style="width:auto; margin-right:8px;">
                        <?php echo htmlspecialchars($opt['option_text']); ?>
                    </label>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <button type="submit" class="btn btn-success" style="width:auto; padding: 12px 30px;">Submit Quiz</button>
</form>

<script src="/quiz_system/assets/js/timer.js"></script>
<script>
    startTimer(<?php echo $remaining_seconds; ?>, "timerDisplay", "quizForm");
</script>

<?php include '../includes/footer.php'; ?>
