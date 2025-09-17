<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Product;
use App\Models\RadiusIdentity;
use App\Services\RadiusSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BroadbandController extends Controller
{
    protected $radiusSync;

    public function __construct(RadiusSyncService $radiusSync)
    {
        $this->radiusSync = $radiusSync;
    }

    /**
     * Create new broadband subscriber
     */
    public function createSubscriber(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'service_type' => 'required|in:pppoe,ipoe',
            'circuit_id' => 'nullable|string',
            'ont_serial' => 'nullable|string',
            'static_ip' => 'nullable|ip',
            'installation_address' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            // Validate product is for broadband
            $product = Product::findOrFail($request->product_id);
            if ($product->service_type !== 'broadband') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not for broadband service'
                ], 422);
            }

            // Create service
            $service = Service::create([
                'tenant_id' => $request->tenant_id,
                'customer_id' => $request->customer_id,
                'service_number' => 'BB-' . strtoupper(Str::random(10)),
                'service_type' => 'broadband',
                'installation_address' => $request->installation_address,
                'circuit_id' => $request->circuit_id,
                'ont_serial' => $request->ont_serial,
                'static_ip' => $request->static_ip,
                'status' => 'pending'
            ]);

            // Create subscription
            $subscription = Subscription::create([
                'tenant_id' => $request->tenant_id,
                'service_id' => $service->id,
                'product_id' => $product->id,
                'start_at' => now(),
                'end_at' => now()->addDays(30), // Default monthly
                'auto_renew' => true,
                'status' => 'pending'
            ]);

            // Generate credentials
            $username = $this->generatePppoeUsername($service, $request->service_type);
            $password = Str::random(12);

            // Create RADIUS identity
            $identity = RadiusIdentity::create([
                'tenant_id' => $request->tenant_id,
                'service_id' => $service->id,
                'subscription_id' => $subscription->id,
                'username' => $username,
                'password' => $password,
                'circuit_id' => $request->circuit_id,
                'ont_serial' => $request->ont_serial,
                'static_ip' => $request->static_ip,
                'auth_type' => $request->service_type,
                'status' => 'active'
            ]);

            // Activate service and subscription
            $service->update([
                'status' => 'active',
                'activated_at' => now()
            ]);

            $subscription->update([
                'status' => 'active',
                'activated_at' => now()
            ]);

            // Sync to RADIUS
            $this->radiusSync->syncIdentity($identity);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'service_id' => $service->id,
                    'service_number' => $service->service_number,
                    'username' => $username,
                    'password' => $password,
                    'product' => $product->name,
                    'speed' => $product->getFormattedSpeedAttribute(),
                    'status' => $service->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscriber: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update subscriber plan
     */
    public function updatePlan(Request $request, $serviceId)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'effective_date' => 'nullable|date'
        ]);

        try {
            DB::beginTransaction();

            $service = Service::with(['activeSubscription', 'radiusIdentities'])
                ->findOrFail($serviceId);

            $newProduct = Product::findOrFail($request->product_id);

            if ($newProduct->service_type !== 'broadband') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not for broadband service'
                ], 422);
            }

            $effectiveDate = $request->effective_date ?
                \Carbon\Carbon::parse($request->effective_date) : now();

            // End current subscription
            $currentSubscription = $service->activeSubscription;
            if ($currentSubscription) {
                $currentSubscription->update([
                    'end_at' => $effectiveDate,
                    'status' => 'cancelled'
                ]);
            }

            // Create new subscription
            $newSubscription = Subscription::create([
                'tenant_id' => $service->tenant_id,
                'service_id' => $service->id,
                'product_id' => $newProduct->id,
                'start_at' => $effectiveDate,
                'end_at' => $effectiveDate->copy()->addMonth(),
                'auto_renew' => true,
                'status' => 'active',
                'activated_at' => now()
            ]);

            // Update RADIUS identity with new subscription
            $identity = $service->radiusIdentities()->where('status', 'active')->first();
            if ($identity) {
                $identity->update([
                    'subscription_id' => $newSubscription->id
                ]);

                // Sync to RADIUS for immediate speed change
                $this->radiusSync->syncIdentity($identity);

                // Send CoA to update active sessions
                $sessions = $this->radiusSync->getActiveSessions($identity->username);
                foreach ($sessions as $session) {
                    $this->radiusSync->sendCoA($identity->username, $session->nasipaddress, [
                        'Mikrotik-Rate-Limit' => ($newProduct->speed_up_kbps ?: 0) . 'k/' . ($newProduct->speed_down_kbps ?: 0) . 'k'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully',
                'data' => [
                    'new_product' => $newProduct->name,
                    'new_speed' => $newProduct->getFormattedSpeedAttribute(),
                    'effective_date' => $effectiveDate->toDateString()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend/Unsuspend subscriber
     */
    public function updateStatus(Request $request, $serviceId)
    {
        $request->validate([
            'status' => 'required|in:active,suspended',
            'reason' => 'nullable|string'
        ]);

        try {
            $service = Service::with(['radiusIdentities'])->findOrFail($serviceId);

            $service->update([
                'status' => $request->status,
                'suspended_at' => $request->status === 'suspended' ? now() : null
            ]);

            // Update RADIUS identity status
            foreach ($service->radiusIdentities as $identity) {
                $newStatus = $request->status === 'suspended' ? 'suspended' : 'active';
                $identity->update(['status' => $newStatus]);

                // Sync to RADIUS
                $this->radiusSync->syncIdentity($identity);

                if ($request->status === 'suspended') {
                    // Disconnect active sessions
                    $sessions = $this->radiusSync->getActiveSessions($identity->username);
                    foreach ($sessions as $session) {
                        $this->radiusSync->disconnectUser($identity->username, $session->nasipaddress);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->status) . ' subscriber successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscriber usage
     */
    public function getUsage($serviceId, Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:90'
        ]);

        try {
            $service = Service::with(['radiusIdentities'])->findOrFail($serviceId);
            $days = $request->input('days', 30);

            $usage = [];
            foreach ($service->radiusIdentities as $identity) {
                $accountingData = $this->radiusSync->getAccountingData($identity->username, $days);

                $usage[] = [
                    'username' => $identity->username,
                    'auth_type' => $identity->auth_type,
                    'total_download_mb' => $this->calculateDownloadUsage($accountingData),
                    'total_upload_mb' => $this->calculateUploadUsage($accountingData),
                    'total_session_time' => $this->calculateSessionTime($accountingData),
                    'session_count' => count($accountingData)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'service_id' => $service->id,
                    'service_number' => $service->service_number,
                    'period_days' => $days,
                    'usage_details' => $usage
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all subscribers with filtering
     */
    public function listSubscribers(Request $request)
    {
        $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'status' => 'nullable|in:pending,active,suspended,terminated',
            'service_type' => 'nullable|in:pppoe,ipoe',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        try {
            $query = Service::with(['customer', 'activeSubscription.product', 'radiusIdentities'])
                ->where('service_type', 'broadband');

            if ($request->tenant_id) {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->service_type) {
                $query->whereHas('radiusIdentities', function($q) use ($request) {
                    $q->where('auth_type', $request->service_type);
                });
            }

            $subscribers = $query->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $subscribers->items(),
                'pagination' => [
                    'current_page' => $subscribers->currentPage(),
                    'last_page' => $subscribers->lastPage(),
                    'per_page' => $subscribers->perPage(),
                    'total' => $subscribers->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list subscribers: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generatePppoeUsername(Service $service, string $type): string
    {
        $prefix = $type === 'pppoe' ? 'pppoe' : 'ipoe';
        return $prefix . '_' . $service->service_number;
    }

    private function calculateDownloadUsage(array $accountingData): float
    {
        $totalBytes = 0;
        foreach ($accountingData as $record) {
            $totalBytes += $record->acctoutputoctets ?? 0;
        }
        return round($totalBytes / (1024 * 1024), 2); // Convert to MB
    }

    private function calculateUploadUsage(array $accountingData): float
    {
        $totalBytes = 0;
        foreach ($accountingData as $record) {
            $totalBytes += $record->acctinputoctets ?? 0;
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
