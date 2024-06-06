<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;


use App\EloVote;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\Admin\Api\ItemResource;
use App\Http\Resources\Admin\Api\StyleResource;
use App\Http\Resources\Admin\Api\TopicCurrentForItem;
use App\Models\Item;
use App\Models\Topic;
use App\Models\Type;
use App\User;
use GeoIp2\Database\Reader;
use Datetime;
use Cache;
use Log;
use App\Models\Rating as Rating;
use DB;

class EloController extends AppBaseController
{

    private $user;

    public function __construct()
    {
        $this->user = $this->validate_api_token();

    }

    public function vote() {

        try {


            $this->cleanAllExpiredMatch();

            $winItemId = \request()->post('win_item_id');
            $lostItemId = \request()->post('lost_item_id');
            $matchId = \request()->post('match_id');

            $match = EloVote::find($matchId);
            if(!$match) {
                throw new \Exception('Match not found or expired.');
            }

            if(!$winItemId || !$lostItemId) {
                throw new \Exception("win_item_id and lost_item_id required");
            }

            $winItem = Item::find($winItemId);
            if(!$winItem) {
                throw new \Exception("Wrong Win Item");
            }

            $lostItem = Item::find($lostItemId);
            if(!$lostItem) {
                throw new \Exception("Wrong Lost Item");
            }

            $scores = $this->calculate($lostItem, $winItem);
            $lostItem->elo_score = doubleval($scores['lost_score']);
            $lostItem->save();

            $winItem->elo_score = doubleval($scores['win_score']);
            $winItem->save();

            $match->win_item_id = $winItemId;
            $match->lost_item_id = $lostItemId;
            $match->win_score = $winItem->elo_score;
            $match->lost_score = $lostItem->elo_score;
            $match->status = 1;
            $match->save();

            $topic = Topic::find($match->topic_id);

            $items = $this->getItems($topic, $match->type_id, $this->user->id);

            Log::info($lostItemId);

            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => $items
            ]);

        }  catch (\Exception $exception) {

            Log::info('Vote error');
            Log::info($exception->getMessage());

            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => [
                    'message' => $exception->getMessage()
                ]
            ]);
        }

    }

    public function calculate($lostItem, $winItem) {
        // player A elo = 1000
        // player B elo = 2000
        // player A lost
        // player B win

        $rating = new Rating($lostItem->elo_score, $winItem->elo_score, Rating::LOST, Rating::WIN);

        // player A elo = 1000
        // player B elo = 2000
        // player A draw
        // player B draw

        #$rating = new Rating(1000, 2000, Rating::DRAW, Rating::DRAW);

        $results = $rating->getNewRatings();


        return [
            'lost_score' => $results['a'],
            'win_score' => $results['b']
        ];
    }

    public function getPairItems() {
        try {

            $this->cleanAllExpiredMatch();

            $topicId = \request()->post('topic_id');
            $typeId = \request()->post('type_id');

            if(!$topicId || !$typeId) {
                throw new \Exception("topic_id and type_id required");
            }

            $topic = Topic::find($topicId);
            if(!$topic) {
                throw new \Exception("Wrong Topic");
            }
            $type = Type::find($typeId);
            if(!$type) {
                throw new \Exception("Wrong Type");
            }

            $items = $this->getItems($topic, $typeId, $this->user->id);

            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => $items
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => [
                    'message' => $exception->getMessage()
                ]
            ]);
        }
    }

    public function getData() {
        try {
            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => [
                    'topics' => Topic::get(['id', 'name'])->toArray(),
                    'types' => Type::get(['id', 'name'])->toArray(),
                ]
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data'   => [
                    'message' => $exception->getMessage()
                ]
            ]);
        }
    }

    protected function getItemsBk($topic, $typeId, $exceptIds = []) {

        $items = $topic->items()->where('type_id', '=', $typeId);

        if(count($exceptIds)) {
            $items = $items->whereNotIn('items.id', $exceptIds);
        }
        $items = $items->get();
        $items = collect($items)
            ->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => url($item->image),
                    'thumb_top' => url($item->thumb_top),
                    'pos_x' => $item->pos_x,
                    'pos_y' => $item->pos_y,
                ];
            })
            ->random(2);

        return $items;
    }

    protected function getCombinations($ids, $n = 2) {
        sort($ids);
        $result = [];

        for($i = 0; $i < count($ids); $i++) {
            for($j = 0; $j < count($ids); $j++) {
                if($ids[$i] <= $ids[$j]) {
                    continue;
                }

                $result [] = [
                    'num' => 0,
                    'values' => [
                        $ids[$i],
                        $ids[$j]
                    ]
                ];
            }
        }

        return $result;
    }

    protected function getCompletedMatches($topic, $typeId) {
        $votes = EloVote::select(['win_item_id', 'lost_item_id', DB::raw('COUNT(*) as num')])
            ->where('topic_id', '=', $topic->id)
            ->where('type_id', '=', $typeId)
            ->groupBy('win_item_id', 'lost_item_id')
            ->get()->toArray();

        $votes = array_map(function ($e) {
            return [
                'num' => $e['num'],
                'values' => [
                    $e['win_item_id'],
                    $e['lost_item_id']
                ]
            ];
        }, $votes);

        return $votes;
    }

    protected function cleanAllExpiredMatch() {
        $expiredMatches = EloVote::where('created_at', '<=', date('Y-m-d H:i:s', time() - 60))
            ->where('status', '=', 0)
            ->get();

        if(count($expiredMatches)) {
            foreach ($expiredMatches as $expiredMatch) {
                $expiredMatch->delete();
            }
        }
    }

    protected function getItems($topic, $typeId, $userId) {

        $this->cleanAllExpiredMatch();

        $matchNum = EloVote::where('topic_id', '=', $topic->id)
            ->where('type_id', '=', $typeId)
            ->get()
            ->toArray();

        $allIds = $topic->items()->where('type_id', '=', $typeId)->pluck('items.id')->toArray();
        if(!$allIds){
            throw new \Exception("Doesn't have any items.");
        }
        $expectedMatches =0;
        for($i = 0; $i < count($allIds); $i++) {
            $expectedMatches += $i;
        }

        if($expectedMatches == $matchNum) {
            throw new \Exception('Expected matches reached.');
        }

        $allCombinations = $this->getCombinations($allIds);
        $completedMatches = $this->getCompletedMatches($topic, $typeId);

        foreach ($completedMatches as $completedMatch) {
            foreach ($allCombinations as &$combination) {
                if(count(array_diff($combination['values'], $completedMatch['values'])) == 0
                    && count(array_diff($completedMatch['values'], $combination['values'])) == 0
                ) {
                    $combination['num'] += $completedMatch['num'];
                }
            }
        }

        $allCombinations = collect($allCombinations)->sortBy('num');
        $combination = $allCombinations->first();


        $items = Item::whereIn('id', $combination['values']);
        $items = $items->get();
        $items = collect($items)
            ->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => url($item->image),
                    'thumb_top' => url($item->thumb_top),
                    'pos_x' => $item->pos_x,
                    'pos_y' => $item->pos_y,
                ];
            })
            ->take(2);

        $newEloVote = new EloVote();
        $newEloVote->fill([
            'user_id' => $userId,
            'email' => $this->user->email,
            'topic_id' => $topic->id,
            'type_id' => $typeId,
            'win_item_id' => $items[0]['id'],
            'lost_item_id' => $items[1]['id'],
            'status' => 0,
            'win_score' => 0,
            'lost_score' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->save();

        return [
            'items' => $items,
            'match_id' => $newEloVote->id,
            'timeout' => 60
        ];
    }
}

