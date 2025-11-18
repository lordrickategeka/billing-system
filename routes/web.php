<?php

use App\Http\Controllers\RadiusController;
use App\Http\Controllers\RadiusUserController;
use App\Livewire\CustomersComponent;
use App\Livewire\ProductsComponent;
use App\Livewire\SubscriptionsComponent;
use App\Livewire\DashboardComponent;
use App\Livewire\ISP\BillingManager;
use App\Livewire\ISP\CustomerManager;
use App\Livewire\ISP\NetworkManager;
use App\Livewire\ISP\ServiceManager;
use App\Livewire\RadiusComponent;
use App\Livewire\ReportsManagerComponent;
use App\Livewire\SettingsComponent;
use App\Livewire\TenantRegistrationComponent;
use App\Livewire\ProfileSetupComponent;
use App\Livewire\VoucherManagerComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

// Public routes (no middleware)
Route::get('/', function () {
    return view('tenant-registration');
})->name('tenants.create');
Route::get('/home', function () {
    return view('tenant-registration');
})->name('home');
Route::get('/register', function () {
    return view('tenant-registration');
})->name('register');

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        // Check if user has completed profile setup
        $user = Auth::user();
        if ($user->tenant && isset($user->tenant->settings['profile_completed']) && $user->tenant->settings['profile_completed']) {
            return redirect()->intended('dashboard');
        }

        return redirect()->route('profile.setup');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->name('login.store')->middleware('guest');

Route::post('/logout', function (Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');



// Authentication required routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->name('admin.')
        // ->middleware('can:manage-roles')
        ->group(function () {
            Route::get('/permissions', App\Livewire\Admin\PermissionManager::class)->name('permissions.index');

            Route::get('/roles', App\Livewire\Admin\RoleManager::class)->name('roles.index');

            Route::get('/role-types', App\Livewire\Admin\RoleTypeManager::class)->name('role-types.index');

            Route::get('/users', App\Livewire\Admin\UserManager::class)->name('users.index');
        });

    // Profile setup routes (no profile completion middleware)
    Route::get('/profile/setup', ProfileSetupComponent::class)->name('profile.setup');
    Route::post('/profile/skip', function () {
        $user = Auth::user();
        if ($user->tenant) {
            $settings = array_merge($user->tenant->settings ?? [], [
                'profile_completed' => true,
                'setup_required' => false,
                'setup_skipped' => true,
            ]);

            $user->tenant->update([
                'settings' => $settings,
                'status' => 'active',
            ]);
        }

        return redirect()->route('dashboard')->with('message', 'Profile setup skipped. You can complete it later from settings.');
    })->name('profile.skip');

    // Main application routes (with profile completion middleware)
    Route::middleware(['profile.complete'])->group(function () {
        Route::get('/dashboard', DashboardComponent::class)->name('dashboard');

        Route::post('/radius/add-user', [RadiusController::class, 'addUser']);
        Route::get('/all-user', [RadiusController::class, 'radiusUsers']);
        Route::get('/radius/test-connection', [RadiusController::class, 'testRadiusConnection']);

        Route::resource('radius', RadiusUserController::class);
        Route::get('/manage-radius', RadiusComponent::class)->name('manage.radius');

        Route::get('/customers', CustomersComponent::class)->name('customers');
        Route::get('/products', ProductsComponent::class)->name('products');
        Route::get('/subscriptions', SubscriptionsComponent::class)->name('subscriptions');

        // ISP Specific Routes
        Route::prefix('isp')->name('isp.')->group(function () {
            Route::get('/customers', CustomerManager::class)->name('customers');
            Route::get('/services', ServiceManager::class)->name('services');
            Route::get('/network', NetworkManager::class)->name('network');
            Route::get('/billing', BillingManager::class)->name('billing');
        });

        // Hotspot & General Routes
        Route::get('/vouchers', VoucherManagerComponent::class)->name('vouchers');
        Route::get('/reports', ReportsManagerComponent::class)->name('reports');
        Route::get('/settings', SettingsComponent::class)->name('settings');
        Route::get('/radius-server', RadiusComponent::class)->name('radius-server');

        // API Test Routes
        Route::get('/api-test', function () {
            return view('api-test');
        })->name('api.test');

        // Additional utility routes
        Route::get('/radius-test', function () {
            try {
                $connection = DB::connection('radius');
                $connection->getPdo();

                $stats = [
                    'nas_count' => $connection->table('nas')->count(),
                    'users_count' => $connection->table('radcheck')->count(),
                    'active_sessions' => $connection->table('radacct')->whereNull('acctstoptime')->count(),
                    'total_accounting_records' => $connection->table('radacct')->count()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'RADIUS database connected successfully',
                    'stats' => $stats
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'RADIUS connection failed: ' . $e->getMessage()
                ], 500);
            }
        })->name('radius.test');
    });
});
