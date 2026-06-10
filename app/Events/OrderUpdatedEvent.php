<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    /**
     * Create a new event instance.
     */
    public function __construct(Order $mOrder)
    {
        $this->order = $mOrder;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.updated.' . $this->order->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderUpdated';
    }


    public function broadcastWith(): array
    {
        return [
            "id" => $this->order->id,
            "code" => $this->order->code,
            "status" => $this->order->status,
            "payment_status" => $this->order->payment_status,
            "driver_id" => $this->order->driver_id,
        ];
    }
}
