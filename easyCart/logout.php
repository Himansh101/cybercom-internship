<?php
session_start();
session_unset();
session_destroy();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <script>
        localStorage.clear();
        window.location.href = "index.php";
    </script>
    <meta http-equiv="refresh" content="2;url=index.php">
</head>
<body>
    <p>Clearing session data... If you are not redirected, <a href="index.php">click here</a>.</p>
</body>
</html>
<?php
exit();
?>