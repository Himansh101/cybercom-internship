<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Lightweight SMTP mailer for local/demo scheduled report delivery.
 */
class SmtpMailerService
{
    private const BASE64_CHUNK_SIZE = 57 * 1024;

    public function sendCsvReport(string $to, string $subject, string $bodyText, string $filename, string $csvContent): void
    {
        $host = $_ENV['SMTP_HOST'] ?? 'mailhog';
        $port = (int) ($_ENV['SMTP_PORT'] ?? 1025);
        $from = $_ENV['SMTP_FROM'] ?? 'reports@example.local';

        $boundary = 'reporting-system-' . bin2hex(random_bytes(8));
        $headers = [
            "From: {$from}",
            'MIME-Version: 1.0',
            "Content-Type: multipart/mixed; boundary=\"{$boundary}\"",
        ];

        $message = [];
        $message[] = "Subject: {$subject}";
        $message[] = "To: {$to}";
        $message[] = implode("\r\n", $headers);
        $message[] = '';
        $message[] = "--{$boundary}";
        $message[] = 'Content-Type: text/plain; charset=UTF-8';
        $message[] = '';
        $message[] = $bodyText;
        $message[] = '';
        $message[] = "--{$boundary}";
        $message[] = "Content-Type: text/csv; name=\"{$filename}\"";
        $message[] = 'Content-Transfer-Encoding: base64';
        $message[] = "Content-Disposition: attachment; filename=\"{$filename}\"";
        $message[] = '';
        $message[] = chunk_split(base64_encode($csvContent));
        $message[] = "--{$boundary}--";
        $payload = implode("\r\n", $message);

        $socket = fsockopen($host, $port, $errno, $errstr, 10);
        if ($socket === false) {
            throw new \RuntimeException("SMTP connection failed: {$errstr} ({$errno})");
        }

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'HELO reporting-system', [250]);
            $this->command($socket, "MAIL FROM:<{$from}>", [250]);
            $this->command($socket, "RCPT TO:<{$to}>", [250, 251]);
            $this->command($socket, 'DATA', [354]);
            fwrite($socket, $payload . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    public function sendCsvReportFromFile(string $to, string $subject, string $bodyText, string $filename, string $filePath): void
    {
        $host = $_ENV['SMTP_HOST'] ?? 'mailhog';
        $port = (int) ($_ENV['SMTP_PORT'] ?? 1025);
        $from = $_ENV['SMTP_FROM'] ?? 'reports@example.local';
        $boundary = 'reporting-system-' . bin2hex(random_bytes(8));

        $socket = fsockopen($host, $port, $errno, $errstr, 10);
        if ($socket === false) {
            throw new \RuntimeException("SMTP connection failed: {$errstr} ({$errno})");
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            fclose($socket);
            throw new \RuntimeException('Unable to open CSV attachment');
        }

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'HELO reporting-system', [250]);
            $this->command($socket, "MAIL FROM:<{$from}>", [250]);
            $this->command($socket, "RCPT TO:<{$to}>", [250, 251]);
            $this->command($socket, 'DATA', [354]);

            fwrite($socket, "Subject: {$subject}\r\n");
            fwrite($socket, "To: {$to}\r\n");
            fwrite($socket, "From: {$from}\r\n");
            fwrite($socket, "MIME-Version: 1.0\r\n");
            fwrite($socket, "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n");
            fwrite($socket, "--{$boundary}\r\n");
            fwrite($socket, "Content-Type: text/plain; charset=UTF-8\r\n\r\n");
            fwrite($socket, $bodyText . "\r\n\r\n");
            fwrite($socket, "--{$boundary}\r\n");
            fwrite($socket, "Content-Type: text/csv; name=\"{$filename}\"\r\n");
            fwrite($socket, "Content-Transfer-Encoding: base64\r\n");
            fwrite($socket, "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n");

            while (!feof($handle)) {
                $chunk = fread($handle, self::BASE64_CHUNK_SIZE);
                if ($chunk === false) {
                    throw new \RuntimeException('Failed to read CSV attachment');
                }
                if ($chunk === '') {
                    continue;
                }
                fwrite($socket, chunk_split(base64_encode($chunk)));
            }

            fwrite($socket, "\r\n--{$boundary}--\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($handle);
            fclose($socket);
        }
    }

    private function command($socket, string $command, array $expectedCodes): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $expectedCodes);
    }

    private function expect($socket, array $expectedCodes): void
    {
        $response = '';
        do {
            $line = fgets($socket, 512);
            if ($line === false) {
                throw new \RuntimeException('SMTP server closed the connection unexpectedly');
            }
            $response .= $line;
        } while (isset($line[3]) && $line[3] === '-');

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new \RuntimeException('SMTP error: ' . trim($response));
        }
    }
}
