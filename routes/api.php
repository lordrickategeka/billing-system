<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HotspotController;
use App\Http\Controllers\Api\BroadbandController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes (for captive portal, etc.)
Route::prefix('hotspot')->group(function () {
    Route::post('/validate-voucher', [HotspotController::class, 'validateVoucher']);
    Route::post('/session-status', [HotspotController::class, 'getSessionStatus']);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Hotspot Management
    Route::prefix('hotspot')->group(function () {
        Route::post('/terminate-session', [HotspotController::class, 'terminateSession']);
    });

    // Broadband Management
    Route::prefix('broadband')->group(function () {
        Route::get('/subscribers', [BroadbandController::class, 'listSubscribers']);
        Route::post('/subscribers', [BroadbandController::class, 'createSubscriber']);
        Route::put('/subscribers/{service}/plan', [BroadbandController::class, 'updatePlan']);
        Route::put('/subscribers/{service}/status', [BroadbandController::class, 'updateStatus']);
        Route::get('/subscribers/{service}/usage', [BroadbandController::class, 'getUsage']);
    });

    // Voucher Management
    Route::prefix('vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index']);
        Route::post('/generate', [VoucherController::class, 'generateBulk']);
        Route::get('/batch/{batchId}', [VoucherController::class, 'getBatch']);
        Route::get('/batch/{batchId}/export', [VoucherController::class, 'exportBatch']);
    });

    // RADIUS Testing
    Route::get('/radius/test', function () {
        try {
            $connection = DB::connection('radius');
            $connection->getPdo();

            // Test basic query
            $nasCount = $connection->table('nas')->count();
            $userCount = $connection->table('radcheck')->count();

            return response()->json([
                'success' => true,
                'message' => 'RADIUS database connected successfully',
                'data' => [
                    'nas_count' => $nasCount,
                    'users_count' => $userCount,
                    'connection_name' => 'radius'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'RADIUS connection failed: ' . $e->getMessage()
            ], 500);
        }
    });
});

