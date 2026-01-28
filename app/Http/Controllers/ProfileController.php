<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $assets = DB::table('assets')
            ->where('user_id', $user->id)
            ->pluck('amount', 'symbol');

        return response()->json([
            'user' => $user,
            'balance' => $user->balance,
            'assets' => $assets
        ]);
    }
}
