<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    /**
     * Generate vouchers in bulk
     */
    public function generateBulk(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10000',
            'value' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:now',
            'prefix' => 'nullable|string|max:5'
        ]);

        try {
            $product = Product::findOrFail($request->product_id);

            if ($product->service_type !== 'hotspot') {
                return response()->json([
                    'success' => false,
                    'message' => 'Product must be for hotspot service'
                ], 422);
            }

            $batchId = 'BATCH_' . strtoupper(Str::random(8));
            $vouchers = [];
            $codes = [];

            for ($i = 0; $i < $request->quantity; $i++) {
                do {
                    $code = ($request->prefix ?: 'HS') . strtoupper(Str::random(8));
                } while (in_array($code, $codes) || Voucher::where('code', $code)->exists());

                $codes[] = $code;
                $vouchers[] = [
                    'tenant_id' => $request->tenant_id,
                    'product_id' => $request->product_id,
                    'code' => $code,
                    'batch_id' => $batchId,
                    'value' => $request->value ?: $product->price,
                    'max_uses' => $request->max_uses ?: 1,
                    'expires_at' => $request->expires_at,
                    'state' => 'unused',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            Voucher::insert($vouchers);

            return response()->json([
                'success' => true,
                'message' => "Generated {$request->quantity} vouchers",
                'data' => [
                    'batch_id' => $batchId,
                    'quantity' => $request->quantity,
                    'product' => $product->name,
                    'value' => $request->value ?: $product->price
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate vouchers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List vouchers with filtering
     */
    public function index(Request $request)
    {
        $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'batch_id' => 'nullable|string',
            'state' => 'nullable|in:unused,active,expired,depleted',
            'product_id' => 'nullable|exists:products,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        try {
            $query = Voucher::with(['product', 'tenant']);

            if ($request->tenant_id) {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->batch_id) {
                $query->where('batch_id', $request->batch_id);
            }

            if ($request->state) {
                $query->where('state', $request->state);
            }

            if ($request->product_id) {
                $query->where('product_id', $request->product_id);
            }

            $vouchers = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $vouchers->items(),
                'pagination' => [
                    'current_page' => $vouchers->currentPage(),
                    'last_page' => $vouchers->lastPage(),
                    'per_page' => $vouchers->perPage(),
                    'total' => $vouchers->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list vouchers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get voucher batch details
     */
    public function getBatch($batchId)
    {
        try {
            $vouchers = Voucher::where('batch_id', $batchId)
                ->with(['product'])
                ->get();

            if ($vouchers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found'
                ], 404);
            }

            $stats = [
                'total' => $vouchers->count(),
                'unused' => $vouchers->where('state', 'unused')->count(),
                'active' => $vouchers->where('state', 'active')->count(),
                'expired' => $vouchers->where('state', 'expired')->count(),
                'depleted' => $vouchers->where('state', 'depleted')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'batch_id' => $batchId,
                    'product' => $vouchers->first()->product,
                    'statistics' => $stats,
                    'vouchers' => $vouchers
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get batch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export vouchers as CSV
     */
    public function exportBatch($batchId)
    {
        try {
            $vouchers = Voucher::where('batch_id', $batchId)
                ->with(['product'])
                ->get();

            if ($vouchers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found'
                ], 404);
            }

            $csv = "Code,Product,Value,Max Uses,State,Created At,Expires At\n";

            foreach ($vouchers as $voucher) {
                $csv .= sprintf(
                    "%s,%s,%s,%d,%s,%s,%s\n",
                    $voucher->code,
                    $voucher->product->name,
                    $voucher->value,
                    $voucher->max_uses,
                    $voucher->state,
                    $voucher->created_at->format('Y-m-d H:i:s'),
                    $voucher->expires_at ? $voucher->expires_at->format('Y-m-d H:i:s') : ''
                );
            }

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="vouchers_' . $batchId . '.csv"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export vouchers: ' . $e->getMessage()
            ], 500);
        }
    }
}
