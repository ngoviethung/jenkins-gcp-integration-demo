<?php


namespace App\Events\Challenge;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Challenge;
/**
 * Class DocumentWasArchived.
 */
class ChallengeWasSubmit
{
   
    use Dispatchable, SerializesModels;

    public $data;
    public $user_id;
    public $change_id;

    /**
     * Create a new event instance.
     *
     * @param Item $item
     * @param $user_id
     */
    public function __construct($user_id, $data, $change_id)
    {
       
        $this->data = $data;
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