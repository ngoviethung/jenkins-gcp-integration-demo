<?php

namespace App\Console\Commands;

use App\Http\Resources\EventResources;
use App\Models\Event;
use App\Models\Topic;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Kreait\Firebase\RemoteConfig;

class GenerateTopicTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:topictask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Topic And Task on Firebase';

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

        foreach (Topic::all()->where('id', '>=', 7)->pluck('id')->toArray() as $id) {
            if(file_exists(base_path('public/export/' . 'topic_' . $id.'.json'))) {
                $content = file_get_contents(url('export/topic_' . $id.'.json'));
                if(!$content) {
                    continue;
                }

                $androidJson = __DIR__ . '/du2_remoteconfig_test.json';
                $factory = (new Factory)
                    ->withServiceAccount($androidJson);
                $remoteConfig = $factory->createRemoteConfig();
                $template = $remoteConfig->get();

                $parameterGroups = $template->parameterGroups();
                $eventGroup = $parameterGroups['Game Data'];

                $eventGroupParameters = $eventGroup->parameters();
                if(isset($eventGroupParameters["topic_{$id}"])) {
                    $topicParam = $eventGroupParameters["topic_{$id}"];
                    $topicParam->withDefaultValue($content);
                } else {
                    $topicParam = RemoteConfig\Parameter::named("topic_{$id}")
                        ->withDefaultValue($content);
                }
                $eventGroup = $eventGroup->withParameter($topicParam);
                $template = $template->withParameterGroup($eventGroup);

                /** Kết thúc chỉnh sửa Event Mode */
                $remoteConfig->publish($template);
            }
        }

        return true;
    }

    public function generateEventsJson($date) {
        $currentEvents = Event::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get();

        $endDates = $currentEvents->pluck('end_date')->toArray();
        $currentIds = $currentEvents->pluck('id')->toArray();

        $endDates = array_map(function ($e) {
            return date('Y-m-d', strtotime($e) + 3600 * 24);
        }, $endDates);

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
