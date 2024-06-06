<?php

namespace App\Http\Controllers\Admin;

use App\Models\GroupLevelType;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Admin\Type\TypeRequest as StoreRequest;
use App\Http\Requests\Admin\Type\TypeRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class TypeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class TypeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Type');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/type');
        $this->crud->setEntityNameStrings('type', 'types');

        $groupLevelTypes = GroupLevelType::all()->pluck('name', 'id')->toArray();
        $this->crud->addFilter([ // select2_ajax filter
            'name' => 'group_level_type_id',
            'type' => 'select2',
            'label' => 'Group Level',
            'placeholder' => 'Pick a group level'
        ], function () use ($groupLevelTypes) {
            return $groupLevelTypes;
        },
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'group_level_type_id', $value);
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

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => "Name",
                'type' => 'string',
            ],
            [
                'name' => 'icon',
                'label' => "Icon",
                'type' => 'image',
                'max-height' => '150px',
                'background-color' => '#c3bba3'
            ],
            [
                'name' => 'icon_selected',
                'label' => "Icon Selected",
                'type' => 'image',
                'max-height' => '150px',
                'background-color' => '#c3bba3'
            ],
            [
                'name' => 'category',
                'label' => "Category",
                'type' => 'string',
            ], [
                'name' => 'parent_id',
                'type' => 'select',
                'label' => 'Parent',
                // the method that defines the relationship in your Model
                'entity' => 'parent', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => \App\Models\Type::class, // foreign key model
                'text' => "- Select -",
            ],
            [
                'name' => 'group_level_type_id',
                'type' => 'select',
                'label' => 'Group Level',
                // the method that defines the relationship in your Model
                'entity' => 'groupleveltype', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model' => \App\Models\GroupLevelType::class, // foreign key model
            ],
            [
                'name' => 'order',
                'label' => "Order Layer",
                'type' => 'number',
            ],
            [
                'name' => 'order_num',
                'label' => "Order_num",
                'type' => 'number',
            ],
            [
                'name' => 'vip',
                'label' => "Vip",
                'type' => 'custom.check',

            ],
            [
                'name' => 'pos_x',
                'label' => "Pos X",
                'type' => 'numeric',
            ],
            [
                'name' => 'pos_y',
                'label' => "Pos Y",
                'type' => 'numeric',
            ],

        ]);
        //Add fields
        $this->crud->addField([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text_readonly',

        ]);

        $this->crud->addField([
            'name' => 'order_num',
            'label' => 'Order Num',

        ]);
        $this->crud->addField([ // image
            'label' => "Icon",
            'name' => "icon",
            'type' => 'browse'
        ]);

        $this->crud->addField([ // image
            'label' => "Icon Selected",
            'name' => "icon_selected",
            'type' => 'browse'
        ]);

        $this->crud->addField([ // image
            'name' => 'category',
            'label' => "Category",
            'type' => 'select_from_array',
            'options' => ['dress_up' => 'DressUp', 'make_up' => 'MakeUp', 'style' => 'Style'],
            'allows_null' => false,
            'default' => 'dress_up',
        ]);

        $this->crud->addField([
            'name' => 'group_level_type_id',
            'type' => 'select2',
            'label' => 'Group Level',
            // the method that defines the relationship in your Model
            'entity' => 'groupleveltype', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\GroupLevelType::class, // foreign key model
            'text' => "- Select -",
        ]);

        $this->crud->addField([
            'name' => 'parent_id',
            'type' => 'select2',
            'label' => 'Parent',
            // the method that defines the relationship in your Model
            'entity' => 'parent', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\Type::class, // foreign key model
            'text' => "- Select -",
        ]);

        $this->crud->addField([
            'name' => 'position_id',
            'type' => 'select2',
            'label' => 'Position',
            // the method that defines the relationship in your Model
            'entity' => 'position', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => \App\Models\Position::class, // foreign key model
            'text' => "- Select -",
        ]);


        $this->crud->addField([
            'name' => 'order',
            'label' => 'Order Layer',
            'type' => 'number',

        ]);

        $this->crud->addField([ // image
            'label' => "Pos X",
            'name' => "pos_x",
            'type' => 'number',
            'attributes' => [
                'step' => "0.01",
            ],
            'tab' => 'Pos',
        ]);
        $this->crud->addField([ // image
            'label' => "Pos Y",
            'name' => "pos_y",
            'type' => 'number',
            'attributes' => [
                'step' => "0.01",
            ],
            'tab' => 'Pos',
        ]);
        $this->crud->addField([
            'name' => 'vip',
            'label' => 'Vip',
            'type' => 'checkbox',
        ]);


        // add asterisk for fields that are required in TypeRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }
}
