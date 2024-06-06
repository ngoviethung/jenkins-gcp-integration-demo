<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01-Nov-19
 * Time: 2:30 PM
 */

namespace App\Http\Controllers\Api;


use App\Http\Resources\Admin\Api\ItemResource;
use App\Http\Resources\Admin\Api\StyleResource;
use App\Http\Resources\Admin\Api\TopicCurrentForItem;
use App\Models\Item;
use App\Models\Topic;
use App\Models\Type;
use App\Receipt;
use GeoIp2\Database\Reader;
use Datetime;
use Cache;
use Log;

class IndexController
{
    public function getTime()
    {
        try {
            $ip = $this->get_client_ip();
            $timeZone = Cache::get('city_ips_'. $ip);
            if(!$timeZone) {

                try {
                    $dbPath = public_path('GeoLite2-City.mmdb');
                    $reader = new Reader($dbPath);
                    $record = $reader->city($ip);
                    $timeZone = new \DateTimeZone($record->location->timeZone);

                    Cache::put('city_ips_'. $ip, $timeZone);
                    Log::info('Cached city_ips_' . $ip);
                } catch (\Exception $e) {
                    $ipResponse = $this->get_from_api($ip);
                    if($ipResponse !== false && $ipResponse->status == 'success') {
                        $timeZone = $ipResponse->timezone;
                        $timeZone = new \DateTimeZone($timeZone);
                        Cache::put('city_ips_'. $ip, $timeZone);
                        Log::info('Cached city_ips_' . $ip);
                    } else {
                        $timeZone = 'UTC';
                        $timeZone = new \DateTimeZone($timeZone);
                    }
                }
            }

            $date = new DateTime("now", $timeZone );
            $nanoTimes = microtime();
            list($nano, $second) = explode(' ', $nanoTimes);
            $nano = substr($nano, 1, 7);
            // Calculating the offset between the timezones
            $offset = $timeZone->getOffset($date);
            $offsetHours = $offset / 3600;
            $offsetLabel = ($offset > 0 ? '+' : '-') . (abs($offsetHours) > 10 ? abs($offsetHours) : '0' . abs($offsetHours)) . ':00';

            $datetime = $date->format('Y-m-d H:i:s');
            $unixTime = strtotime($datetime);
            $datetime = str_replace(' ', 'T', $datetime);
            return [
                'datetime' => $datetime . $nano . $offsetLabel,
                'unixtime' => $unixTime
            ];
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return [
                'error' => $exception->getMessage()
            ];
        }
    }

    function get_from_api($ip) {
        $ipResponse = file_get_contents("https://pro.ip-api.com/json/$ip?key=ATubVoNTyiG6uAB&fields=status,timezone");
        Log::info('Calling IAP API for ' . $ip);
        if($ipResponse) {
            return json_decode($ipResponse);
        }

        return false;
    }

    // Function to get the client IP address
    function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function iap() {
        try {
            $appFlyerId = \request()->post('appsflyer_id');
            $receipt    = \request()->post('receipt');
            $gaid = \request()->post('gaid');
            $cuid = \request()->post('cuid');

            $receiptModel = new Receipt();
            $receiptModel->fill([
                'appsflyer_id' => $appFlyerId,
                'receipt' => $receipt,
                'gaid' => $gaid,
                'cuid' => $cuid
            ])->save();

            return \response()->json([
                'status' => 'success',
                'code'   => 200,
            ]);

        }  catch (\Exception $exception) {
            return \response()->json([
                'status' => 'error',
                'code'   => $exception->getMessage(),
                'data'   => [
                    'message' => $exception->getMessage()
                ]
            ]);
        }
    }

    public function getInfoFromOrder() {
        try {
            $orderId = \request()->get('orderId');

            if(!$orderId) {
                throw new \Exception('Missing Order Id');
            }

            $receipt = Receipt::where('receipt', 'like', "%$orderId%")->first();
            if(!$receipt) {
                throw new \Exception('Wrong id');
            }

            return response()->json([
                'status' => 'success',
                'code'   => 200,
                'data' => [
                    'appsflyer_id' => $receipt->appsflyer_id,
                    'gaid' => $receipt->gaid,
                    'cuid' => $receipt->cuid
                ]
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'code'   => $exception->getCode(),
                'data'   => [
                    'message' => $exception->getMessage()
                ]
            ]);
        }
    }
}
