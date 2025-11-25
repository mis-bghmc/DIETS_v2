<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class FoodRequests implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Message to broadcast.
     */
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('food-requests-channel');
    }

    /**
     * Set the Name for Broadcast
     */
    public function broadcastAs(): string{
        return 'food-requests';
    }
}
