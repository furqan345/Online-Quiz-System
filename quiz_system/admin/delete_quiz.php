<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];

if (isset($_GET['quiz_id'])) {
    $quiz_id = (int) $_GET['quiz_id'];
    // ON DELETE CASCADE in the database will automatically remove
    // related questions, options, student_quiz, answers, and results
    mysqli_query($conn, "DELETE FROM quizzes WHERE quiz_id = $quiz_id AND teacher_id = $admin_id");
}

header("Location: manage_quizzes.php?deleted=1");
exit();
?>
