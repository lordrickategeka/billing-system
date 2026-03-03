<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\RadiusIdentity;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\RadiusSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotspotController extends Controller
{
    protected $radiusSync;

    public function __construct(RadiusSyncService $radiusSync)
    {
        $this->radiusSync = $radiusSync;
    }

    /**
     * Validate voucher and create hotspot session
     */
    public function validateVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'mac_address' => 'nullable|string',
            'nas_ip' => 'required|ip',
            'user_ip' => 'required|ip',
        ]);

        try {
            DB::beginTransaction();

            // Find and validate voucher
            $voucher = Voucher::where('code', $request->voucher_code)
                ->with(['product', 'tenant'])
                ->first();

            if (!$voucher || !$voucher->canBeUsed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired voucher code'
                ], 422);
            }

            // Create or find customer for hotspot
            $customer = $voucher->tenant->customers()->firstOrCreate(
                ['email' => 'hotspot@' . Str::random(8) . '.local'],
                [
                    'first_name' => 'Hotspot',
                    'last_name' => 'User',
                    'customer_number' => 'HS-' . strtoupper(Str::random(8))
                ]
            );

            // Create service
            $service = Service::create([
                'tenant_id' => $voucher->tenant_id,
                'customer_id' => $customer->id,
                'service_number' => 'SV-' . strtoupper(Str::random(10)),
                'service_type' => 'hotspot',
                'status' => 'active',
                'activated_at' => now()
            ]);

            // Create subscription
            $subscription = Subscription::create([
                'tenant_id' => $voucher->tenant_id,
                'service_id' => $service->id,
                'product_id' => $voucher->product_id,
                'start_at' => now(),
                'end_at' => $voucher->product->term_days ?
                    now()->addDays($voucher->product->term_days) : null,
                'expires_at' => $voucher->product->term_days ?
                    now()->addDays($voucher->product->term_days) : null,
                'data_remaining' => $voucher->product->quota_mb ? $voucher->product->quota_mb * 1024 * 1024 : null,
                'status' => 'active',
                'activated_at' => now()
            ]);

            // Generate unique username for this session
            $username = 'voucher_' . $voucher->code . '_' . time();
            $password = Str::random(12);

            // Create RADIUS identity
            $identity = RadiusIdentity::create([
                'tenant_id' => $voucher->tenant_id,
                'service_id' => $service->id,
                'subscription_id' => $subscription->id,
                'voucher_id' => $voucher->id,
                'username' => $username,
                'password' => $password,
                'mac_address' => $request->mac_address,
                'mac_binding' => !empty($request->mac_address),
                'auth_type' => 'voucher',
                'status' => 'active'
            ]);

            // Update voucher usage
            $voucher->update([
                'used_count' => $voucher->used_count + 1,
                'state' => $voucher->used_count + 1 >= $voucher->max_uses ? 'depleted' : 'active',
                'first_used_at' => $voucher->first_used_at ?: now(),
                'last_used_at' => now()
            ]);

            // Sync to RADIUS
            $this->radiusSync->syncIdentity($identity);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'username' => $username,
                    'password' => $password,
                    'session_timeout' => $voucher->product->session_timeout,
                    'data_limit' => $voucher->product->quota_mb ? $voucher->product->quota_mb * 1024 * 1024 : null,
                    'speed_limit' => $voucher->product->getFormattedSpeedAttribute(),
                    'expires_at' => $subscription->end_at,
                    'service_id' => $service->id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hotspot session status
     */
    public function getSessionStatus(Request $request)
    {
        $request->validate([
            'username' => 'required|string'
        ]);

        $identity = RadiusIdentity::where('username', $request->username)
            ->with(['service', 'subscription.product', 'voucher'])
            ->first();

        if (!$identity) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        }

        // Get accounting data
        $sessions = $this->radiusSync->getActiveSessions($identity->username);
        $accountingData = $this->radiusSync->getAccountingData($identity->username, 1);

        return response()->json([
            'success' => true,
            'data' => [
                'username' => $identity->username,
                'status' => $identity->status,
                'service_type' => $identity->service->service_type,
                'product' => $identity->subscription->product->name,
                'active_sessions' => count($sessions),
                'total_usage_mb' => $this->calculateTotalUsage($accountingData),
                'session_time' => $this->calculateSessionTime($accountingData),
                'expires_at' => $identity->subscription->end_at
            ]
        ]);
    }

    /**
     * Terminate hotspot session
     */
    public function terminateSession(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'reason' => 'nullable|string'
        ]);

        $identity = RadiusIdentity::where('username', $request->username)->first();

        if (!$identity) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        }

        try {
            // Update identity status
            $identity->update(['status' => 'expired']);

            // Get active sessions to disconnect
            $sessions = $this->radiusSync->getActiveSessions($identity->username);

            foreach ($sessions as $session) {
                $this->radiusSync->disconnectUser($identity->username, $session->nasipaddress);
            }

            // Sync to RADIUS (will remove from radcheck/radreply)
            $this->radiusSync->syncIdentity($identity);

            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateTotalUsage(array $accountingData): float
    {
        $totalBytes = 0;
        foreach ($accountingData as $record) {
            $totalBytes += ($record->acctinputoctets ?? 0) + ($record->acctoutputoctets ?? 0);
        }
        return round($totalBytes / (1024 * 1024), 2); // Convert to MB
    }

    private function calculateSessionTime(array $accountingData): int
    {
        $totalTime = 0;
        foreach ($accountingData as $record) {
            $totalTime += $record->acctsessiontime ?? 0;
        }
        return $totalTime;
    }
}
