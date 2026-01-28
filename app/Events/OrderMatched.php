<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $buyOrder;
    public $sellOrder;
    public $amount;
    public $price;
    public $fee;

    public function __construct(Order $buyOrder, Order $sellOrder, $amount, $price, $fee)
    {
        $this->buyOrder = $buyOrder;
        $this->sellOrder = $sellOrder;
        $this->amount = $amount;
        $this->price = $price;
        $this->fee = $fee;
    }

    /**
     * Which channel should this event broadcast on?
     */
    public function broadcastOn()
    {
            return new \Illuminate\Broadcasting\Channel('test-channel');

        // broadcast to both users: buyer and seller
        return [
            new PrivateChannel('user.' . $this->buyOrder->user_id),
            new PrivateChannel('user.' . $this->sellOrder->user_id),
        ];
    }

    /**
     * What data should be sent to the channel
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
