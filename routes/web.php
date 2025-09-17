<?php

use App\Http\Controllers\RadiusController;
use App\Http\Controllers\RadiusUserController;
use App\Livewire\CustomersComponent;
use App\Livewire\ProductsComponent;
use App\Livewire\SubscriptionsComponent;
use App\Livewire\TenantsComponent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/radius/add-user', [RadiusController::class, 'addUser']);
Route::get('/all-user', [RadiusController::class, 'radiusUsers']);
Route::get('/radius/test-connection', [RadiusController::class, 'testRadiusConnection']);

Route::resource('radius', RadiusUserController::class);

Route::get('/tenants', TenantsComponent::class)->name('tenants');
Route::get('/customers', CustomersComponent::class)->name('customers');
Route::get('/products', ProductsComponent::class)->name('products');
Route::get('/subscriptions', SubscriptionsComponent::class)->name('subscriptions');
