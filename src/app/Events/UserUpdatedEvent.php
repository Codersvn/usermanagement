<?php

namespace VCComponent\Laravel\User\Events;

use Illuminate\Queue\SerializesModels;
use Vicoders\ActivityLog\Contracts\ActivityLogable;
use Vicoders\ActivityLog\Traits\ActivityLogTrait;

class UserUpdatedEvent implements ActivityLogable
{
    use SerializesModels, ActivityLogTrait;

    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getDescription()
    {
        return "Người dùng cập nhật tài khoản: {$this->user->email}";
    }
}
