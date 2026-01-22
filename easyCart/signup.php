<?php
// Handle form submission logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Basic Phase 2 Logic: Just check if passwords match
    if ($password === $confirm) {
        // In a real app, we'd save to DB. For now, we redirect to login with a success message.
        header("Location: login.php?registered=true");
        exit();
    } else {
        $error = "Passwords do not match!";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyCart | Signup</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/auth.css">
</head>

<body class="page-auth page-signup">
  <div class="auth-card">
    <h1 class="auth-title">Create Account</h1>

    <?php if (isset($error)): ?>
      <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
        <?php echo $error; ?>
      </p>
    <?php endif; ?>

    <form action="signup.php" method="POST">
      <label for="name">Full Name</label>
      <input id="name" name="full_name" type="text" placeholder="Alex Doe" required>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" placeholder="you@example.com" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" placeholder="••••••••" required>

      <label for="confirm">Confirm Password</label>
      <input id="confirm" name="confirm_password" type="password" placeholder="Repeat password" required>

      <button class="btn btn-success" type="submit">Sign Up</button>
    </form>
    
    <p class="auth-meta">Already have an account? <a href="login.php">Login here</a></p>
    <p class="auth-meta"><a href="index.php">Back to Home</a></p>
  </div>
</body>

</html>