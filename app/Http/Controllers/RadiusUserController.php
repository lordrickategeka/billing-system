<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Radius\Check;
use App\Models\Radius\Reply;
use Illuminate\Http\Request;

class RadiusUserController extends Controller
{
    public function index()
    {
        $users = Subscription::with(['customer', 'product'])->get();
        return view('radius.index', compact('users'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products  = Product::all();
        return view('radius.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $subscription = Subscription::create([
            'customer_id' => $request->customer_id,
            'product_id'  => $request->product_id,
            'status'      => 'active',
            'start_at'    => now(),
            'end_at'      => now()->addMonth(),
        ]);

        $username = strtolower($subscription->customer->name) . "_" . $subscription->id;
        $password = $request->password;

        // radcheck entry
        Check::create([
            'username'  => $username,
            'attribute' => 'Cleartext-Password',
            'op'        => ':=',
            'value'     => $password,
        ]);

        // radreply entries for speed limits
        $product = $subscription->product;

        Reply::create([
            'username'  => $username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op'        => '=',
            'value'     => "{$product->speed_up}k/{$product->speed_down}k",
        ]);

        return redirect()->route('radius.index')->with('success', 'User provisioned into RADIUS!');
    }

    public function edit($id)
    {
        $subscription = Subscription::findOrFail($id);
        return view('radius.edit', compact('subscription'));
    }

    public function update(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        // Update subscription
        $subscription->update([
            'status' => $request->status,
            'end_at' => $request->end_at,
        ]);

        // Update RADIUS password
        $username = strtolower($subscription->customer->name) . "_" . $subscription->id;

        Check::where('username', $username)->update([
            'value' => $request->password,
        ]);

        return redirect()->route('radius.index')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        $username     = strtolower($subscription->customer->name) . "_" . $subscription->id;

        // Delete from RADIUS
        Check::where('username', $username)->delete();
        Reply::where('username', $username)->delete();

        // Delete subscription
        $subscription->delete();

        return redirect()->route('radius.index')->with('success', 'User deleted successfully!');
    }
}
