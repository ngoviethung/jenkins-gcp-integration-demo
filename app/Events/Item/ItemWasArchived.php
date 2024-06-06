<?php


namespace App\Events\Item;

use App\Models\Item;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class DocumentWasArchived.
 */
class ItemWasArchived
{
   
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $arr_item_id;
    public $user_id;
    public $change_id;
    public $arr_total_currency_item;

    /**
     * Create a new event instance.
     *
     * @param $arr_item_id
     * @param $user_id
     */
    public function __construct($user_id, $arr_item_id, $arr_total_currency_item, $change_id)
    {
        $this->arr_item_id = $arr_item_id;
        $this->user_id = $user_id;
        $this->change_id = $change_id;
        $this->arr_total_currency_item = $arr_total_currency_item;
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