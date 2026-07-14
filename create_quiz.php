<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $duration_minutes = (int) $_POST['duration_minutes'];
    $teacher_id = $_SESSION['user_id'];

    if ($title == "" || $duration_minutes <= 0) {
        $error = "Please enter a quiz title and a valid duration.";
    } else {
        // total_marks starts at 0, it will be updated automatically as questions are added
        $sql = "INSERT INTO quizzes (title, description, total_marks, duration_minutes, teacher_id)
                VALUES ('$title', '$description', 0, $duration_minutes, $teacher_id)";

        if (mysqli_query($conn, $sql)) {
            $new_quiz_id = mysqli_insert_id($conn);
            header("Location: manage_questions.php?quiz_id=" . $new_quiz_id . "&created=1");
            exit();
        } else {
            $error = "Something went wrong: " . mysqli_error($conn);
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="form-box">
    <h2>Create New Quiz</h2>

    <?php if ($error != "") echo "<p class='alert-error'>$error</p>"; ?>

    <form method="POST" action="create_quiz.php">
        <label>Quiz Title</label>
        <input type="text" name="title" placeholder="e.g. PHP Basics Quiz" required>

        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Brief description of the quiz"></textarea>

        <label>Duration (in minutes)</label>
        <input type="number" name="duration_minutes" min="1" placeholder="e.g. 15" required>

        <button type="submit">Create Quiz &amp; Add Questions</button>
    </form>

    <p class="foot-link"><a href="manage_quizzes.php">&larr; Back to Manage Quizzes</a></p>
</div>

<?php include '../includes/footer.php'; ?>
