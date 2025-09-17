<?php

namespace App\Http\Controllers;

use App\Models\Radius\Check;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RadiusController extends Controller
{
    public function testRadiusConnection()
    {
        try {
            DB::connection('radius')->getPdo();
            return response()->json(['success' => true, 'message' => 'Connected to RADIUS database successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function addUser(Request $request)
    {
        Check::create([
            'username'  => $request->username,
            'attribute' => 'Cleartext-Password',
            'op'        => ':=',
            'value'     => $request->password,
        ]);

        return back()->with('success', 'User added to RADIUS!');
    }

    public function radiusUsers()
    {
    $users = DB::connection('radius')->table('radcheck')->limit(10)->get();
    return response()->json($users);
    }
}
