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

    <form action="signup.php" method="POST" id="signupForm" novalidate>
      <div class="form-group">
        <label for="name">Full Name</label>
        <input id="name" name="full_name" type="text" placeholder="Alex Doe" minlength="3" pattern="[a-zA-Z\s]+" title="Name should only contain letters and spaces, and be at least 3 characters long." required>
        <span class="error-message" id="name-error">Please enter a valid name (letters only, min 3 chars).</span>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="you@example.com" required>
        <span class="error-message" id="email-error">Please enter a valid email address.</span>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="••••••••" minlength="8" required>
        <span class="error-message" id="password-error">Password must be at least 8 characters long.</span>
      </div>

      <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <input id="confirm" name="confirm_password" type="password" placeholder="Repeat password" minlength="8" required>
        <span class="error-message" id="confirm-error">Passwords do not match.</span>
      </div>

      <button class="btn btn-success" type="submit">Sign Up</button>
    </form>

    <script>
      const form = document.getElementById('signupForm');
      const inputs = form.querySelectorAll('input');
      const password = document.getElementById('password');
      const confirm = document.getElementById('confirm');

      inputs.forEach(input => {
        input.addEventListener('blur', () => {
          validateField(input);
        });
        input.addEventListener('input', () => {
          const errorSpan = document.getElementById(input.id + '-error');
          if (errorSpan && errorSpan.style.display === 'block') {
            validateField(input);
          }
        });
      });

      function validateField(input) {
        const errorSpan = document.getElementById(input.id + '-error');

        // Custom check for password confirmation
        if (input.id === 'confirm') {
          if (password.value !== confirm.value) {
            input.setCustomValidity("Passwords don't match");
          } else {
            input.setCustomValidity("");
          }
        }

        if (!input.checkValidity()) {
          errorSpan.style.display = 'block';
          input.style.borderColor = '#ef4444';
        } else {
          errorSpan.style.display = 'none';
          input.style.borderColor = '';
        }
      }

      form.addEventListener('submit', (e) => {
        let isValid = true;
        inputs.forEach(input => {
          validateField(input);
          if (!input.checkValidity()) {
            isValid = false;
          }
        });

        if (!isValid) {
          e.preventDefault();
        }
      });
    </script>

    <p class="auth-meta">Already have an account? <a href="login.php">Login here</a></p>
    <p class="auth-meta"><a href="index.php">Back to Home</a></p>
  </div>
</body>

</html>