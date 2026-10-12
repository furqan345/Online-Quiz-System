<?php
require_once '../includes/auth_check.php';
checkRole('admin');

$admin_id = $_SESSION['user_id'];

if (isset($_GET['question_id']) && isset($_GET['quiz_id'])) {
    $question_id = (int) $_GET['question_id'];
    $quiz_id = (int) $_GET['quiz_id'];

    // Verify quiz belongs to this admin
    $check = mysqli_query($conn, "SELECT q.marks FROM questions q 
                INNER JOIN quizzes qz ON q.quiz_id = qz.quiz_id 
                WHERE q.question_id = $question_id AND qz.quiz_id = $quiz_id AND qz.teacher_id = $admin_id");

    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $marks = $row['marks'];

        // Delete question (options cascade automatically)
        mysqli_query($conn, "DELETE FROM questions WHERE question_id = $question_id");

        // Reduce total marks of quiz
        mysqli_query($conn, "UPDATE quizzes SET total_marks = GREATEST(total_marks - $marks, 0) WHERE quiz_id = $quiz_id");
    }

    header("Location: manage_questions.php?quiz_id=$quiz_id&deleted=1");
    exit();
}

header("Location: manage_quizzes.php");
exit();
?>
