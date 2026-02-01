<?php
session_start();

// Check if session exists
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

//Session timeout

$timeout = 300; // 5 minutes

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=true");
    exit;
}

// Reset activity time
$_SESSION['login_time'] = time();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Welcome <?php echo $_SESSION['username']; ?> 👋</h2>

<p>You are logged in.</p>

<a href="logout.php">Logout</a>

</body>
</html>
