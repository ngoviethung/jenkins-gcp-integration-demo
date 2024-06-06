<?php

namespace App\Http\Controllers\Admin;

use App\Models\CharacterModel;
use App\Models\GroupLevelItem;
use App\Models\Item;
use App\Models\Color;
use App\Models\Collection;
use App\Models\Material;
use App\Models\Pattern;
use App\Models\Tag;
use App\Models\Brand;
use App\Models\Style;
use App\Models\Topic;
use App\Models\TopicDetail;
use App\Models\TopicDetailItem;
use App\Models\Type;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Admin\Item\ItemRequest as StoreRequest;
use App\Http\Requests\Admin\Item\ItemUpdateRequest as UpdateRequest;
use Backpack\CRUD\app\Http\Controllers\Operations\BulkCloneOperation;
use Backpack\CRUD\CrudPanel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Route;

/**
 * Class ItemCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;

    use \App\Http\Controllers\Operations\BulkReadyToTopicOperation;
    use \App\Http\Controllers\Operations\BulkVipOperation;
    use \App\Http\Controllers\Operations\CheckingShowOperation;
//    use BulkCloneOperation;

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        CRUD::setModel('App\Models\Item');
        CRUD::setRoute(config('backpack.base.route_prefix') . '/item');
        CRUD::setEntityNameStrings('item', 'items');
        CRUD::setCreateView('backpack::base.item.create');
        CRUD::setEditView('backpack::base.item.edit');
        CRUD::orderBy('created_at', 'DESC');

        CRUD::enableExportButtons();

        if(backpack_user()->hasRole('Admin')) {
            CRUD::allowAccess('clone');
        }

        if(!backpack_user()->hasRole('Admin')) {
            if(!backpack_user()->hasRole('ItemEditor')) {
                CRUD::denyAccess('create');
                CRUD::denyAccess('update');
                CRUD::denyAccess('delete');
                CRUD::denyAccess('clone');
            }else{
                CRUD::denyAccess('create');
                CRUD::denyAccess('delete');
                CRUD::denyAccess('clone');
            }
        }
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------


        */
        //Filter

        //Type
        $types = Type::get(['name', 'id']);
        $typeOptions = [];
        foreach ($types as $key => $type) {
            $typeOptions[$type->id] = $type->name;
        }
        CRUD::addFilter([ // select2_ajax filter
            'name' => 'type',
            'type' => 'select2',
            'label' => 'Type',
            'placeholder' => 'Pick a type'
        ], function () use ($typeOptions) {
            return $typeOptions;
        },
            function ($value) { // if the filter is active
                $typeIds = Type::where('id', '=', $value)
                    ->orWhere('parent_id', '=', $value)
                    ->pluck('id')->toArray();

                CRUD::addClause('whereIn', 'type_id', $typeIds);
            });



        $groupLevelItems = GroupLevelItem::all()->pluck('name', 'id')->toArray();
        CRUD::addFilter([ // select2_ajax filter
            'name' => 'group_level_item_id',
            'type' => 'select2',
            'label' => 'Group Level',
            'placeholder' => 'Pick a group level'
        ], function () use ($groupLevelItems) {
            return $groupLevelItems;
        },
            function ($value) { // if the filter is active
                CRUD::addClause('where', 'group_level_item_id', $value);
            });

        // Price
        CRUD::addFilter([
            'name' => 'price_filter',
            'type' => 'range',
            'label' => 'Price',
            'label_from' => 'min value',
            'label_to' => 'max value'
        ],
            false,
            function ($value) { // if the filter is active
                $range = json_decode($value);
                if ($range && $range->from) {
                    CRUD::addClause('where', 'price', '>=', (float)$range->from);
                }
                if ($range && $range->to) {
                    CRUD::addClause('where', 'price', '<=', (float)$range->to);
                }
            });

        // Styles
        $styles = Style::get(['name', 'id']);
        $styleOptions = [];
        foreach ($styles as $key => $style) {
            $styleOptions[$style->id] = $style->name;
        }
        CRUD::addFilter([ // select2_multiple filter
            'name' => 'styles',
            'type' => 'select2_multiple',
            'label' => 'Styles'
        ], function () use ($styleOptions) {
            return $styleOptions;
        }, function ($values) { // if the filter is active
            $values = is_array($values) ? $values : json_decode($values);
            foreach ($values as $key => $value) {
                $this->crud->query = $this->crud->query->whereHas('styles', function ($query) use ($value) {
                    $query->where('style_id', $value);
                });
            }
        });
        // Topics
        $topics = Topic::get(['name', 'id']);
        $topicOptions = [];
        foreach ($topics as $key => $topic) {
            $topicOptions[$topic->id] = $topic->name;
        }
        CRUD::addFilter([ // select2_multiple filter
            'name' => 'topics',
            'type' => 'select2',
            'label' => 'Topics'
        ], function () use ($topicOptions) {
            return $topicOptions;
        }, function ($value) {
//            $values = is_array($values) ? $values : json_decode($values);
//            foreach ($values as $key => $value) {
            $this->crud->query = $this->crud->query->whereHas('topics', function ($query) use ($value) {
                $query->where('topic_id', $value);
            });
//            }
        });


        $models = CharacterModel::get(['name', 'id']);
        $modelOptions = [];
        foreach ($models as $key => $model) {
            $modelOptions[$model->id] = $model->name;
        }

        CRUD::addFilter([ // select2_ajax filter
            'name' => 'model_filter',
            'type' => 'select2',
            'label' => 'Models',
            'placeholder' => 'Pick model'
        ], function () use ($modelOptions) {
            return $modelOptions;
        },function ($value) {
//            $values = is_array($values) ? $values : json_decode($values);
//            foreach ($values as $key => $value) {
            $this->crud->query = $this->crud->query->whereHas('models', function ($query) use ($value) {
                $query->where('model_id', $value);
            });
//            }
        });

        CRUD::addFilter([ // select2_multiple filter
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


        CRUD::addFilter([ // select2_multiple filter
            'name' => 'ready_to_topic',
            'type' => 'dropdown',
            'label' => 'Ready To Topic'
        ], function () {
            return [
                -1 => 'All',
                0 => 'No',
                1 => 'Yes'
            ];
        }, function ($value) {
            if($value != -1) {
                $this->crud->query = $this->crud->query->where('ready_to_topic', '=', $value);
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

        $colors = Color::get()->pluck('name', 'id')->toArray();
        CRUD::addFilter([ // select2_multiple filter
            'name' => 'color',
            'type' => 'select2',
            'label' => 'Color'
        ], function () use ($colors) {
            return $colors;
        }, function ($value) {
            $this->crud->query = $this->crud->query->whereHas('colors', function ($query) use ($value) {
                $query->where('color_id', $value);
            });
        });
        $patterns = Pattern::get()->pluck('name', 'id')->toArray();
        CRUD::addFilter([ // select2_multiple filter
            'name' => 'pattern',
            'type' => 'select2',
            'label' => 'Pattern'
        ], function () use ($patterns) {
            return $patterns;
        }, function ($value) {
            $this->crud->query = $this->crud->query->whereHas('patterns', function ($query) use ($value) {
                $query->where('pattern_id', $value);
            });
        });
        $collections = Collection::get()->pluck('name', 'id')->toArray();
        CRUD::addFilter([ // select2_multiple filter
            'name' => 'collection',
            'type' => 'select2',
            'label' => 'Collection'
        ], function () use ($collections) {
            return $collections;
        }, function ($value) {
            $this->crud->query = $this->crud->query->whereHas('collections', function ($query) use ($value) {
                $query->where('collection_id', $value);
            });
        });

        $materials = Material::get()->pluck('name', 'id')->toArray();
        CRUD::addFilter([ // select2_multiple filter
            'name' => 'material',
            'type' => 'select2',
            'label' => 'Materials'
        ], function () use ($materials) {
            return $materials;
        }, function ($value) {
            $this->crud->query = $this->crud->query->whereHas('materials', function ($query) use ($value) {
                $query->where('material_id', $value);
            });
        });


    }


    protected function setupListOperation()
    {
        $this->crud->removeButtons(['create']);
        /*======Columns======*/
        $this->crud->addColumn([
            'name' => 'id',
            'label' => "id",
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'name',
            'label' => "Name",
            'type' => 'text',
        ]);

        $this->crud->addColumn([
            'name'=>'preview',
            'label'=>"Preview",
            'type'=>'preview',
        ]);

        $type_code = request()->get('type_code');
        switch ($type_code) {
            case 'hair':
                $this->crud->addColumn([
                    'name' => 'thumbnail',
                    'label' => "Thumbnail",
                    'type' => 'image',
                    'background-color' => '#ff8100',
                    'max-width' => '300px',
                    'max-height' => '262px',
                ]);
                $this->crud->addColumn([
                    'name' => 'hair_items', // The db column name
                    'label' => "Hairs", // Table column heading
                    'type' => 'hair_items',
                    'background-color' => '#343333',
                    'width' => '50px'
                ]);
                break;
            case 'makeup':
                $this->crud->addColumn([
                    'name' => 'makeup_items', // The db column name
                    'label' => "Makeups", // Table column heading
                    'type' => 'makeup_items',
                    'background-color' => '#343333',
                    'width' => '40px'
                ]);
                break;

            default:
                $this->crud->addColumn([
                    'name' => 'image',
                    'label' => "Front Layer",
                    'type' => 'image',
                    'max-width' => '300px',
                    'max-height' => '262px',
                    'background-color' => '#343333'
                ]);
                $this->crud->addColumn([
                    'name' => 'left_image',
                    'label' => "Left Layer",
                    'type' => 'image',
                    'max-width' => '300px',
                    'max-height' => '262px',
                    'background-color' => '#343333'
                ]);
                $this->crud->addColumn([
                    'name' => 'right_image',
                    'label' => "Right Layer",
                    'type' => 'image',
                    'max-width' => '300px',
                    'max-height' => '262px',
                    'background-color' => '#343333'
                ]);
                $this->crud->addColumn([
                    'name' => 'back_image',
                    'label' => "Back Layer",
                    'type' => 'image',
                    'max-width' => '300px',
                    'max-height' => '262px',
                    'background-color' => '#343333',
                ]);
                $this->crud->addColumn([
                    'name' => 'mid_image',
                    'label' => "Mid Layer",
                    'type' => 'image',
                    'max-width' => '300px',
                    'max-height' => '262px',
                    'background-color' => '#343333',
                ]);
                $this->crud->addColumn([
                    'name' => 'thumbnail',
                    'label' => "Thumbnail",
                    'type' => 'image',
                    'background-color' => '#ff8100',
                    'max-width' => '300px',
                    'max-height' => '262px',
                ]);
        }

        $this->crud->addColumn([
            'name' => 'price_currency',
            'label' => "Price",
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'group_level_item_id',
            'type' => 'select',
            'label' => 'Group Level',
            'entity' => 'grouplevelitem',
            'attribute' => 'name',
            'model' => \App\Models\GroupLevelItem::class,
        ]);
//        $this->crud->addColumn([
//            'label' => "Models",
//            'type' => 'items.list',
//            'name' => 'models',
//            'entity' => 'models',
//            'attribute' => 'name',
//            'model' => \App\Models\CharacterModel::class,
//        ]);
        $this->crud->addColumn([
            'label' => "Type",
            'type' => "select",
            'name' => 'type_id',
            'entity' => 'type',
            'attribute' => "name",
            'model' => \App\Models\Type::class
        ]);
//        $this->crud->addColumn([
//            'name' => 'vip',
//            'type' => 'custom.check'
//        ]);
//        $this->crud->addColumn([
//            'name' => 'elo_score',
//        ]);
        $this->crud->addColumn([
            'name' => 'list_styles', // The db column name
            'label' => "Styles", // Table column heading
            'type' => 'view',
            'view' => 'vendor.backpack.crud.columns.list_custom', // or path to blade file
            'orderable'  => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                /* @todo: cần có logic để chọn được filter theo Style nào, mặc định là 1 (Đẹp) */
                return $query->leftJoin('item_style', function ($join) {
                    $join->on('items.id', '=', 'item_style.item_id');
                    $join->where('item_style.style_id', '=', 1);
                })->orderBy('score', $columnDirection)
                    ->groupBy('items.id')
                    ->select(['items.*', DB::raw('SUM(item_style.score) as score')]);
            }
        ]);
        $this->crud->addColumn([
            'label' => "Topics",
            'type' => 'items.list',
            'name' => 'topics',
            'entity' => 'topics',
            'attribute' => 'name',
            'model' => \App\Models\Topic::class,
        ]);
        $this->crud->addColumn([
            'name' => 'in_local',
            'type' => 'custom.check'
        ]);
        $this->crud->addColumn([
            'name' => 'ready_to_topic',
            'type' => 'custom.check'
        ]);

        $this->crud->addColumn([
            // n-n relationship
            'label' => "Color", // Table column heading
            'type' => "items.list",
            'name' => 'colors', // the method that defines the relationship in your Model
            'entity' => 'colors', // the method that defines the relationship in your Model
            'attribute' => "name", // foreign key attribute that is shown to user
            'model' => Color::class, // foreign key model
        ]);
        $this->crud->addColumn([
            'label' => 'Collections',
            'type' => 'items.list',
            'name' => 'collections',
            'entity' => 'collections',
            'attribute' => 'name',
            'model' => Collection::class,
            'pivot' => true,
        ]);
        $this->crud->addColumn([
            'label' => 'Patterns',
            'type' => 'items.list',
            'name' => 'patterns',
            'entity' => 'patterns',
            'attribute' => 'name',
            'model' => Pattern::class,
            'pivot' => true,
        ]);
        $this->crud->addColumn([
            'label' => 'Materials',
            'type' => 'items.list',
            'name' => 'materials',
            'entity' => 'materials',
            'attribute' => 'name',
            'model' => Material::class,
            'pivot' => true,
        ]);

        $this->crud->addColumn([
            'label' => 'Tags',
            'type' => 'select_multiple',
            'name' => 'tags',
            'entity' => 'tags',
            'attribute' => 'name',
            'model' => Tag::class,
            'pivot' => true,
        ]);

        $this->crud->addColumn([
            // 1-n relationship
            'label' => "Brand", // Table column heading
            'type' => "select",
            'name' => 'brand_id', // the column that contains the ID of that connected entity;
            'entity' => 'brand', // the method that defines the relationship in your Model
            'attribute' => "name", // foreign key attribute that is shown to user
            'model' => Brand::class, // foreign key model
        ]);

        /*=========Where=========*/
        $arr_id_checking = [];

        if($type_code){
            $type = Type::where('code', $type_code)->first();
            $type_id = $type->id;
            $this->crud->addClause('where', 'type_id', $type_id);
        }else{
            $arr_type_code = ['hair', 'makeup'];
            $arr_type_id = Type::whereIn('code', $arr_type_code)->get(['id'])->pluck('id')->toArray();
            $this->crud->addClause('whereNotIn', 'type_id', $arr_type_id);
        }
        /*=========Where=========*/



    }
    protected function setupCreateOperation()
    {
        /*======Filed=======*/
        CRUD::addField([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text', /** + .blade.php  ../fields/.... */
            'tab' => 'Information',
        ]);
        CRUD::addField([ // image
            'label' => "Base Image",
            'name' => "image",
            'type' => 'browse',
            'tab' => 'Information',
        ]);
        CRUD::addField([ // image
            'label' => "Left Image",
            'name' => "left_image",
            'type' => 'browse',
            'tab' => 'Information',
        ]);
        CRUD::addField([ // image
            'label' => "Right Image",
            'name' => "right_image",
            'type' => 'browse',
            'tab' => 'Information',
        ]);
        CRUD::addField([ // image
            'label' => "Back Image",
            'name' => "back_image",
            'type' => 'browse',
            'tab' => 'Information',
        ]);
        CRUD::addField([ // image
            'label' => "Thumbnail",
            'name' => "thumbnail",
            'type' => 'browse',
            'tab' => 'Information',
        ]);
        CRUD::addField([
            'name' => 'group_level_item_id',
            'type' => 'select2',
            // the method that defines the relationship in your Model
            'entity' => 'grouplevelitem', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\GroupLevelItem::class, // foreign key model
            'text' => "- Select -",
            'tab' => 'Information',
            'label' => 'Group Level',
        ]);

        CRUD::addField(
            [ // image
                'label' => "Price",
                'name' => "price",
                'type' => 'number',
                'tab' => 'Information',
            ]);
        CRUD::addField(
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
                'tab' => 'Information',
            ]);



        $item = $this->crud->getCurrentEntry();
        $type_code = $item->type->code;

        switch ($type_code) {
            case 'hair':
                $this->crud->addFields([
                    [
                        'label' => "[Back Layer] Pos X",
                        'name' => "back_image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "back_image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);

                $this->crud->addFields([
                    [
                        'label' => "[Mid Layer] Pos X",
                        'name' => "mid_image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "mid_image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);

                $this->crud->addFields([
                    [
                        'label' => "[Front Layer] Pos X",
                        'name' => "image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);


                // CRUD::addField(
                //     [ 
                //         'label' => "Hair Color",
                //         'name' => "hair_colors",
                //         'type' => 'custom.hair_colors',
                //         'columns' => [
                //             'key' => 'Price',
                //             'value' => 'Short Description',
                //         ],
                //         'value' => '[{"key": "Color 1"},{"key": "Color 2"}]',
                //         'tab' => 'Hair Color',
                //     ]);

                    $this->crud->addField([
                        'name' => 'hair_colors', 
                        'label' => 'Hair Color',
                        'type' => 'custom.hair_colors',
                        'new_item_label'  => 'Add',
                        'tab' => 'Hair Color',
                        'fields' => [
                            
                            [
                                'name'        => 'currency',
                                'label'       => 'Currency',
                                'type'        => 'select_from_array',
                                'options'     => [
                                    1 => "Soft",
                                    2 => "Hard",
                                    3 => "Ad"
                                ],
                                'default' => 1,
                                'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                            ],
                            [
                                'name'    => 'price',
                                'type'    => 'number',
                                'label'   => 'Price',
                                'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                            ],
            
                        ]
                    ]);


                break;

            case 'makeup':
                $this->crud->addFields([
                    [
                        'label' => "Pos X",
                        'name' => "image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);
                break;

            default:
                $this->crud->addFields([
                    [
                        'label' => "[Back Layer] Pos X",
                        'name' => "back_image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "back_image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);
                $this->crud->addFields([
                    [
                        'label' => "[Right Layer] Pos X",
                        'name' => "right_image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "right_image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);

                $this->crud->addFields([
                    [
                        'label' => "[Left Layer] Pos X",
                        'name' => "left_image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "left_image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);
                $this->crud->addFields([
                    [
                        'label' => "[Mid Layer] Pos X",
                        'name' => "mid_image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "mid_image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);

                $this->crud->addFields([
                    [
                        'label' => "[Front Layer] Pos X",
                        'name' => "image_pos_x",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ],
                    [
                        'label' => "Pos Y",
                        'name' => "image_pos_y",
                        'type' => 'number',
                        'attributes' => [
                            'step' => "0.01",
                        ],
                        'wrapperAttributes' => ['class' => 'form-group col-md-6'],
                        'tab' => 'Position',
                    ]
                ]);
        }


//        CRUD::addField([ // image
//            'label' => "Preview",
//            'name' => "preview",
//            'fake' => 'true',
//            'type' => 'preview',
//            'tab' => 'Position'
//        ]);

        CRUD::addField([
            'label' => "Topics",
            'type' => 'select2_multiple',
            'name' => 'topics',
            // the method that defines the relationship in your Model
            'entity' => 'topics', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\Topic::class, // foreign key model
            'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
            'text' => "- Select -",
            'select_all' => true,
            'tab' => 'Information',
        ]);

//        CRUD::addField([
//            'label' => "Models",
//            'type' => 'select2_multiple',
//            'name' => 'models',
//            // the method that defines the relationship in your Model
//            'entity' => 'models', // the method that defines the relationship in your Model
//            'attribute' => 'name', // foreign key attribute that is shown to user
//            'model' => \App\Models\CharacterModel::class, // foreign key model
//            'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
//            'text' => "- Select -",
//            'select_all' => true,
//            'tab' => 'Information',
//        ]);

        CRUD::addField([
            'label' => "Type",
            'type' => 'select2',
            'name' => 'type_id',
            // the method that defines the relationship in your Model
            'entity' => 'type', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\Type::class, // foreign key model,
            'tab' => 'Information',
        ]);

        CRUD::addField([
            'label' => 'In Local',
            'type' => 'checkbox',
            'name' => 'in_local',
            'tab' => 'Information',
        ]);
        CRUD::addField([
            'label' => 'Ready To Topic',
            'type' => 'checkbox',
            'name' => 'ready_to_topic',
            'tab' => 'Information',
        ]);
        $this->crud->addField([
            'name' => 'vip',
            'label' => 'Vip',
            'type' => 'checkbox',
            'tab' => 'Information',
        ]);

        $this->crud->addField([
            'label' => 'Colors',
            'type' => 'select2_multiple',
            'name' => 'colors',
            'entity' => 'colors',
            'attribute' => 'name',
            'model' => Color::class,
            'pivot' => true,
            'tab' => 'Information',

        ]);
        $this->crud->addField([
            'label' => 'Collections',
            'type' => 'select2_multiple',
            'name' => 'collections',
            'entity' => 'collections',
            'attribute' => 'name',
            'model' => Collection::class,
            'pivot' => true,
            'tab' => 'Information',
        ]);
        $this->crud->addField([
            'label' => 'Patterns',
            'type' => 'select2_multiple',
            'name' => 'patterns',
            'entity' => 'patterns',
            'attribute' => 'name',
            'model' => Pattern::class,
            'pivot' => true,
            'tab' => 'Information',
        ]);
        $this->crud->addField([
            'label' => 'Materials',
            'type' => 'select2_multiple',
            'name' => 'materials',
            'entity' => 'materials',
            'attribute' => 'name',
            'model' => Material::class,
            'pivot' => true,
            'tab' => 'Information',
        ]);
        $this->crud->addField([
            'label' => 'Tags',
            'type' => 'select2_multiple',
            'name' => 'tags',
            'entity' => 'tags',
            'attribute' => 'name',
            'model' => Tag::class,
            'pivot' => true,
            'tab' => 'Information',
        ]);

        $this->crud->addField([   // select2_from_array
            'name'        => 'brand_id',
            'label'       => "Brand",
            'type'        => 'select',
            'entity'      => 'brand',
            'attribute' => 'name',
            'model' => Brand::class,
            'tab' => 'Information',

        ]);


        

        // add asterisk for fields that are required in ItemRequest
        //CRUD::setRequiredFields(StoreRequest::class, 'create');
        //CRUD::setRequiredFields(UpdateRequest::class, 'edit');

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function store()
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
        // Sync Styles Relationships
        $this->syncStyles($request);
        // Sync Topics and types
        $this->syncTopics($request);

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * @param Request $request
     */
    private function syncStyles(Request $request): void
    {
        DB::beginTransaction();
        try {
            $item = $this->data['entry'];
            $styles = $request->get('styles');
            // Delete previous styles in pivot table
            $item->styles()->detach();
            if ($styles) {
                $stylesFomarted = array_map(function ($style) {
                    return [
                        'style_id' => $style['id'],
                        'score' => $style['score'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }, $styles);
                $item->styles()->sync($stylesFomarted);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            \Alert::error('Thêm style thất bại')->flash();
        }
    }

    private function syncTopics(Request $request): void
    {
        DB::beginTransaction();
        try {
            $item = $this->data['entry'];
            $topics = $request->get('topics');
            TopicDetailItem::where('item_id', $item->id)->delete();
            // If user select topics
            if ($topics) {
                foreach ($topics as $index => $topic) {
                    if (isset($topic['type_ids'])) {
                        foreach ($topic['type_ids'] as $index2 => $typeID) {
                            $topicDetail = TopicDetail::firstOrCreate([
                                "topic_id" => $topic['topic_id'],
                                "type_id" => $typeID,
                            ]);
                            $topicDetail->topicDetailItems()->create(['item_id' => $item->id]);
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            \Alert::error('Thêm topic thất bại' . $exception->getMessage())->flash();
        }
    }

    public function update(UpdateRequest $request)
    {

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
        $this->syncTopics($request);

        return $this->crud->performSaveAction($item->getKey());
    }

    public function clone($id)
    {
        CRUD::hasAccessOrFail('clone');
        CRUD::setOperation('clone');

        try {
            DB::beginTransaction();
            $model = $this->crud->model->findOrFail($id);
            $clonedEntry = $model->replicate();
            $clonedEntry->push();

            $topics = $model->topics->pluck('id')->toArray();
            if(count($topics)) {
                $clonedEntry->topics()->attach($topics);
            }
            $styles = $model->styles;
            if(count($styles)) {
                foreach ($styles as $style) {
                    $clonedEntry->styles()->attach($style->id, ['score' => $style->pivot->score]);
                }
            }

            DB::commit();
            return (string) $clonedEntry->push();

        } catch (\Exception $exception) {

            DB::rollback();
            throw $exception;
        }
    }

    public function bulkAssign()
    {
        try {
            DB::beginTransaction();

            $this->crud->hasAccessOrFail('update');

            $items = $this->request->input('entries');
            $topics = $this->request->input('topics');


            foreach ($items as $key => $id) {
                if ($item = $this->crud->model->find($id)) {
                    /** @var Item $item */

                    $existedTopics = $item->topics()->pluck('topics.id')->toArray();
                    $neededIds = array_diff($topics, $existedTopics);
                    if(count($neededIds)) {
                        $item->topics()->attach($neededIds);
                    }
                }
            }

            DB::commit();
            return ;
        }
        catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }

    }

    public function bulkSetLocal()
    {
        try {
            DB::beginTransaction();

            $this->crud->hasAccessOrFail('update');
            $items = $this->request->input('entries');
            $inLocal = $this->request->input('in_local');

            foreach ($items as $key => $id) {
                if ($item = $this->crud->model->find($id)) {
                    $item->in_local = $inLocal;
                    $item->save();
                }
            }

            DB::commit();
            return ;
        }
        catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }

    }

    protected function setupBulkAssignRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/bulk-assign', [
            'as'        => $routeName.'.bulkAssign',
            'uses'      => $controller.'@bulkAssign',
            'operation' => 'bulkAssign',
        ]);
    }

    protected function setupBulkSetLocalRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/bulk-inlocal', [
            'as'        => $routeName.'.bulkSetLocal',
            'uses'      => $controller.'@bulkSetLocal',
            'operation' => 'bulkSetLocal',
        ]);
    }

    protected function setupBulkAssignDefaults()
    {
        $this->crud->allowAccess('bulkAssign');

        $this->crud->operation('bulkAssign', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->enableBulkActions();
            $this->crud->addButton('top1', 'bulk_assign', 'view', 'crud::buttons.bulk_assign', 'end');
        });
    }

    protected function setupBulkSetLocalDefaults()
    {
        $this->crud->allowAccess('bulkSetLocal');
        $this->crud->operation('bulkSetLocal', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->enableBulkActions();
            $this->crud->addButton('top1', 'bulk_inlocal', 'view', 'crud::buttons.bulk_inlocal', 'end');

        });
    }
}
