<?php
// Stripe Configuration
// Replace with your actual Stripe API keys

$stripeSecret = getenv('STRIPE_SECRET_KEY') ?: ($_ENV['STRIPE_SECRET_KEY'] ?? '');
$stripePublishable = getenv('STRIPE_PUBLISHABLE_KEY') ?: ($_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');

define('STRIPE_SECRET_KEY', $stripeSecret);
define('STRIPE_PUBLISHABLE_KEY', $stripePublishable);