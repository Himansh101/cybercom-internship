<?php
session_start();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Phase 2 Logic: Simulate a successful login for any non-empty input
    // In Phase 3, you will check these against a MySQL database
    if (!empty($email) && !empty($password)) {
        $_SESSION['user_email'] = $email;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}

// Check if user just arrived from a successful signup
$signup_success = isset($_GET['registered']) && $_GET['registered'] === 'true';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyCart | Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/auth.css">
</head>

<body class="page-auth page-login">
  <div class="auth-card">
    <h1 class="auth-title">Login</h1>

    <?php if ($signup_success): ?>
      <p style="color: #059669; background: #ecfdf5; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem; text-align: center;">
        Registration successful! Please login.
      </p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem; text-align: center;">
        <?php echo $error; ?>
      </p>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" placeholder="you@example.com" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" placeholder="••••••••" required>

      <button class="btn btn-success" type="submit">Sign In</button>
    </form>
    
    <p class="auth-meta">New to EasyCart? <a href="signup.php">Create account</a></p>
    <p class="auth-meta"><a href="index.php">Back to Home</a></p>
  </div>
</body>

</html>