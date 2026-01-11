<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FlutterwaveService;
use App\Services\SMSService;
use Exception;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Voucher;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private FlutterwaveService $flutterwave;
    private SMSService $sms;

    public function __construct(
        FlutterwaveService $flutterwave,
        SMSService $sms
    ) {
        $this->flutterwave = $flutterwave;
        $this->sms = $sms;

        // NOTE: Removed MikroTikService dependency for reverse flow architecture
        // MikroTik will fetch vouchers from our API instead
    }

    /**
     * Handle checkout from hotspot page (NEW - for reverse flow)
     */
    public function checkout(Request $request)
    {
        try {
            $validated = $request->validate([
                'plan_type' => 'required|string|in:basic,premium,daily',
                'amount' => 'required|numeric|min:1000|max:50000',
                'duration' => 'required|string',
                'speed' => 'required|string',
                'phone' => 'required|string|min:10|max:15',
                'email' => 'nullable|email|max:255',
                'return_url' => 'required|url'
            ]);

            // Format and validate phone number
            $formattedPhone = $this->formatPhoneNumber($validated['phone']);
            if (!$formattedPhone) {
                return redirect($validated['return_url'] . '?' . http_build_query([
                    'payment_status' => 'error',
                    'message' => 'Invalid phone number format'
                ]));
            }

            // Find or create customer
            $customer = Customer::firstOrCreate(
                ['phone' => $formattedPhone],
                [
                    'email' => $validated['email'],
                    'name' => 'WiFi Customer'
                ]
            );

            // Generate unique transaction reference
            $reference = 'HSP_' . time() . '_' . rand(1000, 9999);

            // Create transaction
            $transaction = Transaction::create([
                'reference' => $reference,
                'customer_id' => $customer->id,
                'plan_type' => $validated['plan_type'], // Store plan type directly
                'amount' => $validated['amount'],
                'duration' => $validated['duration'],
                'speed' => $validated['speed'],
                'status' => 'pending',
                'payment_method' => 'mobile_money',
                'return_url' => $validated['return_url']
            ]);

            // Log transaction creation
            AuditLog::log('transaction_created', $transaction, null, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'plan_type' => $validated['plan_type']
            ]);

            // Initialize Flutterwave payment
            $paymentData = [
                'tx_ref' => $transaction->reference,
                'amount' => $validated['amount'],
                'currency' => 'UGX',
                'customer' => [
                    'email' => $customer->email ?: 'customer@yourhotspot.com',
                    'phone_number' => $customer->phone,
                    'name' => $customer->name ?: 'WiFi Customer'
                ],
                'customizations' => [
                    'title' => 'YourHotspot WiFi Payment',
                    'description' => $this->getPlanDescription($validated['plan_type']),
                    'logo' => asset('images/logo.png')
                ],
                'redirect_url' => route('payment.callback'),
                'payment_options' => 'mobilemoneyuganda,card'
            ];

            $paymentLink = $this->flutterwave->initializePayment($paymentData);

            if ($paymentLink) {
                Log::info('Payment initialized successfully', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $validated['amount'],
                    'plan_type' => $validated['plan_type']
                ]);

                // Redirect to Flutterwave payment page
                return redirect($paymentLink);
            } else {
                // Mark transaction as failed
                $transaction->update(['status' => 'failed']);

                return redirect($validated['return_url'] . '?' . http_build_query([
                    'payment_status' => 'error',
                    'message' => 'Unable to initialize payment. Please try again.'
                ]));
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Checkout validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return redirect($request->input('return_url', 'http://hotspot.local/login') . '?' . http_build_query([
                'payment_status' => 'error',
                'message' => 'Invalid payment data. Please try again.'
            ]));
        } catch (Exception $e) {
            Log::error('Checkout processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect($request->input('return_url', 'http://hotspot.local/login') . '?' . http_build_query([
                'payment_status' => 'error',
                'message' => 'Payment processing error. Please try again.'
            ]));
        }
    }

    /**
     * Show payment form page
     */
    public function showPaymentPage(Request $request)
    {
        try {
            // Get available plans
            $plans = Plan::active()->ordered()->get();

            if ($plans->isEmpty()) {
                return view('payment.error', [
                    'message' => 'No payment plans are currently available. Please contact support.'
                ]);
            }

            // Log page visit
            Log::info('Payment page visited', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'plan' => $request->get('plan')
            ]);

            return view('payment.form', compact('plans'));
        } catch (Exception $e) {
            Log::error('Error loading payment page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('payment.error', [
                'message' => 'Unable to load payment page. Please try again later.'
            ]);
        }
    }

    /**
     * Process payment initialization (LEGACY - keeping for compatibility)
     */
    public function processPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|exists:hp_plans,id', // Fixed table name
                'phone' => 'required|string|min:10|max:15',
                'email' => 'nullable|email|max:255',
                'reference' => 'required|string|unique:hp_transactions,reference' // Fixed table name
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            // Check if plan is active
            if (!$plan->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected plan is not available'
                ], 400);
            }

            // Format and validate phone number
            $formattedPhone = $this->formatPhoneNumber($validated['phone']);
            if (!$formattedPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format'
                ], 400);
            }

            // Find or create customer
            $customer = Customer::firstOrCreate(
                ['phone' => $formattedPhone],
                [
                    'email' => $validated['email'],
                    'name' => 'Customer'
                ]
            );

            // Create transaction
            $transaction = ModelsTransaction::create([
                'reference' => $validated['reference'],
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'amount' => $plan->amount,
                'status' => 'pending',
                'payment_method' => 'mobile_money'
            ]);

            // Log transaction creation
            AuditLog::log('transaction_created', $transaction, null, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Initialize Flutterwave payment
            $paymentData = [
                'tx_ref' => $transaction->reference,
                'amount' => $plan->amount,
                'currency' => 'UGX',
                'customer' => [
                    'email' => $customer->email ?: 'customer@yourhotspot.com',
                    'phone_number' => $customer->phone,
                    'name' => $customer->name ?: 'WiFi Customer'
                ],
                'customizations' => [
                    'title' => 'YourHotspot WiFi Payment',
                    'description' => $plan->display_name . ' - ' . $plan->duration,
                    'logo' => asset('images/logo.png')
                ],
                'redirect_url' => route('payment.callback'),
                'payment_options' => 'mobilemoneyuganda,card'
            ];

            $paymentLink = $this->flutterwave->initializePayment($paymentData);

            if ($paymentLink) {
                Log::info('Payment initialized successfully', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $plan->amount
                ]);

                return response()->json([
                    'success' => true,
                    'payment_link' => $paymentLink,
                    'reference' => $transaction->reference
                ]);
            } else {
                // Mark transaction as failed
                $transaction->update(['status' => 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to initialize payment. Please try again.'
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Payment processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle Flutterwave callback (UPDATED for reverse flow)
     */
    public function handleCallback(Request $request)
    {
        try {
            $transactionId = $request->get('transaction_id');
            $reference = $request->get('tx_ref');

            if (!$transactionId || !$reference) {
                Log::warning('Invalid callback parameters', [
                    'transaction_id' => $transactionId,
                    'reference' => $reference,
                    'all_params' => $request->all()
                ]);

                return redirect('http://hotspot.local/login?' . http_build_query([
                    'payment_status' => 'error',
                    'message' => 'Invalid payment response received'
                ]));
            }

            // Find transaction
            $transaction = Transaction::where('reference', $reference)->first();
            if (!$transaction) {
                Log::error('Transaction not found for callback', [
                    'reference' => $reference,
                    'transaction_id' => $transactionId
                ]);

                return redirect('http://hotspot.local/login?' . http_build_query([
                    'payment_status' => 'error',
                    'message' => 'Transaction not found'
                ]));
            }

            // Verify payment with Flutterwave
            $verification = $this->flutterwave->verifyPayment($transactionId);

            if ($verification && $verification['status'] === 'successful') {
                // Update transaction
                $transaction->update([
                    'flutterwave_id' => $transactionId,
                    'status' => 'completed',
                    'payment_data' => $verification,
                    'paid_at' => now()
                ]);

                // Update customer stats
                $transaction->customer->updateStats();

                // Generate voucher (SIMPLIFIED - use one method)
                $voucher = $this->generateVoucher($transaction);

                if ($voucher) {
                    // Send SMS with voucher (optional)
                    $this->sendVoucherSMS($voucher);

                    // Log successful payment
                    AuditLog::log(
                        'payment_completed',
                        $transaction,
                        ['status' => 'pending'],
                        ['status' => 'completed', 'voucher_generated' => true, 'voucher_id' => $voucher->id]
                    );

                    // Redirect back to hotspot with success status
                    $returnUrl = $transaction->return_url ?? 'http://hotspot.local/login';

                    return redirect($returnUrl . '?' . http_build_query([
                        'payment_status' => 'success',
                        'voucher' => $voucher->code,
                        'plan' => $voucher->plan_type,
                        'message' => 'Payment successful! Your internet access is being prepared...'
                    ]));
                } else {
                    Log::error('Payment successful but voucher generation failed', [
                        'transaction_id' => $transaction->id
                    ]);

                    $returnUrl = $transaction->return_url ?? 'http://hotspot.local/login';

                    return redirect($returnUrl . '?' . http_build_query([
                        'payment_status' => 'error',
                        'message' => 'Payment successful but voucher generation failed. Please contact support.'
                    ]));
                }
            } else {
                // Payment verification failed
                $transaction->update(['status' => 'failed']);

                Log::warning('Payment verification failed', [
                    'transaction_id' => $transactionId,
                    'reference' => $reference,
                    'verification_result' => $verification
                ]);

                $returnUrl = $transaction->return_url ?? 'http://hotspot.local/login';

                return redirect($returnUrl . '?' . http_build_query([
                    'payment_status' => 'failed',
                    'message' => 'Payment verification failed. Please contact support if money was deducted.'
                ]));
            }
        } catch (Exception $e) {
            Log::error('Callback handling error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect('http://hotspot.local/login?' . http_build_query([
                'payment_status' => 'error',
                'message' => 'An error occurred while processing your payment. Please contact support.'
            ]));
        }
    }

    /**
     * Handle Flutterwave webhook
     */
    public function webhook(Request $request)
    {
        try {
            // Verify webhook signature
            $signature = $request->header('verif-hash');
            $expectedSignature = config('flutterwave.webhook_hash');

            if ($signature !== $expectedSignature) {
                Log::warning('Webhook signature verification failed', [
                    'received_signature' => $signature,
                    'expected_signature' => $expectedSignature
                ]);
                return response('Unauthorized', 401);
            }

            $payload = $request->all();

            Log::info('Webhook received', [
                'event' => $payload['event'] ?? 'unknown',
                'data' => $payload['data'] ?? []
            ]);

            // Process webhook
            $success = $this->flutterwave->handleWebhook($payload);

            if ($success) {
                return response('OK', 200);
            } else {
                return response('Bad Request', 400);
            }
        } catch (Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Payment success page
     */
    public function paymentSuccess(Request $request)
    {
        $voucherCode = $request->get('voucher');

        if (!$voucherCode) {
            return redirect()->route('payment.failed')
                ->with('error', 'No voucher code provided');
        }

        $voucher = Voucher::where('code', $voucherCode)
            ->with(['transaction.customer'])
            ->first();

        if (!$voucher) {
            return redirect()->route('payment.failed')
                ->with('error', 'Voucher not found');
        }

        return view('payment.success', compact('voucher'));
    }

    /**
     * Payment failed page
     */
    public function paymentFailed()
    {
        return view('payment.failed');
    }

    /**
     * Generate voucher for completed transaction (FIXED - single method)
     */
    private function generateVoucher(Transaction $transaction): ?Voucher
    {
        try {
            // Use the Voucher model's createFromTransaction method
            // This handles the proper field mapping for your hp_vouchers table
            $voucher = Voucher::createFromTransaction($transaction);

            Log::info('Voucher generated successfully (reverse flow)', [
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'transaction_id' => $transaction->id,
                'plan_type' => $voucher->plan_type,
                'mikrotik_status' => $voucher->mikrotik_status
            ]);

            return $voucher;

        } catch (Exception $e) {
            Log::error('Voucher generation failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get MikroTik profile for plan type
     */
    private function getMikroTikProfile(string $planType): string
    {
        return match ($planType) {
            'basic' => 'default',
            'premium' => 'default',
            'daily' => 'default',
            default => 'default'
        };
    }

    /**
     * Get session timeout for plan type
     */
    private function getSessionTimeout(string $planType): string
    {
        return match ($planType) {
            'basic' => '1h',
            'premium' => '8h',
            'daily' => '1d',
            default => '1h'
        };
    }

    /**
     * Get plan description for Flutterwave
     */
    private function getPlanDescription(string $planType): string
    {
        return match ($planType) {
            'basic' => 'Quick Browse - 1 Hour WiFi Access',
            'premium' => 'Premium Access - 8 Hours WiFi Access',
            'daily' => 'All Day Pass - 24 Hours WiFi Access',
            default => 'WiFi Internet Access'
        };
    }

    /**
     * Send voucher via SMS
     */
    private function sendVoucherSMS(Voucher $voucher): bool
    {
        try {
            $customer = $voucher->transaction->customer;

            $voucherData = [
                'code' => $voucher->code,
                'duration' => $voucher->plan_details['duration'],
                'speed' => $voucher->plan_details['speed'],
                'network' => 'YourHotspot',
                'expires' => $voucher->expires_at ? $voucher->expires_at->format('M j, Y') : 'Never'
            ];

            $sent = $this->sms->sendVoucherSMS($customer->phone, $voucherData);

            if ($sent) {
                Log::info('Voucher SMS sent successfully', [
                    'voucher_id' => $voucher->id,
                    'phone' => substr($customer->phone, 0, 4) . '***' . substr($customer->phone, -3)
                ]);
            } else {
                Log::warning('Failed to send voucher SMS', [
                    'voucher_id' => $voucher->id,
                    'phone' => substr($customer->phone, 0, 4) . '***' . substr($customer->phone, -3)
                ]);
            }

            return $sent;
        } catch (Exception $e) {
            Log::error('SMS sending error', [
                'voucher_id' => $voucher->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Format phone number for Uganda
     */
    private function formatPhoneNumber(string $phone): ?string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading zeros
        $phone = ltrim($phone, '0');

        // Add Uganda country code if not present
        if (!str_starts_with($phone, '256')) {
            // Check if it starts with common Uganda prefixes
            if (preg_match('/^(70|71|72|73|74|75|76|77|78|79)/', $phone)) {
                $phone = '256' . $phone;
            } else {
                return null; // Invalid format
            }
        }

        // Validate length (Uganda numbers should be 12 digits with country code)
        if (strlen($phone) !== 12) {
            return null;
        }

        return '+' . $phone;
    }

    /**
     * Get payment statistics (for admin use)
     */
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'today' => [
                    'transactions' => Transaction::today()->count(),
                    'revenue' => Transaction::completed()->today()->sum('amount'),
                    'success_rate' => $this->calculateSuccessRate('today'),
                    'vouchers_pending' => Voucher::where('mikrotik_status', 'pending')
                        ->whereDate('created_at', today())->count(),
                    'vouchers_created' => Voucher::where('mikrotik_status', 'created')
                        ->whereDate('created_at', today())->count(),
                ],
                'this_month' => [
                    'transactions' => Transaction::thisMonth()->count(),
                    'revenue' => Transaction::completed()->thisMonth()->sum('amount'),
                    'success_rate' => $this->calculateSuccessRate('month')
                ],
                'payment_methods' => Transaction::completed()
                    ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('payment_method')
                    ->get(),
                'voucher_sync_status' => [
                    'pending' => Voucher::where('mikrotik_status', 'pending')->count(),
                    'created' => Voucher::where('mikrotik_status', 'created')->count(),
                    'failed' => Voucher::where('mikrotik_status', 'failed')->count(),
                ]
            ];

            return response()->json($stats);
        } catch (Exception $e) {
            Log::error('Error getting payment stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Unable to fetch statistics'
            ], 500);
        }
    }

    /**
     * Calculate payment success rate
     */
    private function calculateSuccessRate(string $period): float
    {
        $query = Transaction::query();

        if ($period === 'today') {
            $query->today();
        } elseif ($period === 'month') {
            $query->thisMonth();
        }

        $total = $query->count();
        $successful = $query->where('status', 'completed')->count();

        return $total > 0 ? round(($successful / $total) * 100, 2) : 0;
    }
}
