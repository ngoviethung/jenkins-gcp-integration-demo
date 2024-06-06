<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function show($id) {
        try {
            $event = Event::find($id);
            if(!$event) {
                throw new \Exception('Something Wrong', 404);
            }

            return [
                'success' => true,
                'code' => 200,
                'data' => [
                    'leader_boards' => json_decode($event->leaderBoards)
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }
}

