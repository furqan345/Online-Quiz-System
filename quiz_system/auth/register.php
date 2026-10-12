<?php
require_once '../config/db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $confirm_password = trim($_POST['confirm_password']);
    $contact_number = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));

    // Basic validation
    if ($name == "" || $email == "" || $password == "") {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already registered. Please login instead.";
        } else {
            // Insert new student (public registration is always role = student)
            $sql = "INSERT INTO users (name, email, password, role, contact_number, department)
                    VALUES ('$name', '$email', '$password', 'student', '$contact_number', '$department')";

            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Something went wrong: " . mysqli_error($conn);
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-shell">

        <div class="auth-side">
            <div class="dot-grid"></div>
            <div class="brand">
                <div class="brand-mark">Quiz<span>Portal</span></div>
                <h1>Join and start<br>testing your skills.</h1>
                <p class="tagline">Create your student account to attempt quizzes assigned by your instructors and track your results over time.</p>
            </div>
            <div class="stat-row">
                <div><strong>Fast</strong><span>2-minute setup</span></div>
                <div><strong>Free</strong><span>No cost</span></div>
                <div><strong>Secure</strong><span>Your data is safe</span></div>
            </div>
        </div>

        <div class="auth-form-panel">
            <h2>Create your account</h2>
            <p class="sub">Register as a student to get started.</p>

            <?php if ($error != "") echo "<p class='alert-error'>$error</p>"; ?>
            <?php if ($success != "") echo "<p class='alert-success'>$success</p>"; ?>

            <form method="POST" action="register.php">
                <label>Full name</label>
                <input type="text" name="name" placeholder="Your full name" required>

                <label>Email address</label>
                <input type="email" name="email" placeholder="you@example.com" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>

                <label>Confirm password</label>
                <input type="password" name="confirm_password" placeholder="Re-enter your password" required>

                <label>Contact number</label>
                <input type="text" name="contact_number" placeholder="Optional">

                <label>Department</label>
                <input type="text" name="department" placeholder="e.g. BSCS">

                <button type="submit">Create Account</button>
            </form>

            <p class="foot-link">Already have an account? <a href="login.php">Log in</a></p>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
