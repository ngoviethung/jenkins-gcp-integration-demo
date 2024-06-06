<?php


namespace App\Events\Challenge;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class DocumentWasArchived.
 */
class ChallengeWasResult
{
   
    use Dispatchable, SerializesModels;

    public $user_id;
    public $change_id;
    public $challenge_id;
    public $my_challenge;

    /**
     * Create a new event instance.
     *
     * @param Item $item
     * @param $user_id
     */
    public function __construct($user_id,$challenge_id, $my_challenge, $change_id)
    {
       
        $this->user_id = $user_id;
        $this->change_id = $change_id;
        $this->challenge_id = $challenge_id;
        $this->my_challenge = $my_challenge;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return [];
    }
}