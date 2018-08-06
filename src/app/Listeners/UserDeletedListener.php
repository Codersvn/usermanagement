<?php

namespace VCComponent\Laravel\User\Listeners;

use VCComponent\Laravel\User\Events\UserDeletedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserDeletedListener
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
     * @param  UserDeletedEvent  $event
     * @return void
     */
    public function handle(UserDeletedEvent $event)
    {
        //
    }
}
