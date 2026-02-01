<?php
session_start(); // Resume session

// Check if session exists
if (!isset($_SESSION['username'])) {
    header('Location: page1.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>

<h2>Welcome <?php echo $_SESSION['username']; ?></h2>

<form method="post" action="logout.php">
    <button type="submit">Logout</button>
</form>

</body>
</html>
