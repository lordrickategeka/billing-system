<?php
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Transaction;

class FlutterwaveService
{
    private ?string $publicKey;
    private ?string $secretKey;
    private ?string $encryptionKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->publicKey = config('flutterwave.public_key');
        $this->secretKey = config('flutterwave.secret_key');
        $this->encryptionKey = config('flutterwave.encryption_key');
        $this->baseUrl = config('flutterwave.base_url', 'https://api.flutterwave.com/v3');
        $this->timeout = config('flutterwave.transaction.timeout', 30);

        $this->validateConfiguration();
    }

    /**
     * Validate Flutterwave configuration
     */
    private function validateConfiguration(): void
    {
        if (empty($this->publicKey) || empty($this->secretKey)) {
            throw new Exception('Flutterwave API keys are not configured properly. Please check your .env file.');
        }

        if (empty($this->encryptionKey)) {
            Log::warning('Flutterwave encryption key is not configured');
        }
    }

    /**
     * Initialize payment and get payment link
     */
    public function initializePayment(array $paymentData): ?string
    {
        try {
            $payload = $this->preparePaymentPayload($paymentData);

            Log::info('Initializing Flutterwave payment', [
                'tx_ref' => $payload['tx_ref'],
                'amount' => $payload['amount'],
                'currency' => $payload['currency']
            ]);

            $response = $this->makeApiCall('POST', '/payments', $payload);

            if ($response['status'] === 'success' && isset($response['data']['link'])) {
                Log::info('Payment initialization successful', [
                    'tx_ref' => $payload['tx_ref'],
                    'payment_link' => $response['data']['link']
                ]);

                // Cache payment data for later verification
                $this->cachePaymentData($payload['tx_ref'], $payload);

                return $response['data']['link'];
            }

            Log::error('Payment initialization failed', [
                'tx_ref' => $payload['tx_ref'],
                'response' => $response
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Payment initialization exception', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            return null;
        }
    }

    /**
     * Verify payment with Flutterwave
     */
    public function verifyPayment(string $transactionId): ?array
    {
        try {
            Log::info('Verifying payment with Flutterwave', [
                'transaction_id' => $transactionId
            ]);

            $response = $this->makeApiCall('GET', "/transactions/{$transactionId}/verify");

            if ($response['status'] === 'success') {
                $transactionData = $response['data'];

                Log::info('Payment verification successful', [
                    'transaction_id' => $transactionId,
                    'status' => $transactionData['status'],
                    'amount' => $transactionData['amount'],
                    'currency' => $transactionData['currency']
                ]);

                // Additional security verification
                if ($this->validateTransactionSecurity($transactionData)) {
                    return $transactionData;
                } else {
                    Log::warning('Payment verification failed security check', [
                        'transaction_id' => $transactionId
                    ]);
                    return null;
                }
            }

            Log::warning('Payment verification failed', [
                'transaction_id' => $transactionId,
                'response' => $response
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Payment verification exception', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Handle webhook payload
     */
    public function handleWebhook(array $payload): bool
    {
        try {
            Log::info('Processing Flutterwave webhook', [
                'event' => $payload['event'] ?? 'unknown',
                'transaction_id' => $payload['data']['id'] ?? 'unknown'
            ]);

            if (!$this->validateWebhookSignature($payload)) {
                Log::error('Webhook signature validation failed');
                return false;
            }

            $event = $payload['event'] ?? '';
            $transactionData = $payload['data'] ?? [];

            switch ($event) {
                case 'charge.completed':
                    return $this->processChargeCompleted($transactionData);

                case 'transfer.completed':
                    return $this->processTransferCompleted($transactionData);

                default:
                    Log::info('Unhandled webhook event', ['event' => $event]);
                    return true;
            }

        } catch (Exception $e) {
            Log::error('Webhook processing exception', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $reference): ?array
    {
        try {
            $response = $this->makeApiCall('GET', "/transactions", [
                'tx_ref' => $reference
            ]);

            if ($response['status'] === 'success' && !empty($response['data'])) {
                return $response['data'][0];
            }

            return null;

        } catch (Exception $e) {
            Log::error('Failed to get payment status', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(string $transactionId, float $amount, string $reason = 'Customer request'): ?array
    {
        try {
            Log::info('Initiating payment refund', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'reason' => $reason
            ]);

            $payload = [
                'amount' => $amount,
                'comments' => $reason
            ];

            $response = $this->makeApiCall('POST', "/transactions/{$transactionId}/refund", $payload);

            if ($response['status'] === 'success') {
                Log::info('Refund processed successfully', [
                    'transaction_id' => $transactionId,
                    'refund_id' => $response['data']['id'] ?? 'unknown'
                ]);

                return $response['data'];
            }

            Log::error('Refund processing failed', [
                'transaction_id' => $transactionId,
                'response' => $response
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Refund processing exception', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get transaction analytics
     */
    public function getTransactionAnalytics(string $from, string $to): ?array
    {
        try {
            $response = $this->makeApiCall('GET', '/transactions', [
                'from' => $from,
                'to' => $to,
                'currency' => config('flutterwave.currency', 'UGX')
            ]);

            if ($response['status'] === 'success') {
                return $this->processAnalyticsData($response['data']);
            }

            return null;

        } catch (Exception $e) {
            Log::error('Failed to get transaction analytics', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeApiCall('GET', '/transactions', ['page' => 1, 'size' => 1]);
            return $response['status'] === 'success';

        } catch (Exception $e) {
            Log::error('Flutterwave connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get supported banks for bank transfer
     */
    public function getSupportedBanks(string $country = 'UG'): array
    {
        try {
            $cacheKey = "flutterwave_banks_{$country}";

            return Cache::remember($cacheKey, 3600, function () use ($country) {
                $response = $this->makeApiCall('GET', '/banks/' . $country);

                if ($response['status'] === 'success') {
                    return $response['data'];
                }

                return [];
            });

        } catch (Exception $e) {
            Log::error('Failed to get supported banks', [
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Validate card details
     */
    public function validateCard(array $cardData): array
    {
        try {
            $response = $this->makeApiCall('POST', '/charges', [
                'card_number' => $cardData['number'],
                'cvv' => $cardData['cvv'],
                'expiry_month' => $cardData['expiry_month'],
                'expiry_year' => $cardData['expiry_year'],
                'currency' => config('flutterwave.currency', 'UGX'),
                'amount' => 1, // Minimal amount for validation
                'tx_ref' => 'validation_' . time(),
                'validate_only' => true
            ]);

            return [
                'valid' => $response['status'] === 'success',
                'message' => $response['message'] ?? 'Unknown error',
                'data' => $response['data'] ?? []
            ];

        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Prepare payment payload
     */
    private function preparePaymentPayload(array $paymentData): array
    {
        $payload = [
            'tx_ref' => $paymentData['tx_ref'],
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? config('flutterwave.currency', 'UGX'),
            'redirect_url' => $paymentData['redirect_url'] ?? config('flutterwave.redirect_url'),
            'customer' => [
                'email' => $paymentData['customer']['email'],
                'phonenumber' => $paymentData['customer']['phone_number'] ?? null,
                'name' => $paymentData['customer']['name'] ?? 'Customer'
            ],
            'customizations' => [
                'title' => $paymentData['customizations']['title'] ?? config('flutterwave.customizations.title'),
                'description' => $paymentData['customizations']['description'] ?? config('flutterwave.customizations.description'),
                'logo' => $paymentData['customizations']['logo'] ?? config('flutterwave.customizations.logo')
            ]
        ];

        // Add payment options if specified
        if (isset($paymentData['payment_options'])) {
            $payload['payment_options'] = $paymentData['payment_options'];
        } else {
            $payload['payment_options'] = config('flutterwave.payment_options', 'mobilemoneyuganda,card');
        }

        // Add metadata
        $payload['meta'] = [
            'source' => 'hotspot_management',
            'created_at' => now()->toISOString()
        ];

        return $payload;
    }

    /**
     * Make API call to Flutterwave
     */
    private function makeApiCall(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        try {
            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->$method($url, $data);

            if (!$response->successful()) {
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
            }

            $responseData = $response->json();

            Log::debug('Flutterwave API call completed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $responseData['status'] ?? 'unknown'
            ]);

            return $responseData;

        } catch (Exception $e) {
            Log::error('Flutterwave API call failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate transaction security
     */
    private function validateTransactionSecurity(array $transactionData): bool
    {
        // Check if transaction is successful
        if ($transactionData['status'] !== 'successful') {
            return false;
        }

        // Verify currency
        $expectedCurrency = config('flutterwave.currency', 'UGX');
        if ($transactionData['currency'] !== $expectedCurrency) {
            Log::warning('Currency mismatch in transaction', [
                'expected' => $expectedCurrency,
                'received' => $transactionData['currency']
            ]);
            return false;
        }

        // Check if transaction reference exists in our system
        $txRef = $transactionData['tx_ref'];
        $transaction = Transaction::where('reference', $txRef)->first();

        if (!$transaction) {
            Log::warning('Transaction reference not found in system', [
                'tx_ref' => $txRef
            ]);
            return false;
        }

        // Verify amount matches
        if ((float) $transactionData['amount'] !== (float) $transaction->amount) {
            Log::warning('Amount mismatch in transaction', [
                'expected' => $transaction->amount,
                'received' => $transactionData['amount']
            ]);
            return false;
        }

        return true;
    }

    /**
     * Validate webhook signature
     */
    private function validateWebhookSignature(array $payload): bool
    {
        $webhookHash = config('flutterwave.webhook_hash');

        if (empty($webhookHash)) {
            Log::warning('Webhook hash not configured, skipping signature validation');
            return true; // Allow if not configured
        }

        $signature = request()->header('verif-hash');

        if ($signature !== $webhookHash) {
            Log::error('Webhook signature validation failed', [
                'expected' => $webhookHash,
                'received' => $signature
            ]);
            return false;
        }

        return true;
    }

    /**
     * Process charge completed webhook
     */
    private function processChargeCompleted(array $transactionData): bool
    {
        try {
            $txRef = $transactionData['tx_ref'] ?? null;
            $flutterwaveId = $transactionData['id'] ?? null;

            if (!$txRef || !$flutterwaveId) {
                Log::error('Missing required transaction data in webhook');
                return false;
            }

            $transaction = Transaction::where('reference', $txRef)->first();

            if (!$transaction) {
                Log::warning('Transaction not found for webhook', ['tx_ref' => $txRef]);
                return false;
            }

            if ($transaction->status === 'completed') {
                Log::info('Transaction already completed', ['tx_ref' => $txRef]);
                return true;
            }

            // Verify the payment
            $verificationData = $this->verifyPayment($flutterwaveId);

            if ($verificationData && $verificationData['status'] === 'successful') {
                $transaction->markAsCompleted($flutterwaveId, $verificationData);

                Log::info('Transaction marked as completed via webhook', [
                    'transaction_id' => $transaction->id,
                    'tx_ref' => $txRef
                ]);

                return true;
            }

            Log::error('Payment verification failed in webhook processing', [
                'tx_ref' => $txRef,
                'flutterwave_id' => $flutterwaveId
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('Error processing charge completed webhook', [
                'error' => $e->getMessage(),
                'transaction_data' => $transactionData
            ]);
            return false;
        }
    }

    /**
     * Process transfer completed webhook
     */
    private function processTransferCompleted(array $transactionData): bool
    {
        // Handle transfer/refund completions
        Log::info('Transfer completed webhook received', [
            'transfer_id' => $transactionData['id'] ?? 'unknown'
        ]);

        // Add transfer processing logic here if needed
        return true;
    }

    /**
     * Process analytics data
     */
    private function processAnalyticsData(array $transactions): array
    {
        $analytics = [
            'total_amount' => 0,
            'total_transactions' => count($transactions),
            'successful_transactions' => 0,
            'failed_transactions' => 0,
            'pending_transactions' => 0,
            'by_payment_method' => [],
            'by_day' => []
        ];

        foreach ($transactions as $transaction) {
            $analytics['total_amount'] += (float) $transaction['amount'];

            switch ($transaction['status']) {
                case 'successful':
                    $analytics['successful_transactions']++;
                    break;
                case 'failed':
                    $analytics['failed_transactions']++;
                    break;
                default:
                    $analytics['pending_transactions']++;
            }

            // Group by payment method
            $method = $transaction['payment_type'] ?? 'unknown';
            if (!isset($analytics['by_payment_method'][$method])) {
                $analytics['by_payment_method'][$method] = ['count' => 0, 'amount' => 0];
            }
            $analytics['by_payment_method'][$method]['count']++;
            $analytics['by_payment_method'][$method]['amount'] += (float) $transaction['amount'];

            // Group by day
            $date = date('Y-m-d', strtotime($transaction['created_at']));
            if (!isset($analytics['by_day'][$date])) {
                $analytics['by_day'][$date] = ['count' => 0, 'amount' => 0];
            }
            $analytics['by_day'][$date]['count']++;
            $analytics['by_day'][$date]['amount'] += (float) $transaction['amount'];
        }

        return $analytics;
    }

    /**
     * Cache payment data temporarily
     */
    private function cachePaymentData(string $reference, array $data): void
    {
        Cache::put("flw_payment_{$reference}", $data, 3600); // Cache for 1 hour
    }

    /**
     * Get cached payment data
     */
    public function getCachedPaymentData(string $reference): ?array
    {
        return Cache::get("flw_payment_{$reference}");
    }

    /**
     * Clear cached payment data
     */
    public function clearCachedPaymentData(string $reference): void
    {
        Cache::forget("flw_payment_{$reference}");
    }

    /**
     * Format amount for Flutterwave (remove decimals for some currencies)
     */
    private function formatAmount(float $amount, string $currency = 'UGX'): int
    {
        // UGX doesn't use decimal places
        if ($currency === 'UGX') {
            return (int) $amount;
        }

        // For other currencies, multiply by 100 (cents)
        return (int) ($amount * 100);
    }

    /**
     * Get service statistics
     */
    public function getServiceStats(): array
    {
        return [
            'connection_status' => $this->testConnection(),
            'public_key' => substr($this->publicKey, 0, 10) . '...',
            'base_url' => $this->baseUrl,
            'timeout' => $this->timeout,
            'currency' => config('flutterwave.currency'),
            'payment_options' => config('flutterwave.payment_options'),
        ];
    }
}
