<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EasyCart'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <?php if (isset($extraStyles)): foreach ($extraStyles as $style): ?>
        <link rel="stylesheet" href="assets/css/<?php echo $style; ?>">
    <?php endforeach; endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/auth.js" defer></script>
</head>

<body class="page-<?php echo $currentPage ?? 'default'; ?>" data-is-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
    <header>
        <div class="logo">EasyCart</div>
        <nav>
            <a href="index.php" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a>
            <a href="plp.php" class="<?php echo ($currentPage ?? '') === 'products' ? 'active' : ''; ?>">Products</a>
            <a href="cart.php" id="cart-nav-link" class="<?php echo ($currentPage ?? '') === 'cart' ? 'active' : ''; ?>">Cart<?php if ($cartQuantity > 0): ?><span class="cart-badge"><?php echo $cartQuantity; ?></span><?php endif; ?></a>
            <?php if ($isLoggedIn): ?>
                <a href="orders.php" class="<?php echo ($currentPage ?? '') === 'orders' ? 'active' : ''; ?>">My Orders</a>
                <span class="user-greeting" >
                    Hi, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>
                </span>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" class="<?php echo ($currentPage ?? '') === 'login' ? 'active' : ''; ?>">Login</a>
            <?php endif; ?>
        </nav>
        <button class="mobile-menu-btn" id="mobile-menu-btn">
            <i class="ri-menu-line"></i>
        </button>
    </header>
    <main>
