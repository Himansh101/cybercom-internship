<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <?php foreach ($extraStyles as $style): ?>
        <link rel="stylesheet" href="assets/css/<?php echo $style; ?>">
    <?php endforeach; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/auth.js" defer></script>
</head>

<body class="page-auth page-signup">
    <div class="auth-card">
        <h1 class="auth-title">Create Account</h1>

        <?php if (isset($error)): ?>
            <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>

        <form action="signup" method="POST" id="signupForm" novalidate>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input id="name" name="full_name" type="text" placeholder="Alex Doe" minlength="3" pattern="[a-zA-Z\s]+" title="Name should only contain letters and spaces, and be at least 3 characters long." required value="<?php echo htmlspecialchars($fullName ?? ''); ?>">
                <span class="error-message" id="name-error">Please enter a valid name (letters only, min 3 chars).</span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="you@example.com"
                    pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                    title="Please enter a valid email address with a domain extension (e.g., .com, .org, .net)"
                    required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                <span class="error-message" id="email-error">Please enter a valid email address with domain extension.</span>
            </div>

            <div class="form-group">
                <label for="mobile">Mobile No</label>
                <input id="mobile" name="mobile" type="tel" placeholder="9876543210" pattern="[6-9][0-9]{9}" required value="<?php echo htmlspecialchars($mobile ?? ''); ?>">
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

        <p class="auth-meta">Already have an account? <a href="login">Login here</a></p>
        <p class="auth-meta"><a href="index">Back to Home</a></p>
    </div>
</body>

</html>
