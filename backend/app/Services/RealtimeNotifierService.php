<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Sends realtime broadcast events to the websocket relay service.
 */
class RealtimeNotifierService
{
    public function broadcast(array $event): bool
    {
        $url = $_ENV['REALTIME_BROADCAST_URL'] ?? 'http://realtime-server:3001/broadcast';
        $body = json_encode($event, JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return $errno === 0 && $status >= 200 && $status < 300;
    }
}
