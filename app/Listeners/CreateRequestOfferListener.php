<?php

namespace App\Listeners;

use App\Events\CreateRequestOfferEvent;
use App\Jobs\CreateRequestOffer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateRequestOfferListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CreateRequestOfferEvent  $event
     * @return void
     */
    public function handle(CreateRequestOfferEvent $event)
    {
        if(!in_array($event->requestFund->status,['accepted_customer','rejected_customer'])) {
            CreateRequestOffer::dispatch($event->requestFund);
        }
    }
}
