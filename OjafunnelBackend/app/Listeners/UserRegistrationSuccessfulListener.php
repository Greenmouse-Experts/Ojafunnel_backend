<?php

namespace App\Listeners;

use App\Models\UserReferral;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRegistrationSuccessfulListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {   
        $userCreateRequestData = $event->userCreateRequestData;
        $userId = $event->user_id;
        $user = $this->user::find($userId);

        //Send email verification mail
        $user->sendApiEmailVerificationTokenNotification();    
    }
}
