<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebsocketDriverNewOrderEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Order details
     * @var array
     */
    public $orderDetails;

    /**
     * Driver's account ID
     * @var int
     */
    public $driverId;


    /**
     * Create a new event instance.
     *
     * @param $driverId
     */
    public function __construct($driverId, array $orderDetails)
    {
        $this->driverId = $driverId;
        $this->orderDetails = $orderDetails;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver.new-order.' . $this->driverId),
        ];
    }


    public function broadcastAs()
    {
        return "WebsocketDriverNewOrderEvent";
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return $this->orderDetails;
    }
}