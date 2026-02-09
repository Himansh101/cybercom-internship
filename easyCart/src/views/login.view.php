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

<body class="page-auth page-login">
    <div class="auth-card">
        <h1 class="auth-title">Login</h1>

        <?php if ($signup_success): ?>
            <p
                style="color: #059669; background: #ecfdf5; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem; text-align: center;">
                Registration successful! Please login.
            </p>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p
                style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem; text-align: center;">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>

        <form action="login" method="POST" id="loginForm" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="you@example.com"
                    pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                    title="Please enter a valid email address with a domain extension (e.g., .com, .org, .net)" required
                    value="<?php echo htmlspecialchars($email ?? ''); ?>">
                <span class="error-message" id="email-error">Please enter a valid email address with domain
                    extension.</span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="••••••••" minlength="8" required>
                <span class="error-message" id="password-error">Password must be at least 8 characters long.</span>
            </div>

            <button class="btn btn-success" type="submit">Sign In</button>
        </form>

        <p class="auth-meta">New to EasyCart? <a href="signup">Create account</a></p>
        <p class="auth-meta"><a href="index">Back to Home</a></p>
    </div>
</body>

</html>