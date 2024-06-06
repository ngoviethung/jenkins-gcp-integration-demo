<?php

namespace App\Http\Controllers\Admin;

use App\Models\GroupLevelTask;
use App\Models\Language;
use App\Models\Style;
use App\Models\TaskCategory;
use App\Models\Topic;
use App\Models\Type;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Admin\Task\TaskRequest as StoreRequest;
use App\Http\Requests\Admin\Task\TaskRequest as UpdateRequest;
use Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;
use Backpack\CRUD\CrudPanel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class TaskCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class TaskCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use CloneOperation;

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Task');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/task');
        $this->crud->setEntityNameStrings('task', 'tasks');
        $this->crud->setCreateView('backpack::base.task.create');
        $this->crud->setEditView('backpack::base.task.edit');
        $this->crud->orderBy('created_at', 'DESC');
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        $this->crud->allowAccess('clone');
        $this->crud->addButton('bottom', 'clone', 'view', 'crud::buttons.bulk_clone', 'beginning');

        // Column
        $columns = [
            [
                'name' => 'cover',
                'label' => "Cover",
                'type' => 'image',
                'height' => '200px',
                'width' => '200px',
            ],
            [
                'name' => 'background',
                'label' => "Background",
                'type' => 'image',
                'height' => '200px',
                'width' => '200px',
            ],
            [
                'name' => 'list_types', // The db column name
                'label' => "Types", // Table column heading
                'type' => 'view',
                'view' => 'vendor.backpack.crud.columns.list_custom', // or path to blade file
            ],
            [
                'name' => 'list_styles', // The db column name
                'label' => "Styles", // Table column heading
                'type' => 'view',
                'view' => 'vendor.backpack.crud.columns.list_custom', // or path to blade file
            ],
            [
                'label' => "Topic",
                'type' => "select",
                'name' => 'topic',
                'entity' => 'topic',
                'attribute' => "name",
                'model' => \App\Models\Topic::class
            ],
            [
                'name' => 'group_level_task_id',
                'type' => 'select',
                'label' => 'Group Level',
                'entity' => 'groupleveltask', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => \App\Models\GroupLevelTask::class, // foreign key model
                'text' => "- Select -",
            ],
            [ // image
                'label' => "In Local",
                'name' => "in_local",
                'type' => 'custom.check'
            ],
            [
                'name' => 'price_currency',
                'label' => "Currency",
                'type' => 'text',
            ],
        ];
        $languages = Language::latest()->get(['id', 'name', 'symbol']);
        foreach ($languages as $language) {
            $nameField = "name_{$language->symbol}";
            $fieldOrder = "name->" . $nameField;
            array_unshift($columns, [
                'name' => $nameField,
                'field_order' => $fieldOrder,
                'label' => "Name {$language->name}",
                'type' => 'string',
                'orderable' => true,
                'orderLogic' => function ($query, $column, $columnDirection) {
                    return $query
                        ->orderBy($column['field_order'], $columnDirection);
                },
                'searchLogic' => function ($query, $column, $searchTerm) {
                    $query->orWhere($column['field_order'], 'like', '%' . $searchTerm . '%');
                }
            ]);
        };

        $this->crud->setColumns($columns);

        foreach ($languages as $language) {
            $nameField = "name_{$language->symbol}";
            $descriptionField = "description_{$language->symbol}";
            $this->crud->addField([ // select_from_array
                'name' => $nameField,
                'label' => "Name",
                'type' => 'text',
                'fake' => true,
                'store_in' => 'name',
                'tab' => $language->name,
            ]);
            $this->crud->addField([ // select_from_array
                'name' => $descriptionField,
                'label' => "Description",
                'type' => 'text',
                'fake' => true,
                'store_in' => 'description',
                'tab' => $language->name,
            ]);
        }

        // Chung

        $this->crud->addField([ // image
            'label' => "In Local",
            'name' => "in_local",
        ]);

        $this->crud->addField([ // image
            'label' => "Cover",
            'name' => "cover",
            'type' => 'browse'
        ]);
        $this->crud->addField([ // image
            'label' => "Background",
            'name' => "background",
            'type' => 'browse'
        ]);
        $this->crud->addField([
            'label' => "Topic",
            'type' => 'select',
            'name' => 'require_topic', // the db column for the foreign key
            'entity' => 'topic', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => "App\Models\Topic",
        ]);
        $this->crud->addField([
            'name' => 'group_level_task_id',
            'type' => 'select2',
            'label' => 'Group Level',
            // the method that defines the relationship in your Model
            'entity' => 'groupleveltask', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\GroupLevelTask::class, // foreign key model
            'text' => "- Select -",
        ]);

        $this->crud->addField([
            'label' => "Category",
            'type' => 'select',
            'name' => 'category_id', // the db column for the foreign key
            'entity' => 'category', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => TaskCategory::class,
        ]);

        $this->crud->addField([ // image
            'label' => "Weight",
            'name' => "weight",
        ]);

        $this->crud->addField([ // image
            'label' => "Min Score",
            'name' => "min_score",
        ]);

        $this->crud->addField([ // image
            'label' => "Reward Coin",
            'name' => "reward_coin",
        ]);
        $this->crud->addField(
            [   // radio
                'name'        => 'currency', // the name of the db column
                'label'       => 'Currency', // the input label
                'type'        => 'radio',
                'options'     => [
                    // the key will be stored in the db, the value will be shown as label;
                    1 => "Soft",
                    2 => "Hard"

                ],
                'default' => 1,
                // optional
                'inline'      => true, // show the radios all on the same line?

            ]);

        $this->crud->addField([
            'name' => 'types',
            'type' => 'select2_multiple',
            'label' => 'Types',
            // the method that defines the relationship in your Model
            'entity' => 'types', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\Type::class, // foreign key model
            'text' => "- Select -",
            'pivot' => true
        ]);



        $this->crud->addButtonFromView('top', 'export', 'export', 'beginning');


        // add asterisk for fields that are required in TaskRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function setupListOperation()
    {
        $types = Type::get(['name', 'id']);
        $typeOptions = [];
        foreach ($types as $key => $type) {
            $typeOptions[$type->id] = $type->name;
        }
        $this->crud->addFilter([ // select2_ajax filter
            'name' => 'type',
            'type' => 'select2_multiple',
            'label' => 'Type',
            'placeholder' => 'Pick a type'
        ], function () use ($typeOptions) {
            return $typeOptions;
        },
            function ($values) { // if the filter is active
                if($values) {
                    foreach (json_decode($values) as $key => $value) {
                        $this->crud->query = $this->crud->query->whereHas('types', function ($query) use ($value) {
                            $query->where('type_id', $value);
                        });
                    }
                }
            });


        $styles = Style::get(['name', 'id']);
        $styleOptions = [];
        foreach ($styles as $key => $style) {
            $styleOptions[$style->id] = $style->name;
        }
        $this->crud->addFilter([ // select2_ajax filter
            'name' => 'styles',
            'type' => 'select2_multiple',
            'label' => 'Styles',
            'placeholder' => 'Pick a style'
        ], function () use ($styleOptions) {
            return $styleOptions;
        },
            function ($values) { // if the filter is active
                if($values) {
                    foreach (json_decode($values) as $key => $value) {
                        $this->crud->query = $this->crud->query->whereHas('styles', function ($query) use ($value) {
                            $query->where('style_id', $value);
                        });
                    }
                }
            });


        // Topics
        $topics = Topic::get(['name', 'id']);
        $topicOptions = [];
        foreach ($topics as $key => $topic) {
            $topicOptions[$topic->id] = $topic->name;
        }
        $this->crud->addFilter([ // select2_multiple filter
            'name' => 'topics',
            'type' => 'select2',
            'label' => 'Topics'
        ], function () use ($topicOptions) {
            return $topicOptions;
        }, function ($value) { // if the filter is active
            $this->crud->addClause('where', 'require_topic', $value);
        });

        $taskCategories = TaskCategory::all()->pluck('name', 'id')->toArray();
        $this->crud->addFilter([ // select2_multiple filter
            'name' => 'category',
            'type' => 'select2',
            'label' => 'Categories'
        ], function () use ($taskCategories) {
            return $taskCategories;
        }, function ($value) { // if the filter is active
            $this->crud->addClause('where', 'category_id', $value);
        });

        $groupLevelTasks = GroupLevelTask::all()->pluck('name', 'id')->toArray();
        $this->crud->addFilter([ // select2_ajax filter
            'name' => 'group_level_task_id',
            'type' => 'select2',
            'label' => 'Group Level',
            'placeholder' => 'Pick a group level'
        ], function () use ($groupLevelTasks) {
            return $groupLevelTasks;
        },
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'group_level_task_id', $value);
            });
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

        $this->syncStyles($request);
//        $this->syncTypes($request);

        return $this->crud->performSaveAction($item->getKey());
    }

    private function syncStyles($request): void
    {
        DB::beginTransaction();
        try {
            $task = $this->data['entry'];
            $styles = $request->get('styles');
            // Delete previous styles in pivot table
            $task->styles()->detach();
            if ($styles) {
                $stylesFomarted = array_map(function ($style) {
                    return [
                        'style_id' => $style['id'],
                        'factor' => $style['factor'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }, $styles);
                $task->styles()->sync($stylesFomarted);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            \Alert::error('Thêm style thất bại')->flash();
        }
    }

    private function syncTypes($request): void
    {
        DB::beginTransaction();
        try {
            $task = $this->data['entry'];
            $types = $request->get('types');
            // Delete previous styles in pivot table
            $task->types()->detach();
            if ($types) {
                $typesFomarted = array_map(function ($type) {
                    return [
                        'type_id' => $type['id'],
                        'factor' => $type['factor'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }, $types);
                $task->types()->sync($typesFomarted);
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

        $this->syncStyles($request);
//        $this->syncTypes($request);

        return $this->crud->performSaveAction($item->getKey());
    }

    public function clone($id)
    {
        $this->crud->hasAccessOrFail('clone');
        $this->crud->setOperation('clone');

        try {
            DB::beginTransaction();

            $model = $this->crud->model->findOrFail($id);
            $clonedEntry = $model->replicate();
            $clonedEntry->push();

            if(count($model->styles)) {
                foreach ($model->styles as $style) {
                    $clonedEntry->styles()->attach($style->id, ['factor' => $style->pivot->factor]);
                }
            }

            if(count($model->types)) {
                foreach ($model->types as $type) {
                    $clonedEntry->types()->attach($type->id, ['factor' => $type->pivot->factor]);
                }
            }

            DB::commit();
            return (string) $clonedEntry->push();

        } catch (\Exception $exception) {

            DB::rollback();
            throw $exception;
        }
    }
}
