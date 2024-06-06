<?php

require __DIR__ . '/../../../vendor/autoload.php';

use GeoIp2\Database\Reader;

try {

    $dbPath = __DIR__ . '/../../GeoLite2-City.mmdb';
    $ip = get_client_ip();


    if(!$timeZone = getCache('timezone_ip_'. $ip)) {
        try {
            $reader = new Reader($dbPath);
            $record = $reader->country($ip);
            $timeZone = $record->location->timeZone;
            setCache('timezone_ip_'. $ip, $timeZone);
        } catch (\Exception $e) {
            $ipResponse = get_from_api($ip);
            if ($ipResponse !== false && $ipResponse->status == 'success') {
                $timeZone = $ipResponse->timezone;
            } else {
                $timeZone = 'UTC';
            }
            setCache('timezone_ip_'. $ip, $timeZone);
        }
    }

    $timeZone = new \DateTimeZone($timeZone);
    $date = new DateTime("now", $timeZone);
    $nanoTimes = microtime();
    list($nano, $second) = explode(' ', $nanoTimes);
    $nano = substr($nano, 1, 7);
    // Calculating the offset between the timezones
    $offset = $timeZone->getOffset($date);
    $offsetHours = $offset / 3600;
    #$offsetLabel = ($offset > 0 ? '+' : '-') . (abs($offsetHours) > 10 ? abs($offsetHours) : '0' . abs($offsetHours)) . ':00';

    $offsetHours = abs($offsetHours);
    $hour = (int) floor($offsetHours);
    $minutes = 60 * ($offsetHours - $hour);
    $offsetLabel = ($offset > 0 ? '+' : '-') . ($hour >= 10 ? $hour : '0' . $hour) . ':' . ($minutes >= 10 ? $minutes : '0' . $minutes);

    $datetime = $date->format('Y-m-d H:i:s');
    $unixTime = strtotime($datetime);
    $datetime = str_replace(' ', 'T', $datetime);
    echo json_encode([
            'datetime' => $datetime . $nano . $offsetLabel,
            'unixtime' => $unixTime
        ]
    );
    exit();
} catch (\Exception $exception) {
    echo json_encode(
        ['error' => $exception->getMessage()]
    );
    exit();
}

function get_from_api($ip)
{
    $ipResponse = file_get_contents("https://pro.ip-api.com/json/$ip?key=ATubVoNTyiG6uAB&fields=status,timezone");
    if ($ipResponse) {
        return json_decode($ipResponse);
    }

    return false;
}

function get_client_ip()
{
    if (getenv('HTTP_CLIENT_IP'))
        return getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        return getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        return getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        return getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        return getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        return getenv('REMOTE_ADDR');
    else
        return 'UNKNOWN';
}

function getCache($key) {
    $memcache = new Memcache();
    $memcache->connect('localhost', 11211);
    return  $memcache->get($key);
}

function setCache($key, $obj) {
    $memcache = new Memcache();
    $memcache->connect('localhost', 11211);
    return  $memcache->set($key, $obj, 0, 0);
}


