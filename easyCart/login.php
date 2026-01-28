<?php
session_start();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Load users from JSON file
  $users = [];
  if (file_exists('users.json')) {
    $json_data = file_get_contents('users.json');
    $users = json_decode($json_data, true) ?? [];
  }

  $userFound = false;

  foreach ($users as $user) {
    if ($user['email'] === $email) {
      if (password_verify($password, $user['password'])) {
        // Successful login - store user data in session
        $_SESSION['user'] = [
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email'],
          'mobile' => $user['mobile']
        ];
        header("Location: index.php");
        exit();
      } else {
        $error = "Invalid email or password.";
      }
      $userFound = true;
      break;
    }
  }

  if (!$userFound) {
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/auth.js" defer></script>
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

    <form action="login.php" method="POST" id="loginForm" novalidate>
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="you@example.com"
          pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
          title="Please enter a valid email address with a domain extension (e.g., .com, .org, .net)"
          required>
        <span class="error-message" id="email-error">Please enter a valid email address with domain extension.</span>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="••••••••" minlength="8" required>
        <span class="error-message" id="password-error">Password must be at least 8 characters long.</span>
      </div>

      <button class="btn btn-success" type="submit">Sign In</button>
    </form>


    <p class="auth-meta">New to EasyCart? <a href="signup.php">Create account</a></p>
    <p class="auth-meta"><a href="index.php">Back to Home</a></p>
  </div>
</body>

</html>