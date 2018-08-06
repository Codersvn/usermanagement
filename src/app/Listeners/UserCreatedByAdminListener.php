<?php

namespace VCComponent\Laravel\User\Listeners;

use VCComponent\Laravel\User\Events\UserCreatedByAdminEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCreatedByAdminListener
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
     * @param  UserCreatedByAdminEvent  $event
     * @return void
     */
    public function handle(UserCreatedByAdminEvent $event)
    {
        //
    }
}
