<?php
require_once '../config/db.php';

// Destroy all session data
session_unset();
session_destroy();

header("Location: login.php");
exit();
?>
