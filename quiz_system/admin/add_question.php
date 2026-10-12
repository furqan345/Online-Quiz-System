<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];
$error = "";

if (!isset($_GET['quiz_id'])) {
    header("Location: manage_quizzes.php");
    exit();
}
$quiz_id = (int) $_GET['quiz_id'];

// Verify quiz belongs to this admin
$quiz_result = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_id = $quiz_id AND teacher_id = $admin_id");
if (mysqli_num_rows($quiz_result) == 0) {
    header("Location: manage_quizzes.php");
    exit();
}
$quiz = mysqli_fetch_assoc($quiz_result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_text = mysqli_real_escape_string($conn, trim($_POST['question_text']));
    $question_type = $_POST['question_type'];
    $marks = (int) $_POST['marks'];

    if ($question_text == "" || $marks <= 0) {
        $error = "Please enter the question text and valid marks.";
    } else {
        // Insert question
        $sql = "INSERT INTO questions (quiz_id, question_text, question_type, marks) 
                VALUES ($quiz_id, '$question_text', '$question_type', $marks)";
        mysqli_query($conn, $sql);
        $question_id = mysqli_insert_id($conn);

        if ($question_type == 'mcq') {
            $options = $_POST['option_text'];
            $correct = (int) $_POST['correct_option']; // index of correct option

            foreach ($options as $index => $opt_text) {
                $opt_text = mysqli_real_escape_string($conn, trim($opt_text));
                if ($opt_text == "") continue;
                $is_correct = ($index == $correct) ? 1 : 0;
                mysqli_query($conn, "INSERT INTO options (question_id, option_text, is_correct) 
                                      VALUES ($question_id, '$opt_text', $is_correct)");
            }
        } elseif ($question_type == 'truefalse') {
            $correct = $_POST['tf_correct']; // "True" or "False"
            mysqli_query($conn, "INSERT INTO options (question_id, option_text, is_correct) 
                                  VALUES ($question_id, 'True', " . ($correct == 'True' ? 1 : 0) . ")");
            mysqli_query($conn, "INSERT INTO options (question_id, option_text, is_correct) 
                                  VALUES ($question_id, 'False', " . ($correct == 'False' ? 1 : 0) . ")");
        } elseif ($question_type == 'short') {
            $correct_answer = mysqli_real_escape_string($conn, trim($_POST['correct_answer']));
            if ($correct_answer != "") {
                mysqli_query($conn, "INSERT INTO options (question_id, option_text, is_correct) 
                                      VALUES ($question_id, '$correct_answer', 1)");
            }
        }

        // Update total marks of the quiz
        mysqli_query($conn, "UPDATE quizzes SET total_marks = total_marks + $marks WHERE quiz_id = $quiz_id");

        header("Location: manage_questions.php?quiz_id=$quiz_id&added=1");
        exit();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="form-box" style="max-width:520px;">
    <h2>Add Question</h2>
    <p style="color:var(--muted); font-size:13px; margin-top:-12px; margin-bottom:16px;">For quiz: <?php echo htmlspecialchars($quiz['title']); ?></p>

    <?php if ($error != "") echo "<p class='alert-error'>$error</p>"; ?>

    <form method="POST" action="add_question.php?quiz_id=<?php echo $quiz_id; ?>" id="questionForm">
        <label>Question Text</label>
        <textarea name="question_text" rows="2" required></textarea>

        <label>Question Type</label>
        <select name="question_type" id="question_type" onchange="toggleFields()">
            <option value="mcq">Multiple Choice (MCQ)</option>
            <option value="truefalse">True / False</option>
            <option value="short">Short Answer</option>
        </select>

        <label>Marks</label>
        <input type="number" name="marks" min="1" value="1" required>

        <!-- MCQ Fields -->
        <div id="mcq_fields">
            <label>Options (fill at least 2, select the correct one)</label>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="option-row">
                    <input type="radio" name="correct_option" value="<?php echo $i; ?>" <?php echo $i == 0 ? 'checked' : ''; ?>>
                    <input type="text" name="option_text[]" placeholder="Option <?php echo $i + 1; ?>">
                </div>
            <?php endfor; ?>
        </div>

        <!-- True/False Fields -->
        <div id="tf_fields" style="display:none;">
            <label>Correct Answer</label>
            <div style="display:flex; gap:20px; margin-top:8px;">
                <label style="display:flex; align-items:center; gap:6px; font-weight:400; margin-top:0;"><input type="radio" name="tf_correct" value="True" checked style="width:auto;"> True</label>
                <label style="display:flex; align-items:center; gap:6px; font-weight:400; margin-top:0;"><input type="radio" name="tf_correct" value="False" style="width:auto;"> False</label>
            </div>
        </div>

        <!-- Short Answer Fields -->
        <div id="short_fields" style="display:none;">
            <label>Correct Answer (for reference / auto-check)</label>
            <input type="text" name="correct_answer" placeholder="Expected answer">
        </div>

        <button type="submit">Save Question</button>
    </form>

    <p class="foot-link"><a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>">&larr; Back to Questions</a></p>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('question_type').value;
    document.getElementById('mcq_fields').style.display = (type === 'mcq') ? 'block' : 'none';
    document.getElementById('tf_fields').style.display = (type === 'truefalse') ? 'block' : 'none';
    document.getElementById('short_fields').style.display = (type === 'short') ? 'block' : 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
