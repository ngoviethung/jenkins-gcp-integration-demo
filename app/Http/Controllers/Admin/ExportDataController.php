<?php
/**
 * Created by PhpStorm.
 * User: tuananh
 * Date: 05/11/2019
 * Time: 17:08
 */

namespace App\Http\Controllers\Admin;


use App\Http\Resources\Admin\Export\TaskResource;
use App\Models\CharacterModel;
use App\Models\GroupLevelTask;
use App\Models\GroupLevelTopic;
use App\Models\GroupLevelType;
use App\Models\Language;
use App\Models\Style;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\Topic;
use App\Models\Type;
use App\TaskRevision;
use App\TopicRevision;
use App\Models\Outfit;
use App\OutfitRevision;
use App\Models\Item;
use DB;
use Log;

use Kreait\Firebase\Factory;
use Kreait\Firebase\RemoteConfig;

class ExportDataController extends \App\Http\Controllers\Controller
{
    CONST EXPORT_FOLDER = 'export';

    public $directory_seperator;
    public $uploadPath;
    public $exportPath;
    public $google_service_acc_android = 'du03_android.json';


    public function __construct()
    {
        ini_set('max_execution_time', 3000);
        ini_set('memory_limit', -1);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->directory_seperator = "\\";
        } else {
            $this->directory_seperator = DIRECTORY_SEPARATOR;
        }
        $this->uploadPath = public_path() . $this->directory_seperator . 'uploads';
        $this->exportPath = public_path() . $this->directory_seperator . 'export';
    }

    public function submitToFirebase() {

        $from = request()->post('from');
        $changeLogs = request()->post('changeLogs');
        $changeLogs = json_decode($changeLogs);

        $changeLogs_android = [
            'changeLogs' => $changeLogs->changeLogs_android
        ];
        $changeLogs_android = json_encode($changeLogs_android);

        $changeLogs_ios = [
            'changeLogs' => $changeLogs->changeLogs_ios
        ];
        $changeLogs_ios = json_encode($changeLogs_ios);

        $androidJson = __DIR__ . '/'.$this->google_service_acc_android;
        $factory = (new Factory)
            ->withServiceAccount($androidJson);
        $remoteConfig = $factory->createRemoteConfig();
        $template = $remoteConfig->get();

        $parameterGroups = $template->parameterGroups();
        $eventGroup = $parameterGroups['Game Config'];

        $eventGroupParameters = $eventGroup->parameters();
        $eventMode = $eventGroupParameters['data_change_log'];

        /* @todo  thay dữ liệu event mode ở đây */

        $values = $eventMode->defaultValue();

        if($from == 'outfit'){
            if($changeLogs_android != $values->value()) {
                $eventMode = $eventMode->withDefaultValue($changeLogs_android);
                $eventGroup = $eventGroup->withParameter($eventMode);
                $template = $template->withParameterGroup($eventGroup);

                $remoteConfig->publish($template);
            }

        }elseif($from == 'topic'){
            if($changeLogs_android != $values->value()) {
                $eventMode = $eventMode->withDefaultValue($changeLogs_android);
                $eventGroup = $eventGroup->withParameter($eventMode);
                $template = $template->withParameterGroup($eventGroup);
            }

            $topic = file_get_contents(public_path('export/topics.json'));
            $dataTopic = $eventGroupParameters['topics_data_v2'];
            $dataTopic = $dataTopic->withDefaultValue($topic);
            $eventGroup = $eventGroup->withParameter($dataTopic);
            $template = $template->withParameterGroup($eventGroup);

            $remoteConfig->publish($template);
        }else{
            die('error');
            return 0;
        }

        return redirect('/admin/topic');
    }
    public function exportToFirebase() {

        try {

            //$this->cleanFolder($this->exportPath);

            $topics = Topic::all();
            DB::beginTransaction();

            $from = request()->post('from');

            //get current data changelog android
            $androidJson = __DIR__ . '/'.$this->google_service_acc_android;
            $factory = (new Factory)->withServiceAccount($androidJson);
            $remoteConfig = $factory->createRemoteConfig();
            $template = $remoteConfig->get();
            $parameterGroups = $template->parameterGroups();
            $eventGroup = $parameterGroups['Game Config'];
            $eventGroupParameters = $eventGroup->parameters();
            $eventMode = $eventGroupParameters['data_change_log'];
            $values = $eventMode->defaultValue();
            $changeLogs = json_decode($values->value());
            $changeLogs_android = $changeLogs->changeLogs;

            $result_android = [];
            $result_ios = [];

            if($from == 'outfit'){
                //topic
                foreach ($topics as $topic) {
                    if($topic->use_in_game == 0){
                        continue;
                    }
                    $fileName = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'topic_' . $topic->id . '.json';
                    $file = 'export/' . basename($fileName);
                    foreach ($changeLogs_android as $value){
                        if($file == $value->file){
                            $result_android [] = [
                                'file' => $file,
                                'version' => $value->version,
                            ];
                        }
                    }


                }
                //task
                $fileName = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'tasks.json';
                $file = 'export/' . basename($fileName);
                foreach ($changeLogs_android as $value){
                    if($file == $value->file){
                        $result_android [] = [
                            'file' => $file,
                            'version' => $value->version,
                        ];
                    }
                }

                //outfit
                foreach ($topics as $topic) {
                    if($topic->use_in_game == 0){
                        continue;
                    }
                    $fileName = $this->_generateTopicOutfit($topic->id);
                    $lastVersion = OutfitRevision::where('topic_id', $topic->id)->orderBy('version', 'DESC')->first();
                    $result_android [] = [
                        'file' => 'export/' . basename($fileName),
                        'version' => $lastVersion->version,
                    ];

                }

            }else{ //topic
                foreach ($topics as $topic) {
                    if($topic->use_in_game == 0){
                        continue;
                    }
                    $fileName = $this->_generateTopic($topic, false);
                    $result_android [] = [
                        'file' => 'export/' . basename($fileName),
                        'version' => $topic->lastVersion()->version,
                    ];

                }

                $tasks = Task::all();
                $fileName = $this->_generateTask($tasks, false, false);

                $result_android [] = [
                    'file' => 'export/' . basename($fileName),
                    'version' => Task::getLastVersion()->version
                ];


                //outfit
                foreach ($topics as $topic) {
                    if($topic->use_in_game == 0){
                        continue;
                    }
                    $fileName = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'outfit_' . $topic->id . '.json';
                    $file = 'export/' . basename($fileName);
                    foreach ($changeLogs_android as $value){
                        if($file == $value->file){
                            $result_android [] = [
                                'file' => $file,
                                'version' => $value->version,
                            ];

                        }
                    }


                }
            }

            DB::commit();

            $this->clearCDNCache();

            return [
                'changeLogs_android' => $result_android,
                'changeLogs_ios' => $result_ios,
            ];

        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;

            return [
                'success' => false,
                'code' => $exception->getCode()
            ];
        }


    }

    protected function cleanFolder($folder)
    {
        exec('rm -rf ' . $folder. '/*');
    }


    public function clearCDNCache() {
        try {
            $curl = curl_init();

            $domain_cdn = urlencode(urlencode(env('URL_CDN').'export/*'));

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.bunny.net/purge?url=$domain_cdn",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'AccessKey: f52db29c-cec9-469d-aaab-d39803be8d81f7a43e74-1604-48cc-abda-bf45c3af480f'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
            Log::info($response);
        } catch (\Exception $exception) {
            Log::info('Exception on clear CDN cache');
        }
    }

    public function export()
    {
        DB::table('exports')->insert(['created_at' => date('Y-m-d H:i:s')]);
        return redirect('/admin/download-export');
    }


    protected function _generateTask($tasks, $copyFile = true, $generateTopic = true)
    {
        try {
            $styles = Style::all();
            $types = Type::with('groupleveltype')->get();
            $topics = Topic::with('types')->get();
            $taskCategories = TaskCategory::all();
            $languages = Language::all();


            $newTopics = [];
            $newStyle = [];
            $newType = [];
            $newTaskCategories = [];
            $newLanguages = [];

            foreach ($languages as $language) {
                $newLanguages[] = [
                    'id' => $language->id,
                    'name' => $language->name,
                    'symbol' => $language->symbol
                ];
            }

            $this->_generateLanguages($newLanguages);

            foreach ($types as $type) {
                $newType[] = [
                    'id' => $type->id,
                    'name' => $type->name,
                    'category' => $type->category,
                    'icon' => $this->changeToBytes($type->icon),
                    'icon_selected' => $this->changeToBytes($type->icon_selected),
                    'parent_id' => $type->parent_id ? $type->parent_id : 0,
                    'order_layer' => $type->order,
                    'order_num' => $type->order_num,
                    'lv_unlock_id' => $type->groupleveltype ? $type->groupleveltype->id : 0,
                    'pos_x' => $type->pos_x,
                    'pos_y' => $type->pos_y,
                    'vip' => $type->vip
                ];


                if($copyFile) {
                    $this->copyUploadToExport($type->icon);
                    $this->copyUploadToExport($type->icon_selected);
                }
            }

            foreach ($styles as $style) {
                $newStyle[] = [
                    'id' => $style->id,
                    'name' => $style->name,
                ];
            }
            foreach ($topics as $topic) {

                if($topic->use_in_game == 0){
                    continue;
                }

                $this->copyUploadToExport($topic->image);

                $topicTypes = $topic->types;
                $factorTypes = [];
                if(count($topicTypes)) {
                    foreach ($topicTypes as $topicType) {
                        $factorTypes [] = [
                            'id' => $topicType->id,
                            'factor' => $topicType->pivot->factor
                        ];
                    }
                }

                $countries = $topic->country_models()->count() ? $topic->country_models()->pluck('country_code')->toArray() : [];
                $newTopics [] = [
                    'id' => $topic->id,
                    'name' => $topic->name,
                    'image' =>  $this->changeToBytes($topic->image),
                    'group_level_id' => $topic->group_level_topic_id,
                    'countries' =>$countries,
                    'factor_types' => $factorTypes,
                    'in_local' => $topic->in_local,
                    'locale' => json_decode($topic->locale),
                    'vip' => $topic->vip
                ];
            }

            foreach ($taskCategories as $taskCategory) {
                $newTaskCategories[] = [
                    'id' => $taskCategory->id,
                    'name' => $taskCategory->name,
                ];
            }

            $characterModels = CharacterModel::all()->sortBy('sort_order');
            $models = [];
            foreach ($characterModels as $characterModel) {
                $defaultItems = $characterModel->default_items ? json_decode($characterModel->default_items) : [];

                if($copyFile) {
                    if(count($defaultItems)) {
                        foreach ($defaultItems as $defaultItem) {
                            $this->copyUploadToExport($defaultItem->image);
                            $defaultItem->image = $this->changeToBytes($defaultItem->image);
                        }
                    }
                }

                $models [] = [
                    'id' => $characterModel->id,
                    'name' => $characterModel->name,
                    'image' => $this->changeToBytes($characterModel->image),
                    'thumb' => $this->changeToBytes($characterModel->thumb),
                    'pos_x' => $characterModel->pos_x,
                    'pos_y' => $characterModel->pos_y,
                    'default_items' => $defaultItems,
                    'sort_order' => $characterModel->sort_order,
                ];

                if($copyFile) {
                    $this->copyUploadToExport($characterModel->image);
                    $this->copyUploadToExport($characterModel->thumb);
                }
            }

            $this->_generateModels($models);

            $data = [
                'tasks' => TaskResource::collection($tasks),
                'types' => $newType,
                'styles' => $newStyle,
                'categories' => $newTaskCategories,
//                'topics' => $newTopics,
//                'models' => $models
            ];

            if($copyFile) {
                foreach ($tasks as $task) {
                    if($task->in_local) {
                        $this->copyUploadToExport($task->cover);
                        $this->copyUploadToExport($task->background);
                    }
                }
            }


            $this->_generateTopics($newTopics);

            if($generateTopic) {
                foreach ($topics as $topic) {
                    if($topic->use_in_game == 0){
                        continue;
                    }
                    $topicFile = $this->_generateTopic($topic);
                    if ($topicFile == false) {
                        throw new \Exception('Can not generate topic ' . $topic->id);
                    }
                }
            }

            $groupTaskLevels = GroupLevelTask::all();
            $this->_generateGroupLevelTask($groupTaskLevels);

            $groupLevelTypes = GroupLevelType::all();
            $this->_generateGroupTypeLevel($groupLevelTypes);

            $groupLevelTopics = GroupLevelTopic::all();
            $this->_generateGroupLevelTopic($groupLevelTopics);

            $file = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'tasks.json';

            $jsonContent = json_encode($data);

            if($lastVersion = Task::getLastVersion()) {
                $obj = json_decode($lastVersion->json);

                if($obj && isset($obj->tasks) && $jsonContent != json_encode($obj->tasks)) {
                    $version = new TaskRevision();
                    $versionNumber = $lastVersion->version + 1;
                    $data['version'] = $versionNumber;
                    $finalData = json_encode($data);
                    $version->fill([
                        'json' => $finalData,
                        'version' => $versionNumber
                    ])->save();
                }
            } else {
                $version = new TaskRevision();
                $data['version'] = 0;
                $finalData = json_encode($data);

                $version->fill([
                    'json' => $finalData,
                    'version' => 0
                ])->save();
            }

            if(!isset($finalData)) {
                $data['version'] = Task::getLastVersion()->version;
                $finalData = json_encode($data);
            }

            file_put_contents($file, $finalData);

            return $file;
        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }

    protected function copyUploadToExport($fileName, $changeToBytes = true)
    {
        $fileName = str_replace("/", $this->directory_seperator, $fileName);
        $segments = explode($this->directory_seperator, $fileName);
        unset($segments[count($segments) - 1]);
        $exportPath = implode($this->directory_seperator, $segments);

        if (!file_exists(public_path('export') . $this->directory_seperator . $exportPath)) {
            mkdir(public_path('export') . $this->directory_seperator . $exportPath, 0777, true);
        }
        if (is_file(public_path() . $this->directory_seperator . $fileName) && file_exists(public_path() . $this->directory_seperator . $fileName)) {
            $newFileName = $fileName;
            if($changeToBytes) {
                $newFileName = $this->changeToBytes($fileName);
            }

            copy(public_path() . $this->directory_seperator . $fileName, public_path('export') . $this->directory_seperator . $newFileName);
        }
    }

    protected function changeToBytes($fileName) {
        if($fileName) {
            return $fileName . '.bytes';
        }

        return $fileName;
    }
    protected function _generateTopicOutfit($topic_id){

        try {
            $file_export_json = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'outfit_' . $topic_id . '.json';

            $outfits_good = Outfit::where(['topic_id' => $topic_id, 'category' => 1])->get(['item_id'])->pluck('item_id')->toArray();
            $outfits_bad = Outfit::where(['topic_id' => $topic_id, 'category' => 0])->get(['item_id'])->pluck('item_id')->toArray();


            $items_good = [];
            foreach ($outfits_good as $good){
                $items_good[] = explode(',', $good);
            }
            $items_bad = [];
            foreach ($outfits_bad as $bad){
                $items_bad[] = explode(',', $bad);
            }

            $final_type_item_good = [];
            $final_type_item_bad = [];

            foreach ($items_good as $arr_items){
                $type_item_good = Item::whereIn('id', $arr_items)
                    ->orderBy('type_id', 'ASC')
                    ->get(['id', 'type_id'])->pluck('id', 'type_id')->toArray();
                $final_type_item_good[] = $type_item_good;
            }
            foreach ($items_bad as $arr_items){
                $type_item_bad = Item::whereIn('id', $arr_items)
                    ->orderBy('type_id', 'ASC')
                    ->get(['id', 'type_id'])->pluck('id', 'type_id')->toArray();
                $final_type_item_bad[] = $type_item_bad;
            }

            $data = [
                'list_outfit_good' => $final_type_item_good,
                'list_outfit_bad' => $final_type_item_bad
            ];

            $jsonContent = json_encode($data);


            $lastVersion = OutfitRevision::where('topic_id', $topic_id)->orderBy('version', 'DESC')->first();
            $versionNumber = 0;
            $update = true;

            if($lastVersion) {
                $obj = json_decode($lastVersion->json);
                if($obj && isset($obj->outfits) && $jsonContent != json_encode($obj->outfits)) {
                    $versionNumber = $lastVersion->version + 1;
                }else{ //khong co gi thay doi
                    $versionNumber = $lastVersion->version;
                    $update = false;
                }
            }

            if($update === true){
                $outfitVersion = new OutfitRevision();
                $json = json_encode([
                    'version' => $versionNumber,
                    'outfits' => $data
                ]);
                $outfitVersion->fill([
                    'topic_id' => $topic_id,
                    'json' => $json,
                    'version' => $versionNumber
                ])->save();
            }

            $new_data = [
                'version' => $versionNumber,
                'topic' => $topic_id,
                'list_outfit_good' => $final_type_item_good,
                'list_outfit_bad' => $final_type_item_bad
            ];
            $new_data = json_encode($new_data);
            file_put_contents($file_export_json, $new_data);

            return $file_export_json;

        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }


    }
    protected function _generateTopic($topic, $copyFile = true)
    {
        try {
            $topicFile = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'topic_' . $topic->id . '.json';
            //$items = $topic->items;
            $items = Item::with('styles','models','grouplevelitem')
                ->join('topic_item_rlt', 'items.id', 'topic_item_rlt.item_id')
                ->where('topic_item_rlt.topic_id', $topic->id)
                ->orderBy('topic_item_rlt.id', 'ASC')
                ->get(['items.*']);

            $data = [];

            if ($items) {
                $typeIds = array_map(function ($item) {
                    return $item['type_id'];
                }, $items->toArray());
                $typeIds = array_unique($typeIds);
                sort($typeIds);

                foreach ($typeIds as $typeId) {
                    $typeData = [
                        'type' => $typeId,
                        'items' => []
                    ];

                    foreach ($items as $item) {

                        if($item->ready_to_topic == 0){
                            continue;
                        }

                        if($copyFile && $item->in_local) {
                            $this->copyUploadToExport($item->image);
                            $this->copyUploadToExport($item->thumb_top);
                            $this->copyUploadToExport($item->thumb_bottom);
                        }

                        $styles = collect($item->styles)->transform(function ($e) {
                            return [
                                'id' => $e->id,
                                'score' => $e->pivot->score
                            ];
                        });

                        $models = $item->models;
                        $models = collect($models)->transform(function ($e) {
                            return $e->id;
                        });

                        if ($item->type_id == $typeId) {
                            $typeData['items'] [] = [
                                'id' => $item->id,
//                                "name" => $item->name,
                                "image" => $this->changeToBytes($item->image),
                                "thumb_top" => $this->changeToBytes($item->thumb_top),
                                "thumb_bottom" => $this->changeToBytes($item->thumb_bottom),
                                "price" => $item->price,
                                "price_unit" => $item->currency != null ? $item->currency : 1,
                                "pos_x" => $item->pos_x,
                                "pos_y" => $item->pos_y,
                                "styles" => $styles,
                                "level_unlock" => $item->grouplevelitem ? $item->grouplevelitem->level : 0,
                                "in_local" => $item->in_local,
                                "models" => $models,
                                "type_id" => $typeId,
                                "vip" => $item->vip
                            ];
                        }
                    }

                    $data [] = $typeData;
                }
            }

            $jsonContent = json_encode($data);

            if($lastVersion = $topic->lastVersion()) {
                $obj = json_decode($lastVersion->json);

                if($obj && isset($obj->types) && $jsonContent != json_encode($obj->types)) {
                    $version = new TopicRevision();
                    $versionNumber = $lastVersion->version + 1;
                    $finalData = json_encode([
                        'version' => $versionNumber,
                        'types' => $data
                    ]);

                    $version->fill([
                        'topic_id' => $topic->id,
                        'json' => $finalData,
                        'version' => $versionNumber
                    ])->save();
                }
            } else {
                $version = new TopicRevision();
                $finalData = json_encode([
                    'version' => 0,
                    'types' => $data
                ]);

                $version->fill([
                    'topic_id' => $topic->id,
                    'json' => $finalData,
                    'version' => 0
                ])->save();
            }

            if(!isset($finalData)) {
                $finalData = json_encode([
                    'version' => $topic->lastVersion()->version,
                    'types' => $data
                ]);
            }

            file_put_contents($topicFile, $finalData);

            return $topicFile;
        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }

    protected function _generateLanguages($languages)
    {
        try {
            $languageFile = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'languages.json';
            file_put_contents($languageFile, json_encode($languages));

            return $languageFile;

        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }

    protected function _generateTopics($topics)
    {
        try {
            $topicFile = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'topics.json';
            file_put_contents($topicFile, json_encode($topics));

            return $topicFile;

        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }

    protected function _generateModels($models)
    {
        try {
            $topicFile = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'models.json';
            file_put_contents($topicFile, json_encode($models));

            return $topicFile;

        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }

    protected function _generateGroupLevelTask($tasks)
    {
        try {
            $taskFile = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'groups_task.json';

            $data = [];
            foreach ($tasks as $task) {
                $data [] = \App\Http\Resources\GroupLevelTask::make($task);
            }
            file_put_contents($taskFile, json_encode($data));
            return $taskFile;
        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }

    protected function _generateGroupTypeLevel($levelTypes)
    {
        try {
            $groupLevelTypeFile = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'group_level_unlock_type.json';

            $data = [];
            foreach ($levelTypes as $type) {
                $data [] = [
                    'id' => $type->id,
                    'name' => $type->name,
                    'level' => $type->level
                ];
            }

            file_put_contents($groupLevelTypeFile, json_encode($data));

            return $groupLevelTypeFile;
        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }

    protected function _generateGroupLevelTopic($levelTopics)
    {
        try {
            $groupLevelTopicFile = public_path() . $this->directory_seperator . self::EXPORT_FOLDER . $this->directory_seperator . 'group_level_unlock_topic.json';

            $data = [];
            foreach ($levelTopics as $topic) {
                $data [] = [
                    'id' => $topic->id,
                    'name' => $topic->name,
                    'level' => $topic->level
                ];
            }

            file_put_contents($groupLevelTopicFile, json_encode($data));
            return $groupLevelTopicFile;

        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
    }
}
