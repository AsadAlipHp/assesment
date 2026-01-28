<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Jobs\MatchOrderJob;

class OrderController extends Controller
{
    // List all orders (for testing)
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->get();

        return response()->json($orders);
    }

    // Place a limit order (basic, no matching yet)
    public function store(Request $request)
{
    $request->validate([
        'symbol' => 'required|string',
        'side' => 'required|in:buy,sell',
        'price' => 'required|numeric|min:0.00000001',
        'amount' => 'required|numeric|min:0.00000001',
    ]);

    $user = $request->user();
    $symbol = strtoupper($request->symbol);
    $side = $request->side;
    $price = $request->price;
    $amount = $request->amount;

    $order = DB::transaction(function () use ($user, $symbol, $side, $price, $amount) {

        if ($side === 'buy') {
            $totalCost = $price * $amount;

            if ($user->balance < $totalCost) {
                throw ValidationException::withMessages(['balance' => 'Insufficient USD balance']);
            }

            // Lock USD
            $user->balance -= $totalCost;
            $user->save();

        } elseif ($side === 'sell') {
            $asset = Asset::firstOrCreate(
                ['user_id' => $user->id, 'symbol' => $symbol],
                ['amount' => 0, 'locked_amount' => 0]
            );

            if ($asset->amount < $amount) {
                throw ValidationException::withMessages(['asset' => 'Insufficient asset balance']);
            }

            // Lock asset
            $asset->amount -= $amount;
            $asset->locked_amount += $amount;
            $asset->save();
        }

        return Order::create([
            'user_id' => $user->id,
            'symbol' => $symbol,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'status' => 1, // open
        ]);
    });
MatchOrderJob::dispatch($order);

return response()->json([
    'success' => true,
    'message' => 'Order created successfully',
    'data' => $order,
], 201);
    return response()->json($order);
}

    // Cancel order (will implement properly later)
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $order->status = 3; // cancelled
        $order->save();

        return response()->json(['message' => 'Order cancelled', 'order' => $order]);
    }
}
