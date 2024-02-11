<?php

namespace App\Events;


use App\Models\v2\RequestFund;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateRequestOfferEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $requestFund;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RequestFund $requestFund)
    {
        $this->requestFund = $requestFund;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
