<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Models\Shop;
use App\Models\Config;
use App\Models\Level;
use App\Models\Export;
use App\Models\Version;
use App\Models\VotingReward;

class DataController extends AppBaseController
{


    public function getData(Request $request)
    {
        try {
            
            $export = Export::orderBy('id', 'DESC')->first();
            $level = Level::orderBy('id', 'DESC')->first();
            $version_levelsData = Version::where('key', 'levelsData')->first()->version;
            
            $member_id = $request->get('user_id');
            $data = [
                'garmentData' => [
                    'version' => $export->id,
                    'file' => asset('export_to_local/data.json')
                ],
                'levelsData' => [
                    'version' => $version_levelsData,
                    'file' => asset('export/level.json')
                ],
                'votingRewards' => $this->getVotingRewards(),
                'configs' => $this->getConfigs(),

            ];

            return $this->sendResponse($data);
        } catch (exception $e) {
            return $this->sendError('server_error', $e->getMessage(), 404, 'Server error');
        }

    }

    private function getConfigs(){
        $data = [];
        $shop = Shop::orderBy('id', 'DESC')->first();
        $config = Config::first();
        $metadata = $config->metadata;
        foreach($metadata as $key => $config){
            $data[$config['key']] = $this->formatNumberInString($config['value']);
        }
        $data['shop'] = json_decode($shop->metadata);

        return $data;
    }

    private function getVotingRewards(){

        $voting_rewards = VotingReward::get();

        $data = [];
        foreach($voting_rewards as $voting_reward){
            $rewards = $voting_reward->rewards;
            $new_rewards = [];
            foreach($rewards as $reward){
                $new_rewards[] = [
                    'type' => $reward['type'],
                    'value' => $reward['type'] == 'ITEM' ? (int)$reward['item_id'] : (int)$reward['value']
                ];
            }
            $data[] = [
                'streak' => $voting_reward->streak,
                'step' => $voting_reward->step,
                'rewards' => $new_rewards
            ];
        }
        
        return $data;
    }

    private function formatNumberInString($value) {

        $pattern = '/[^0-9.]/';
        if (preg_match($pattern, $value)) {
            return $value;
        }
        // Sử dụng biểu thức chính quy để tìm số trong chuỗi
        $pattern = '/-?\d+(\.\d+)?/';
        preg_match($pattern, $value, $matches);
        if (!empty($matches)) {
            $number = $matches[0];
            // Kiểm tra xem số có dấu chấm thập phân hay không
            if (strpos($number, '.') !== false) {
                $value = (float) $number;
            } else {
                $value = (int) $number;
            }
        }

        return $value;
    }



}
