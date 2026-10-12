<?php
require_once '../includes/auth_check.php';
checkRole('student');

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['student_quiz_id'])) {
    header("Location: quiz_list.php");
    exit();
}

$student_quiz_id = (int) $_POST['student_quiz_id'];
$quiz_id = (int) $_POST['quiz_id'];
$submitted_answers = isset($_POST['answer']) ? $_POST['answer'] : [];

// Verify this attempt belongs to the logged-in student and is still in progress
$check = mysqli_query($conn, "SELECT * FROM student_quiz WHERE student_quiz_id = $student_quiz_id AND student_id = $student_id");
if (mysqli_num_rows($check) == 0) {
    header("Location: quiz_list.php");
    exit();
}

$total_score = 0;

// Get all questions for this quiz
$questions = mysqli_query($conn, "SELECT * FROM questions WHERE quiz_id = $quiz_id");

while ($q = mysqli_fetch_assoc($questions)) {
    $question_id = $q['question_id'];
    $marks_obtained = 0;
    $selected_option_id = null;
    $answer_text = null;

    if (isset($submitted_answers[$question_id])) {
        if ($q['question_type'] == 'short') {
            // Compare submitted text with the stored correct answer (case-insensitive)
            $answer_text = mysqli_real_escape_string($conn, trim($submitted_answers[$question_id]));
            $correct = mysqli_query($conn, "SELECT * FROM options WHERE question_id = $question_id AND is_correct = 1 LIMIT 1");
            if (mysqli_num_rows($correct) > 0) {
                $correct_row = mysqli_fetch_assoc($correct);
                if (strtolower(trim($correct_row['option_text'])) == strtolower(trim($submitted_answers[$question_id]))) {
                    $marks_obtained = $q['marks'];
                }
            }
        } else {
            // MCQ or True/False
            $selected_option_id = (int) $submitted_answers[$question_id];
            $opt_check = mysqli_query($conn, "SELECT * FROM options WHERE option_id = $selected_option_id AND question_id = $question_id");
            if (mysqli_num_rows($opt_check) > 0) {
                $opt_row = mysqli_fetch_assoc($opt_check);
                if ($opt_row['is_correct'] == 1) {
                    $marks_obtained = $q['marks'];
                }
            }
        }
    }

    $total_score += $marks_obtained;

    // Insert into answers table
    $selected_sql = $selected_option_id ? $selected_option_id : "NULL";
    $answer_text_sql = $answer_text !== null ? "'$answer_text'" : "NULL";

    mysqli_query($conn, "INSERT INTO answers (student_quiz_id, question_id, selected_option_id, answer_text, marks_obtained)
                          VALUES ($student_quiz_id, $question_id, $selected_sql, $answer_text_sql, $marks_obtained)");
}

// Get quiz total marks
$quiz_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT total_marks FROM quizzes WHERE quiz_id = $quiz_id"));
$total_marks = $quiz_data['total_marks'] > 0 ? $quiz_data['total_marks'] : 1;
$percentage = round(($total_score / $total_marks) * 100, 2);

// Determine grade
if ($percentage >= 90) $grade = "A";
elseif ($percentage >= 80) $grade = "B";
elseif ($percentage >= 70) $grade = "C";
elseif ($percentage >= 60) $grade = "D";
else $grade = "F";

// Update student_quiz status
mysqli_query($conn, "UPDATE student_quiz SET score = $total_score, status = 'completed' WHERE student_quiz_id = $student_quiz_id");

// Insert result
mysqli_query($conn, "INSERT INTO results (student_quiz_id, total_score, percentage, grade) 
                      VALUES ($student_quiz_id, $total_score, $percentage, '$grade')");

header("Location: view_result.php?student_quiz_id=$student_quiz_id");
exit();
?>
