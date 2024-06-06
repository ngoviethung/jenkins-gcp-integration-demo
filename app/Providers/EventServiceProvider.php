<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\Item\ItemWasArchived;
use App\Listeners\Item\ItemArchivedActivity;

use App\Events\Challenge\ChallengeWasSubmit;
use App\Listeners\Challenge\ChallengeSubmitedActivity;

use App\Events\Challenge\ChallengeWasResult;
use App\Listeners\Challenge\ChallengeResultActivity;

use App\Events\Vote\VoteWasSubmit;
use App\Listeners\Vote\VoteSubmitedActivity;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ItemWasArchived::class => [
            ItemArchivedActivity::class,
        ],
        ChallengeWasSubmit::class => [
            ChallengeSubmitedActivity::class,
        ],
        ChallengeWasResult::class => [
            ChallengeResultActivity::class,
        ],
        VoteWasSubmit::class => [
            VoteSubmitedActivity::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
