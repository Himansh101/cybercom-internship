<?php
// Handle form submission logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullName = $_POST['full_name'];
  $email = $_POST['email'];
  $mobile = $_POST['mobile'];
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];

  // Basic validation
  if ($password !== $confirm) {
    $error = "Passwords do not match!";
  } elseif (strlen($password) < 8) {
    $error = "Password must be at least 8 characters long!";
  } elseif (!preg_match("/[a-z]/", $password)) {
    $error = "Password must contain at least one lowercase letter!";
  } elseif (!preg_match("/[A-Z]/", $password)) {
    $error = "Password must contain at least one uppercase letter!";
  } elseif (!preg_match("/[0-9]/", $password)) {
    $error = "Password must contain at least one number!";
  } else {
    // Check if user already exists
    $users = [];
    if (file_exists('users.json')) {
      $json_data = file_get_contents('users.json');
      $users = json_decode($json_data, true) ?? [];
    }

    $userExists = false;

    foreach ($users as $user) {
      if ($user['email'] === $email) {
        $userExists = true;
        break;
      }
    }

    if ($userExists) {
      $error = "An account with this email already exists!";
    } else {
      // Create new user
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $newUser = [
        'id' => uniqid('user_', true),
        'name' => $fullName,
        'email' => $email,
        'mobile' => $mobile,
        'password' => $hashedPassword,
        'created_at' => date('Y-m-d H:i:s')
      ];

      $users[] = $newUser;
      file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));

      // Redirect to login with success message
      header("Location: login.php?registered=true");
      exit();
    }
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/auth.js" defer></script>
</head>

<body class="page-auth page-signup">
  <div class="auth-card">
    <h1 class="auth-title">Create Account</h1>

    <?php if (isset($error)): ?>
      <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
        <?php echo $error; ?>
      </p>
    <?php endif; ?>

    <form action="signup.php" method="POST" id="signupForm" novalidate>
      <div class="form-group">
        <label for="name">Full Name</label>
        <input id="name" name="full_name" type="text" placeholder="Alex Doe" minlength="3" pattern="[a-zA-Z\s]+" title="Name should only contain letters and spaces, and be at least 3 characters long." required>
        <span class="error-message" id="name-error">Please enter a valid name (letters only, min 3 chars).</span>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="you@example.com"
          pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
          title="Please enter a valid email address with a domain extension (e.g., .com, .org, .net)"
          required>
        <span class="error-message" id="email-error">Please enter a valid email address with domain extension.</span>
      </div>

      <div class="form-group">
        <label for="mobile">Mobile No</label>
        <input id="mobile" name="mobile" type="tel" placeholder="9876543210" pattern="[6-9][0-9]{9}" required>
        <span class="error-message" id="mobile-error">Please enter a valid mobile number.</span>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="••••••••"
          minlength="8"
          title="Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number."
          required>
        <span class="error-message" id="password-error">Password must be at least 8 characters with uppercase, lowercase, and number.</span>
      </div>

      <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <input id="confirm" name="confirm_password" type="password" placeholder="Repeat password"
          title="Please re-enter your password to confirm."
          required>
        <span class="error-message" id="confirm-error">Passwords do not match.</span>
      </div>

      <button class="btn btn-success" type="submit">Sign Up</button>
    </form>


    <p class="auth-meta">Already have an account? <a href="login.php">Login here</a></p>
    <p class="auth-meta"><a href="index.php">Back to Home</a></p>
  </div>
</body>

</html>