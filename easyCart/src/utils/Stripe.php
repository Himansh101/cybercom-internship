<?php
namespace App\Utils;

use Exception;

class Stripe
{
    public static function createPaymentIntent($amount, $currency = 'inr', $metadata = [])
    {
        // Re-using config constants which should be loaded
        // However, constants like STRIPE_SECRET_KEY might need to be ensured.
        // init.php will load config/stripe.php or I should load it here?
        // It's safer if this class manages config or assumes config is loaded.
        // I will assume global constant OR require it.
        if (!defined('STRIPE_SECRET_KEY')) {
            require_once __DIR__ . '/../config/stripe.php';
        }

        $url = 'https://api.stripe.com/v1/payment_intents';
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

        if ($error)
            throw new Exception("Stripe cURL Error: " . $error);

        $response = json_decode($result, true);

        if ($httpCode !== 200 || isset($response['error'])) {
            throw new Exception("Stripe Error: " . ($response['error']['message'] ?? 'Unknown error'));
        }

        return $response;
    }

    public static function retrievePaymentIntent($paymentIntentId)
    {
        if (!defined('STRIPE_SECRET_KEY')) {
            require_once __DIR__ . '/../config/stripe.php';
        }

        $url = 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error)
            throw new Exception("Stripe cURL Error: " . $error);

        return json_decode($result, true);
    }
}
