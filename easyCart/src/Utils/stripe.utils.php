<?php
/**
 * Simple Stripe API Wrapper using cURL (No Composer required)
 */
require_once __DIR__ . '/../config/stripe.php';

function stripe_create_payment_intent($amount, $currency = 'inr', $metadata = []) {
    $url = 'https://api.stripe.com/v1/payment_intents';
    
    // Amount must be integer (e.g., cents/piese)
    $data = [
        'amount' => $amount,
        'currency' => strtolower($currency),
        'metadata' => $metadata,
        'automatic_payment_methods' => ['enabled' => 'true']
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        throw new Exception("Stripe cURL Error: " . $error);
    }
    
    $response = json_decode($result, true);
    
    if ($httpCode !== 200 || isset($response['error'])) {
        throw new Exception("Stripe Error: " . ($response['error']['message'] ?? 'Unknown error'));
    }

    return $response;
}

function stripe_retrieve_payment_intent($paymentIntentId) {
    $url = 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("Stripe cURL Error: " . $error);
    }

    return json_decode($result, true);
}
