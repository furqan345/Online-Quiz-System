<?php
require_once '../includes/auth_check.php';
checkRole('superadmin');

$error = "";
$success = "";

// Handle Add Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));
    $contact_number = mysqli_real_escape_string($conn, trim($_POST['contact_number']));

    if ($name == "" || $email == "" || $password == "") {
        $error = "Please fill in all required fields.";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already in use.";
        } else {
            $sql = "INSERT INTO users (name, email, password, role, department, contact_number)
                    VALUES ('$name', '$email', '$password', 'admin', '$department', '$contact_number')";
            if (mysqli_query($conn, $sql)) {
                $success = "Admin account created successfully.";
            } else {
                $error = "Something went wrong: " . mysqli_error($conn);
            }
        }
    }
}

// Handle Delete Admin
if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $delete_id AND role = 'admin'");
    $success = "Admin account deleted.";
}

$admins = mysqli_query($conn, "SELECT u.*, 
            (SELECT COUNT(*) FROM quizzes WHERE teacher_id = u.user_id) as quiz_count
            FROM users u WHERE role='admin' ORDER BY created_at DESC");
?>
<?php include '../includes/header.php'; ?>

<div class="page-header">
    <h2>Manage Admins</h2>
</div>

<div class="form-box" style="margin-left:0;">
    <h2>Add New Admin</h2>

    <?php if ($error != "") echo "<p class='alert-error'>$error</p>"; ?>
    <?php if ($success != "") echo "<p class='alert-success'>$success</p>"; ?>

    <form method="POST" action="manage_admins.php">
        <label>Full Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Department</label>
        <input type="text" name="department" placeholder="e.g. Computer Science">

        <label>Contact Number</label>
        <input type="text" name="contact_number">

        <button type="submit">Add Admin</button>
    </form>
</div>

<h3 style="margin-top:35px; color: var(--navy);">All Admins</h3>
<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Quizzes Created</th>
        <th>Joined</th>
        <th>Action</th>
    </tr>
    <?php if (mysqli_num_rows($admins) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($admins)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['department'] ?: '-'); ?></td>
            <td><?php echo $row['quiz_count']; ?></td>
            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
            <td><a href="manage_admins.php?delete_id=<?php echo $row['user_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this admin? Their quizzes will also be removed.');">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No admins added yet.</td></tr>
    <?php endif; ?>
</table>

<?php include '../includes/footer.php'; ?>
