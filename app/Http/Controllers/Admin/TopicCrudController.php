<?php

namespace App\Http\Controllers\Admin;

use App\Country;
use App\Models\GroupLevelTopic;
use App\Models\TopicDetail;
use App\Models\TopicDetailItem;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Admin\Topic\TopicRequest as StoreRequest;
use App\Http\Requests\Admin\Topic\TopicRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class TopicCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class TopicCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    const STORE_ACTION = 'store';
    const UPDATE_ACTION = 'update';

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Topic');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/topic');
        $this->crud->setEntityNameStrings('topic', 'topics');
        $this->crud->setCreateView('backpack::base.topic.create');
        $this->crud->setEditView('backpack::base.topic.edit');
        $this->crud->orderBy('created_at', 'DESC');
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns

        $this->crud->addButtonFromView('top2', 'export_to_firebase', 'topic.export_to_firebase', 'beginning');

        // add asterisk for fields that are required in TopicRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');

        $this->crud->addField([
            'name' => 'name',
        ]);
        $this->crud->addField([
            'name' => 'image', // The db column name
            'label' => "Image", // Table column heading
            'type' => 'image',
            'upload' => true,
        ]);

        $this->crud->addField([
            'name' => 'country_models',
            'type' => 'select2_multiple',
            'label' => 'Countries',
            'model' => \App\Country::class,
            'entity' => 'country_models',
            'attribute' => 'country_name',
            'default' => GroupLevelTopic::DEFAULT_GROUP_ID,
            'pivot' => true,
        ]);

        $this->crud->addField([
            'name' => 'group_level_topic_id',
            'type' => 'select2',
            'label' => 'Group Level Topic ID',
            'model' => \App\Models\GroupLevelTopic::class,
            'entity' => 'groupleveltopic',
            'attribute' => 'name',
            'default' => GroupLevelTopic::DEFAULT_GROUP_ID,
        ]);
        $this->crud->addField([
            'label' => 'In Local',
            'type' => 'checkbox',
            'name' => 'in_local',
        ]);


        $languages = \App\Models\Language::latest()->get(['id', 'name', 'symbol']);
        foreach ($languages as $language) {
            $nameField = "name_{$language->symbol}";
            $this->crud->addField([ // select_from_array
                'name' => $nameField,
                'label' => "Locale",
                'type' => 'text',
                'fake' => true,
                'store_in' => 'locale',
                'tab' => $language->name,
            ]);
        }
        $this->crud->addField([
            'label' => 'Use In Game',
            'type' => 'checkbox',
            'name' => 'use_in_game',
        ]);
        $this->crud->addField([
            'name' => 'vip',
            'label' => 'Vip',
            'type' => 'checkbox',
        ]);

        $this->crud->addColumn([
            'name' => 'name'
        ]);
        $this->crud->addColumn([
            'label' => "Locale", // Table column heading
            'type' => "model_function",
            'name' => 'locale',
            'function_name' => 'getLocale',
            'limit' => 1000,
            'width' => '150px',
        ]);
        $this->crud->addColumn([
            'name' => 'image', // The db column name
            'label' => "Image", // Table column heading
            'type' => 'image',
            'height' => 'auto',
            'width' => '100px',
        ]);

        $this->crud->addColumn([
            'name' => 'country_models',
            'label' => 'Countries',
            'type' => 'select_multiple',
            'model' => Country::class,
            'entity' => 'country_models',
            'attribute' => 'country_name',
        ]);

        $this->crud->addColumn([
            'name' => 'group_level_topic_id',
            'type' => 'select',
            'model' => GroupLevelTopic::class,
            'entity' => 'groupleveltopic',
            'attribute' => 'name',
        ]);




        $this->crud->addColumn([
            'name' => 'in_local', // The db column name
            'label' => "In Local", // Table column heading
            'type' => 'custom.check'
        ]);

        $this->crud->addColumn([
            'name' => 'use_in_game', // The db column name
            'label' => "Use In Game", // Table column heading
            'type' => 'custom.check'
        ]);
        $this->crud->addColumn([
            'name' => 'vip', // The db column name
            'label' => "Vip", // Table column heading
            'type' => 'custom.check'
        ]);
        $this->crud->addColumn([
            'name' => 'list_types', // The db column name
            'label' => "Types", // Table column heading
            'type' => 'view',
            'view' => 'vendor.backpack.crud.columns.list_custom', // or path to blade file
        ]);

        $this->crud->addFilter([ // select2_multiple filter
            'name' => 'use_in_game',
            'type' => 'dropdown',
            'label' => 'Use In Game'
        ], function () {
            return [
                -1 => 'All',
                0 => 'No',
                1 => 'Yes'
            ];
        }, function ($value) {
            if($value != -1) {
                $this->crud->query = $this->crud->query->where('use_in_game', '=', $value);
            }
        });
        $this->crud->addFilter([ // select2_multiple filter
            'name' => 'inLocalFilter',
            'type' => 'dropdown',
            'label' => 'In Local'
        ], function () {
            return [
                -1 => 'All',
                0 => 'No',
                1 => 'Yes'
            ];
        }, function ($value) {
            if($value != -1) {
                $this->crud->query = $this->crud->query->where('in_local', '=', $value);
            }
        });
        $this->crud->addFilter([ // select2_multiple filter
            'name' => 'vip',
            'type' => 'dropdown',
            'label' => 'Vip'
        ], function () {
            return [
                -1 => 'All',
                0 => 'No',
                1 => 'Yes'
            ];
        }, function ($value) {
            if($value != -1) {
                $this->crud->query = $this->crud->query->where('vip', '=', $value);
            }
        });

    }

    private function handleOldTopicDetail($action)
    {
        if ($action == self::STORE_ACTION) return;
        // if action is update, delete all old relationship is topic_details table and topic_detail_items table
        if ($action == self::UPDATE_ACTION) {
            $topicCurrent = $this->data['entry'];
            $topicCurrent->topicDetailItems()->delete();
            $topicCurrent->topicDetails()->delete();
        }
    }

    private function topicDetailHandler($request, $action = self::STORE_ACTION)
    {
        DB::beginTransaction();
        try {
            $this->handleOldTopicDetail($action);
            $topicCurrent = $this->data['entry'];
            $topic_details = $request->get('topics');

            // if array topics detail is not empty
            if ($topic_details) {
                // Each topic_detail in array
                foreach ($topic_details as $key => $topicDetail) {
                    // Insert to topic_details table
                    $topicDetailCreated = TopicDetail::create([
                        'topic_id' => $topicCurrent->id,
                        'type_id' => $topicDetail['type_id'],
                    ]);
                    // Insert to topic_detail_items table
                    if(isset($topicDetail['item_ids'])){
                        $topic_detail_items = array_map(function ($element) {
                            return new TopicDetailItem([
                                'item_id' => $element
                            ]);
                        }, $topicDetail['item_ids']);
                        $topicDetailCreated->topicDetailItems()->saveMany($topic_detail_items);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            \Alert::error('Thêm detail topic thất bại')->flash();
        }
    }

    private function syncTypes($request): void
    {
        DB::beginTransaction();
        try {
            $topic = $this->data['entry'];
            $types = $request->get('types');
            // Delete previous styles in pivot table
            $topic->types()->detach();
            if ($types) {
                $typesFomarted = array_map(function ($type) {
                    return [
                        'type_id' => $type['id'],
                        'factor' => $type['factor'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }, $types);
                $topic->types()->sync($typesFomarted);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            \Alert::error('Thêm types thất bại')->flash();
        }
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest());

        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        $this->syncTypes($request);

        return $this->crud->performSaveAction($item->getKey());
    }

    public function store(StoreRequest $request)
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        $this->syncTypes($request);

        return $this->crud->performSaveAction($item->getKey());
    }
}
