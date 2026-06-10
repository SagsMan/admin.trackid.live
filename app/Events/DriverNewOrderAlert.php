<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverNewOrderAlert implements ShouldBroadcast
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
    public $accountId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $accountId, array $orderDetails)
    {
        $this->accountId = $accountId;
        $this->orderDetails = $orderDetails;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // Create a private channel based on the driver's account ID
        return new PrivateChannel('driver-order.' . $this->accountId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'new.order';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return $this->orderDetails;
    }
}