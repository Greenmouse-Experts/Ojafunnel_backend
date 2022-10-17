<?php

namespace App\Providers;

use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Events\UserEmailVerified;
use App\Listeners\UserEmailVerifiedListener;
use App\Events\UserRegistrationSuccessful;
use App\Listeners\UserRegistrationSuccessfulListener;
use App\Events\SuccessfulFlutterwaveCardTransaction;
use App\Listeners\SuccessfulFlutterwaveCardTransactionListener;
use App\Events\SuccessfulFlutterwaveTokenChargeTransaction;
use App\Listeners\SuccessfulFlutterwaveTokenChargeTransactionListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(
            UserEmailVerified::class,
            [UserEmailVerifiedListener::class, 'handle']
        );

        Event::listen(
            SuccessfulFlutterwaveCardTransaction::class,
            [SuccessfulFlutterwaveCardTransactionListener::class, 'handle']
        );

        Event::listen(
            SuccessfulFlutterwaveTokenChargeTransaction::class,
            [SuccessfulFlutterwaveTokenChargeTransactionListener::class, 'handle']
        );

        Event::listen(
            UserRegistrationSuccessful::class,
            [UserRegistrationSuccessfulListener::class, 'handle']
        );
    }
}
