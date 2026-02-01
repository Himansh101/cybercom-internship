<?php
session_start();

// Simulate login
if (isset($_POST['login'])) {
    $_SESSION['username'] = 'Intern';
    $_SESSION['login_time'] = time(); // store login time

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login Page</h2>

<form method="post">
    <button type="submit" name="login">Login</button>
</form>

</body>
</html>
