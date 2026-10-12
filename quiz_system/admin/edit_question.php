<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];
$error = "";

if (!isset($_GET['question_id']) || !isset($_GET['quiz_id'])) {
    header("Location: manage_quizzes.php");
    exit();
}
$question_id = (int) $_GET['question_id'];
$quiz_id = (int) $_GET['quiz_id'];

// Verify ownership
$check = mysqli_query($conn, "SELECT q.* FROM questions q 
            INNER JOIN quizzes qz ON q.quiz_id = qz.quiz_id 
            WHERE q.question_id = $question_id AND qz.quiz_id = $quiz_id AND qz.teacher_id = $admin_id");
if (mysqli_num_rows($check) == 0) {
    header("Location: manage_quizzes.php");
    exit();
}
$question = mysqli_fetch_assoc($check);
$old_marks = $question['marks'];

// Fetch current options
$options = [];
$opt_result = mysqli_query($conn, "SELECT * FROM options WHERE question_id = $question_id ORDER BY option_id ASC");
while ($o = mysqli_fetch_assoc($opt_result)) {
    $options[] = $o;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_text = mysqli_real_escape_string($conn, trim($_POST['question_text']));
    $marks = (int) $_POST['marks'];

    if ($question_text == "" || $marks <= 0) {
        $error = "Please enter valid question text and marks.";
    } else {
        mysqli_query($conn, "UPDATE questions SET question_text='$question_text', marks=$marks WHERE question_id = $question_id");

        // Update total_marks on quiz (difference between old and new)
        $diff = $marks - $old_marks;
        mysqli_query($conn, "UPDATE quizzes SET total_marks = GREATEST(total_marks + ($diff), 0) WHERE quiz_id = $quiz_id");

        if ($question['question_type'] == 'mcq') {
            $option_ids = $_POST['option_id'];
            $option_texts = $_POST['option_text'];
            $correct = (int) $_POST['correct_option'];

            foreach ($option_ids as $i => $opt_id) {
                $opt_text = mysqli_real_escape_string($conn, trim($option_texts[$i]));
                $is_correct = ($i == $correct) ? 1 : 0;
                mysqli_query($conn, "UPDATE options SET option_text='$opt_text', is_correct=$is_correct WHERE option_id = " . (int)$opt_id);
            }
        } elseif ($question['question_type'] == 'truefalse') {
            $correct = $_POST['tf_correct'];
            mysqli_query($conn, "UPDATE options SET is_correct = (option_text = '$correct') WHERE question_id = $question_id");
        } elseif ($question['question_type'] == 'short') {
            $correct_answer = mysqli_real_escape_string($conn, trim($_POST['correct_answer']));
            if (count($options) > 0) {
                mysqli_query($conn, "UPDATE options SET option_text='$correct_answer' WHERE question_id = $question_id");
            } else {
                mysqli_query($conn, "INSERT INTO options (question_id, option_text, is_correct) VALUES ($question_id, '$correct_answer', 1)");
            }
        }

        header("Location: manage_questions.php?quiz_id=$quiz_id&updated=1");
        exit();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="form-box" style="max-width:520px;">
    <h2>Edit Question</h2>
    <p style="color:var(--muted); font-size:13px; margin-top:-12px; margin-bottom:16px;">Type: <?php echo strtoupper($question['question_type']); ?> (type cannot be changed)</p>

    <?php if ($error != "") echo "<p class='alert-error'>$error</p>"; ?>

    <form method="POST" action="edit_question.php?question_id=<?php echo $question_id; ?>&quiz_id=<?php echo $quiz_id; ?>">
        <label>Question Text</label>
        <textarea name="question_text" rows="2" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>

        <label>Marks</label>
        <input type="number" name="marks" min="1" value="<?php echo $question['marks']; ?>" required>

        <?php if ($question['question_type'] == 'mcq'): ?>
            <label>Options (select the correct one)</label>
            <?php foreach ($options as $i => $opt): ?>
                <div class="option-row">
                    <input type="radio" name="correct_option" value="<?php echo $i; ?>" <?php echo $opt['is_correct'] ? 'checked' : ''; ?>>
                    <input type="hidden" name="option_id[]" value="<?php echo $opt['option_id']; ?>">
                    <input type="text" name="option_text[]" value="<?php echo htmlspecialchars($opt['option_text']); ?>">
                </div>
            <?php endforeach; ?>

        <?php elseif ($question['question_type'] == 'truefalse'): ?>
            <label>Correct Answer</label>
            <div style="display:flex; gap:20px; margin-top:8px;">
                <?php foreach ($options as $opt): ?>
                    <label style="display:flex; align-items:center; gap:6px; font-weight:400; margin-top:0;">
                        <input type="radio" name="tf_correct" value="<?php echo $opt['option_text']; ?>" <?php echo $opt['is_correct'] ? 'checked' : ''; ?> style="width:auto;">
                        <?php echo $opt['option_text']; ?>
                    </label>
                <?php endforeach; ?>
            </div>

        <?php elseif ($question['question_type'] == 'short'): ?>
            <label>Correct Answer</label>
            <input type="text" name="correct_answer" value="<?php echo isset($options[0]) ? htmlspecialchars($options[0]['option_text']) : ''; ?>">
        <?php endif; ?>

        <button type="submit">Save Changes</button>
    </form>

    <p class="foot-link"><a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>">&larr; Back to Questions</a></p>
</div>

<?php include '../includes/footer.php'; ?>
