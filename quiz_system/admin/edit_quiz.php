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

// Fetch quiz (only if it belongs to this admin)
$result = mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_id = $quiz_id AND teacher_id = $admin_id");
if (mysqli_num_rows($result) == 0) {
    header("Location: manage_quizzes.php");
    exit();
}
$quiz = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $duration_minutes = (int) $_POST['duration_minutes'];
    $status = $_POST['status'];

    if ($title == "" || $duration_minutes <= 0) {
        $error = "Please enter a valid title and duration.";
    } else {
        $sql = "UPDATE quizzes SET title='$title', description='$description', 
                duration_minutes=$duration_minutes, status='$status' 
                WHERE quiz_id = $quiz_id AND teacher_id = $admin_id";
        if (mysqli_query($conn, $sql)) {
            header("Location: manage_quizzes.php?updated=1");
            exit();
        } else {
            $error = "Something went wrong: " . mysqli_error($conn);
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="form-box">
    <h2>Edit Quiz</h2>

    <?php if ($error != "") echo "<p class='alert-error'>$error</p>"; ?>

    <form method="POST" action="edit_quiz.php?quiz_id=<?php echo $quiz_id; ?>">
        <label>Quiz Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>

        <label>Description</label>
        <textarea name="description" rows="3"><?php echo htmlspecialchars($quiz['description']); ?></textarea>

        <label>Duration (in minutes)</label>
        <input type="number" name="duration_minutes" min="1" value="<?php echo $quiz['duration_minutes']; ?>" required>

        <label>Status</label>
        <select name="status">
            <option value="active" <?php echo $quiz['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $quiz['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>

        <button type="submit">Save Changes</button>
    </form>

    <p class="foot-link"><a href="manage_quizzes.php">&larr; Back to Manage Quizzes</a></p>
</div>

<?php include '../includes/footer.php'; ?>
