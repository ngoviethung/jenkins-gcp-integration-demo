<?php

namespace App\Console\Commands;

use App\Http\Resources\EventResources;
use App\Models\Event;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Kreait\Firebase\RemoteConfig;

class GenerateEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Leader Board on Firebase';

    public $sGen = true;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $now = date('Y-m-d');
        $object = $this->generateEventsJson($now);

        $iosJson = __DIR__ . '/du03_ios.json';

        $factory = (new Factory)
            ->withServiceAccount($iosJson);
        $remoteConfig = $factory->createRemoteConfig();
        $template = $remoteConfig->get();

        $parameterGroups = $template->parameterGroups();
        $eventGroup = $parameterGroups['Event'];

        $eventGroupParameters = $eventGroup->parameters();
        $eventMode = $eventGroupParameters['event_mode_ver3'];

        /* @todo  thay dữ liệu event mode ở đây */

        $values = $eventMode->defaultValue();

        if(json_encode($object) != $values->value()) {
            $eventMode = $eventMode->withDefaultValue(json_encode($object));
            $eventGroup = $eventGroup->withParameter($eventMode);
            $template = $template->withParameterGroup($eventGroup);

            /** Kết thúc chỉnh sửa Event Mode */
            $remoteConfig->publish($template);
        }


        $androidJson = __DIR__ . '/du3_android.json';

        $factory = (new Factory)
            ->withServiceAccount($androidJson);
        $remoteConfig = $factory->createRemoteConfig();
        $template = $remoteConfig->get();

        $parameterGroups = $template->parameterGroups();
        $eventGroup = $parameterGroups['Event'];

        $eventGroupParameters = $eventGroup->parameters();
        $eventMode = $eventGroupParameters['event_mode_ver3'];

        /* @todo  thay dữ liệu event mode ở đây */

        $values = $eventMode->defaultValue();

        if(json_encode($object) != $values->value()) {
            $eventMode = $eventMode->withDefaultValue(json_encode($object));
            $eventGroup = $eventGroup->withParameter($eventMode);
            $template = $template->withParameterGroup($eventGroup);

            /** Kết thúc chỉnh sửa Event Mode */
            $remoteConfig->publish($template);
        }




        return true;
    }

    public function generateEventsJson($date) {
        $currentEvents = Event::where('start_date', '<=', $date)
            ->where('end_date', '>', $date)
            ->get();

        $endDates = $currentEvents->pluck('end_date')->toArray();
        $currentIds = $currentEvents->pluck('id')->toArray();

        $endDates = array_map(function ($e) {
            return date('Y-m-d', strtotime($e) + 3600 * 24);
        }, $endDates);

        if(!count($endDates)) {
            $endDates  [] = date('Y-m-d', time() + 3600 * 24);
            $endDates  [] = date('Y-m-d', time() + 3600 * 24 * 2);
            $endDates  [] = date('Y-m-d', time() + 3600 * 24 * 3);
        }

        $nextEvents = Event::query()->whereNotIn('id', $currentIds);
        $nextEvents = $nextEvents->where(function ($query) use ($endDates) {
            foreach ($endDates as $endDate) {
                $query->orWhere(function ($query1) use ($endDate) {
                    $query1->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $endDate);
                });
            }
        });

        $nextEvents = $nextEvents->get();


        return [
            'current' => EventResources::collection($currentEvents),
            'next' => EventResources::collection($nextEvents),
        ];
    }
}
