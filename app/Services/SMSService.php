<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\hotspot\AuditLog;

class SMSService
{
    private string $defaultDriver;
    private array $config;
    private array $drivers;

    public function __construct()
    {
        $this->defaultDriver = config('sms.default', 'africastalking');
        $this->config = config('sms', []);
        $this->drivers = $this->config['drivers'] ?? [];

        $this->validateConfiguration();
    }

    /**
     * Validate SMS configuration
     */
    private function validateConfiguration(): void
    {
        if (empty($this->drivers[$this->defaultDriver])) {
            throw new Exception("SMS driver '{$this->defaultDriver}' is not configured");
        }
    }

    /**
     * Send SMS using default driver
     */
    public function send(string $to, string $message, $driver = null): bool
    {
        $driver = $driver ?: $this->defaultDriver;

        try {
            // Check rate limiting
            if (!$this->checkRateLimit($to)) {
                Log::warning('SMS rate limit exceeded', [
                    'phone' => $this->maskPhoneNumber($to),
                    'driver' => $driver
                ]);
                return false;
            }

            // Check blacklist
            if ($this->isBlacklisted($to)) {
                Log::warning('SMS blocked - phone number blacklisted', [
                    'phone' => $this->maskPhoneNumber($to)
                ]);
                return false;
            }

            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($to);
            $processedMessage = $this->processMessage($message);

            Log::info('Sending SMS', [
                'phone' => $this->maskPhoneNumber($formattedPhone),
                'driver' => $driver,
                'message_length' => strlen($processedMessage)
            ]);

            // Send via specific driver
            $result = $this->sendViaDriver($driver, $formattedPhone, $processedMessage);

            if ($result['success']) {
                $this->logSuccessfulSend($formattedPhone, $processedMessage, $driver, $result);
                $this->incrementRateLimit($to);
                return true;
            } else {
                $this->logFailedSend($formattedPhone, $processedMessage, $driver, $result);
                return false;
            }

        } catch (Exception $e) {
            Log::error('SMS sending exception', [
                'phone' => $this->maskPhoneNumber($to),
                'driver' => $driver,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send voucher SMS with template
     */
    public function sendVoucherSMS(string $phone, array $voucherData): bool
    {
        $template = $this->config['templates']['voucher'] ?? null;

        if (!$template) {
            Log::error('Voucher SMS template not configured');
            return false;
        }

        $message = $this->populateTemplate($template['body'], [
            'code' => $voucherData['code'],
            'duration' => $voucherData['duration'],
            'speed' => $voucherData['speed'],
            'network' => $voucherData['network'] ?? 'YourHotspot',
            'expires' => $voucherData['expires']
        ]);

        $success = $this->send($phone, $message);

        if ($success) {
            Log::info('Voucher SMS sent successfully', [
                'phone' => $this->maskPhoneNumber($phone),
                'voucher_code' => $voucherData['code']
            ]);
        }

        return $success;
    }

    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmation(string $phone, array $paymentData): bool
    {
        $template = $this->config['templates']['payment_confirmation'] ?? null;

        if (!$template) {
            Log::error('Payment confirmation SMS template not configured');
            return false;
        }

        $message = $this->populateTemplate($template['body'], [
            'amount' => number_format($paymentData['amount']),
            'reference' => $paymentData['reference'],
            'voucher_code' => $paymentData['voucher_code']
        ]);

        return $this->send($phone, $message);
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $recipients, string $message, $driver = null): array
    {
        $results = [
            'total' => count($recipients),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($recipients as $phone) {
            $success = $this->send($phone, $message, $driver);
            $results['details'][$phone] = $success;

            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            // Small delay to avoid overwhelming the API
            usleep(500000); // 0.5 seconds
        }

        Log::info('Bulk SMS completed', [
            'total' => $results['total'],
            'success' => $results['success'],
            'failed' => $results['failed'],
            'driver' => $driver ?: $this->defaultDriver
        ]);

        return $results;
    }

    /**
     * Send via specific driver
     */
    private function sendViaDriver(string $driver, string $phone, string $message): array
    {
        switch ($driver) {
            case 'africastalking':
                return $this->sendViaAfricasTalking($phone, $message);

            case 'twilio':
                return $this->sendViaTwilio($phone, $message);

            case 'nexmo':
                return $this->sendViaNexmo($phone, $message);

            case 'fake':
                return $this->sendViaFake($phone, $message);

            case 'log':
                return $this->sendViaLog($phone, $message);

            default:
                throw new Exception("Unsupported SMS driver: {$driver}");
        }
    }

    /**
     * Send via Africa's Talking
     */
    private function sendViaAfricasTalking(string $phone, string $message): array
    {
        try {
            $config = $this->drivers['africastalking'];
            $url = $config['environment'] === 'production'
                ? 'https://api.africastalking.com/version1/messaging'
                : 'https://api.sandbox.africastalking.com/version1/messaging';

            $response = Http::withHeaders([
                'apiKey' => $config['api_key'],
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ])->asForm()->post($url, [
                'username' => $config['username'],
                'to' => $phone,
                'message' => $message,
                'from' => $config['sender_id'] ?? null
            ]);

            if (!$response->successful()) {
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
            }

            $data = $response->json();
            $recipient = $data['SMSMessageData']['Recipients'][0] ?? [];

            if (isset($recipient['status']) && $recipient['status'] === 'Success') {
                return [
                    'success' => true,
                    'message_id' => $recipient['messageId'] ?? null,
                    'cost' => $recipient['cost'] ?? null,
                    'response' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $recipient['status'] ?? 'Unknown error',
                    'response' => $data
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Send via Twilio
     */
    private function sendViaTwilio(string $phone, string $message): array
    {
        try {
            $config = $this->drivers['twilio'];
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$config['sid']}/Messages.json";

            $response = Http::withBasicAuth($config['sid'], $config['token'])
                ->asForm()
                ->post($url, [
                    'From' => $config['from'],
                    'To' => $phone,
                    'Body' => $message
                ]);

            if (!$response->successful()) {
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
            }

            $data = $response->json();

            return [
                'success' => true,
                'message_id' => $data['sid'] ?? null,
                'status' => $data['status'] ?? null,
                'response' => $data
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Send via Nexmo/Vonage
     */
    private function sendViaNexmo(string $phone, string $message): array
    {
        try {
            $config = $this->drivers['nexmo'];
            $url = 'https://rest.nexmo.com/sms/json';

            $response = Http::post($url, [
                'api_key' => $config['key'],
                'api_secret' => $config['secret'],
                'from' => $config['from'],
                'to' => $phone,
                'text' => $message
            ]);

            if (!$response->successful()) {
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
            }

            $data = $response->json();
            $message = $data['messages'][0] ?? [];

            if (isset($message['status']) && $message['status'] === '0') {
                return [
                    'success' => true,
                    'message_id' => $message['message-id'] ?? null,
                    'response' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $message['error-text'] ?? 'Unknown error',
                    'response' => $data
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Send via fake driver (for testing)
     */
    private function sendViaFake(string $phone, string $message): array
    {
        Log::info('FAKE SMS SENT', [
            'phone' => $phone,
            'message' => $message
        ]);

        return [
            'success' => true,
            'message_id' => 'fake_' . uniqid(),
            'response' => ['status' => 'fake_success']
        ];
    }

    /**
     * Send via log driver
     */
    private function sendViaLog(string $phone, string $message): array
    {
        $logChannel = $this->drivers['log']['channel'] ?? 'default';

        Log::channel($logChannel)->info('SMS LOG', [
            'phone' => $phone,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ]);

        return [
            'success' => true,
            'message_id' => 'log_' . uniqid(),
            'response' => ['status' => 'logged']
        ];
    }

    /**
     * Format phone number for international format
     */
    private function formatPhoneNumber(string $phone): string
    {
        $config = $this->config['formatting'] ?? [];
        $countryCode = $config['country_code'] ?? '256';
        $stripZeros = $config['strip_leading_zeros'] ?? true;
        $addPlus = $config['add_plus_sign'] ?? true;

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Strip leading zeros if configured
        if ($stripZeros) {
            $phone = ltrim($phone, '0');
        }

        // Add country code if not present
        if (!str_starts_with($phone, $countryCode)) {
            $phone = $countryCode . $phone;
        }

        // Add plus sign if configured
        if ($addPlus && !str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Process message content
     */
    private function processMessage(string $message): string
    {
        $config = $this->config['settings'] ?? [];

        // Add prefix if configured
        if (!empty($config['prefix'])) {
            $message = $config['prefix'] . ' ' . $message;
        }

        // Add suffix if configured
        if (!empty($config['suffix'])) {
            $message = $message . ' ' . $config['suffix'];
        }

        // Truncate if too long
        $maxLength = $config['max_length'] ?? 160;
        if (strlen($message) > $maxLength) {
            $message = substr($message, 0, $maxLength - 3) . '...';
            Log::warning('SMS message truncated', [
                'original_length' => strlen($message) + 3,
                'max_length' => $maxLength
            ]);
        }

        return $message;
    }

    /**
     * Populate message template with data
     */
    private function populateTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(string $phone): bool
    {
        $config = $this->config['rate_limiting'] ?? [];

        if (!($config['enabled'] ?? true)) {
            return true;
        }

        $key = 'sms_rate_limit:' . $this->hashPhone($phone);
        $maxPerMinute = $config['max_per_minute'] ?? 10;
        $maxPerHour = $config['max_per_hour'] ?? 100;
        $maxPerDay = $config['max_per_day'] ?? 500;

        // Check per minute limit
        if (RateLimiter::tooManyAttempts($key . ':minute', $maxPerMinute)) {
            return false;
        }

        // Check per hour limit
        if (RateLimiter::tooManyAttempts($key . ':hour', $maxPerHour)) {
            return false;
        }

        // Check per day limit
        if (RateLimiter::tooManyAttempts($key . ':day', $maxPerDay)) {
            return false;
        }

        return true;
    }

    /**
     * Increment rate limit counters
     */
    private function incrementRateLimit(string $phone): void
    {
        $key = 'sms_rate_limit:' . $this->hashPhone($phone);

        RateLimiter::hit($key . ':minute', 60); // 1 minute
        RateLimiter::hit($key . ':hour', 3600); // 1 hour
        RateLimiter::hit($key . ':day', 86400); // 1 day
    }

    /**
     * Check if phone number is blacklisted
     */
    private function isBlacklisted(string $phone): bool
    {
        $config = $this->config['blacklist'] ?? [];

        if (!($config['enabled'] ?? false)) {
            return false;
        }

        $blacklistedNumbers = explode(',', $config['numbers'] ?? '');
        $formattedPhone = $this->formatPhoneNumber($phone);

        foreach ($blacklistedNumbers as $number) {
            if (trim($number) === $formattedPhone) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hash phone number for privacy
     */
    private function hashPhone(string $phone): string
    {
        return hash('sha256', $phone . config('app.key'));
    }

    /**
     * Mask phone number for logging
     */
    private function maskPhoneNumber(string $phone): string
    {
        if (strlen($phone) <= 4) {
            return $phone;
        }

        return substr($phone, 0, 4) . str_repeat('*', strlen($phone) - 4);
    }

    /**
     * Log successful SMS send
     */
    private function logSuccessfulSend(string $phone, string $message, string $driver, array $result): void
    {
        Log::info('SMS sent successfully', [
            'phone' => $this->maskPhoneNumber($phone),
            'driver' => $driver,
            'message_id' => $result['message_id'] ?? null,
            'cost' => $result['cost'] ?? null,
            'message_length' => strlen($message)
        ]);

        // Audit log (optional - be careful with sensitive data)
        // if (config('sms.audit_logging', false)) {
        //     AuditLog::log('sms_sent', (object)['phone' => $this->hashPhone($phone)], null, [
        //         'driver' => $driver,
        //         'message_id' => $result['message_id'] ?? null,
        //         'success' => true
        //     ]);
        // }
    }

    /**
     * Log failed SMS send
     */
    private function logFailedSend(string $phone, string $message, string $driver, array $result): void
    {
        Log::error('SMS sending failed', [
            'phone' => $this->maskPhoneNumber($phone),
            'driver' => $driver,
            'error' => $result['error'] ?? 'Unknown error',
            'message_length' => strlen($message)
        ]);
    }

    /**
     * Get SMS statistics
     */
    public function getStats(): array
    {
        return [
            'default_driver' => $this->defaultDriver,
            'configured_drivers' => array_keys($this->drivers),
            'rate_limiting_enabled' => $this->config['rate_limiting']['enabled'] ?? false,
            'blacklist_enabled' => $this->config['blacklist']['enabled'] ?? false,
            'max_message_length' => $this->config['settings']['max_length'] ?? 160,
        ];
    }

    /**
     * Test SMS sending
     */
    public function testSend(string $phone, string $driver = null): bool
    {
        $testMessage = 'Test SMS from YourHotspot system at ' . now()->format('H:i:s');
        return $this->send($phone, $testMessage, $driver);
    }

    /**
     * Get delivery status (if supported by driver)
     */
    public function getDeliveryStatus(string $messageId, string $driver = null): ?array
    {
        $driver = $driver ?: $this->defaultDriver;

        try {
            switch ($driver) {
                case 'africastalking':
                    return $this->getAfricasTalkingDeliveryStatus($messageId);

                case 'twilio':
                    return $this->getTwilioDeliveryStatus($messageId);

                default:
                    Log::info('Delivery status not supported for driver', ['driver' => $driver]);
                    return null;
            }
        } catch (Exception $e) {
            Log::error('Failed to get delivery status', [
                'message_id' => $messageId,
                'driver' => $driver,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get Africa's Talking delivery status
     */
    private function getAfricasTalkingDeliveryStatus(string $messageId): ?array
    {
        $config = $this->drivers['africastalking'];
        $url = $config['environment'] === 'production'
            ? 'https://api.africastalking.com/version1/messaging'
            : 'https://api.sandbox.africastalking.com/version1/messaging';

        $response = Http::withHeaders([
            'apiKey' => $config['api_key'],
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->get($url, [
            'username' => $config['username'],
            'messageId' => $messageId
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Get Twilio delivery status
     */
    private function getTwilioDeliveryStatus(string $messageId): ?array
    {
        $config = $this->drivers['twilio'];
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$config['sid']}/Messages/{$messageId}.json";

        $response = Http::withBasicAuth($config['sid'], $config['token'])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Clear rate limit for phone number (admin function)
     */
    public function clearRateLimit(string $phone): void
    {
        $key = 'sms_rate_limit:' . $this->hashPhone($phone);

        RateLimiter::clear($key . ':minute');
        RateLimiter::clear($key . ':hour');
        RateLimiter::clear($key . ':day');

        Log::info('SMS rate limit cleared for phone', [
            'phone' => $this->maskPhoneNumber($phone)
        ]);
    }
}
