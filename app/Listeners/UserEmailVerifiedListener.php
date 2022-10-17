<?php

namespace App\Listeners;

use Exception;
use App\Models\User;
use App\Models\Tenant;
use App\Classes\UserClass;
use App\Models\UserDetail;
use App\Events\UserEmailVerified;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class UserEmailVerifiedListener implements ShouldQueue
{
    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;
    /**
     * @var User
     */
    private $user;

    /**
     * @var tenant
     */
    private $tenant;
    
    /**
     * @var UserDetail
     */
    private $UserDetail;
    
    /**
     * @var userClass
     */
    private $userClass;

    /**
     * Create the event listener.
     *
     * @return void
     */

    public function __construct()
    {
        //
        $this->user = new User();
        $this->UserDetail = new UserDetail();
        $this->tenant = new Tenant();
        $this->userClass = new UserClass();
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\UserEmailVerified $event
     * @return void
     * @throws Exception
     */
    public function handle(UserEmailVerified $event)
    {
        $user_id = $event->userId;
        $user = $this->user::find($user_id);

        // $tenantCreate = $this->tenant->create(['id' => $user->subdomain]);
        // $tenantCreate->domains()->create(['domain' => $user->subdomain.'.'.config('settings.customer_support.domain')]);

        // $this->tenant->all()->runForEach(function () {
        //     // App\Models\User::factory()->create();
        //     $this->UserDetail->create([
        //         'user_id' => "12",
        //         'slug' => "1234"
        //     ]);
        // });

        $this->UserDetail->create([
            'user_id' => $user->id,
            'slug' => date('Ymd').rand(000000,999999).date('His'),
            'referral_code' => $this->userClass->generateReferralCode($user->subdomain)
        ]);

        
        //send welcome mail
        Notification::route('mail', $user->email)
            ->notify(new WelcomeNotification($user->subdomain));

        return ;
    }
}
