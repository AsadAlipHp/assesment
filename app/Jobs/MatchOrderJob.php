<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\OrderMatched;

class MatchOrderJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        DB::transaction(function () {
            $order = Order::lockForUpdate()->find($this->order->id);

            if (!$order || $order->status != 1) {
                // already filled/cancelled
                return;
            }

            if ($order->side === 'buy') {
                $this->matchBuyOrder($order);
            } else {
                $this->matchSellOrder($order);
            }
        });
    }

    protected function matchBuyOrder(Order $buyOrder)
    {
        $sellOrder = Order::where('symbol', $buyOrder->symbol)
            ->where('side', 'sell')
            ->where('status', 1)
            ->where('price', '<=', $buyOrder->price)
            ->lockForUpdate()
            ->orderBy('created_at')
            ->first();

        if (!$sellOrder) return;

        $this->executeTrade($buyOrder, $sellOrder);
    }

    protected function matchSellOrder(Order $sellOrder)
    {
        $buyOrder = Order::where('symbol', $sellOrder->symbol)
            ->where('side', 'buy')
            ->where('status', 1)
            ->where('price', '>=', $sellOrder->price)
            ->lockForUpdate()
            ->orderBy('created_at')
            ->first();

        if (!$buyOrder) return;

        $this->executeTrade($buyOrder, $sellOrder);
    }

    protected function executeTrade(Order $buyOrder, Order $sellOrder)
    {
        $buyer = User::lockForUpdate()->find($buyOrder->user_id);
        $seller = User::lockForUpdate()->find($sellOrder->user_id);

        $symbol = $buyOrder->symbol;
        $amount = $buyOrder->amount; // Full match only
        $price = $sellOrder->price; // Use seller price
        $usdValue = $amount * $price;

        // Commission = 1.5%
        $commissionRate = 0.015;
        $commission = $usdValue * $commissionRate;

        // ====== Update buyer ======
        // Buyer already locked USD when placing order
        $buyer->balance += ($buyOrder->side === 'buy' ? ($amount * $buyOrder->price - $usdValue) : 0); // refund difference if buy price > sell price
        $buyer->balance -= $commission; // deduct commission from buyer USD
        $buyer->save();

        // Add asset to buyer
        $buyerAsset = Asset::firstOrCreate(
            ['user_id' => $buyer->id, 'symbol' => $symbol],
            ['amount' => 0, 'locked_amount' => 0]
        );
        $buyerAsset->amount += $amount;
        $buyerAsset->save();

        // ====== Update seller ======
        $sellerAsset = Asset::lockForUpdate()->where('user_id', $seller->id)->where('symbol', $symbol)->first();
        if (!$sellerAsset) {
            // Should never happen
            Log::error("Seller asset not found for trade: Order {$sellOrder->id}");
            return;
        }
        $sellerAsset->locked_amount -= $amount; // release locked asset
        $sellerAsset->save();

        // Seller receives USD minus commission (we already deducted from buyer, so seller receives full USD)
        $seller->balance += $usdValue;
        $seller->save();

        // ====== Update orders ======
        $buyOrder->status = 2; // filled
        $buyOrder->save();

        $sellOrder->status = 2; // filled
        $sellOrder->save();

        // ====== Broadcast event ======
        event(new OrderMatched($buyOrder, $sellOrder, $amount, $price, $commission));
    }
}
