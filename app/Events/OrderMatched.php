<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $buyOrder;
    public Order $sellOrder;
    public float $amount;
    public float $price;
    public float $fee;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $buyOrder, Order $sellOrder, float $amount, float $price, float $fee)
    {
        $this->buyOrder = $buyOrder;
        $this->sellOrder = $sellOrder;
        $this->amount = $amount;
        $this->price = $price;
        $this->fee = $fee;
    }

    /**
     * The channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        // Private channels for buyer and seller
        return [
            new PrivateChannel('user.' . $this->buyOrder->user_id),
            new PrivateChannel('user.' . $this->sellOrder->user_id),
        ];
    }

    /**
     * The data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'buy_order_id' => $this->buyOrder->id,
            'sell_order_id' => $this->sellOrder->id,
            'amount' => $this->amount,
            'price' => $this->price,
            'fee' => $this->fee,
        ];
    }
}
