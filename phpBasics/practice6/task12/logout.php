<?php
session_start();

// Remove all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect back to Page 1
header('Location: page1.php');
exit();
?>
