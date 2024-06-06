<?php


namespace App\Events\Vote;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
/**
 * Class DocumentWasArchived.
 */
class VoteWasSubmit
{
   
    use Dispatchable, SerializesModels;

    public $current_streak_vote;
    public $user_id;
    public $change_id;

    /**
     * Create a new event instance.
     *
     * @param Item $item
     * @param $user_id
     */
    public function __construct($user_id, $current_streak_vote, $change_id)
    {
       
        $this->current_streak_vote = $current_streak_vote;
        $this->user_id = $user_id;
        $this->change_id = $change_id;
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