<?php
require_once '../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    if ($email == "" || $password == "") {
        $error = "Please enter both email and password.";
    } else {
        // Plain text password check (no hashing, as per project requirement)
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Save session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'student') {
                header("Location: ../student/dashboard.php");
                exit();
            } elseif ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            } elseif ($user['role'] == 'superadmin') {
                header("Location: ../superadmin/dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid email or password.";
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
                <h1>Assess smarter.<br>Grade instantly.</h1>
                <p class="tagline">A single platform for teachers to build quizzes and for students to take them, with results generated the moment the last answer is submitted.</p>
            </div>
            <div class="stat-row">
                <div><strong>MCQ</strong><span>Multiple choice</span></div>
                <div><strong>T/F</strong><span>True or false</span></div>
                <div><strong>Auto</strong><span>Instant grading</span></div>
            </div>
        </div>

        <div class="auth-form-panel">
            <h2>Welcome back</h2>
            <p class="sub">Log in to continue to your dashboard.</p>

            <?php if ($error != "") echo "<p class='alert-error'>$error</p>"; ?>

            <form method="POST" action="login.php">
                <label>Email address</label>
                <input type="email" name="email" placeholder="you@example.com" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>

                <button type="submit">Log In</button>
            </form>

            <p class="foot-link">New student? <a href="register.php">Create an account</a></p>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
