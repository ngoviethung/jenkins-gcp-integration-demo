<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Challenge\StoreRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use DB;
// VALIDATION: change the requests to match your own file names if you need form validation

use Backpack\CRUD\CrudPanel;

/**
 * Class StyleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ChallengeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    //use \App\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Infomationrmation
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Challenge');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/challenges');
        $this->crud->setEntityNameStrings('Challenge', 'Challenges');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns


        //$this->crud->setFromDb();

        // add asterisk for fields that are required in StyleRequest

    }
    protected function setupListOperation()
    {

        $this->crud->addColumn([
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'cover', // The db column name
            'label' => "Cover", // Table column heading
            'type' => 'image',
            'max-width' => '100px'
        ]);
        $this->crud->addColumn([
            'name' => 'background', // The db column name
            'label' => "Background", // Table column heading
            'type' => 'image',
            'max-width' => '100px'
        ]);
        
        $this->crud->addColumn([
            'name' => 'short_description', // The db column name
            'label' => "Short Desc", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'long_description', // The db column name
            'label' => "Long Desc", // Table column heading
            'type' => 'textarea',
        ]);
        $this->crud->addColumn([
            'name' => 'start_time', // The db column name
            'label' => "Start Time", // Table column heading
            'type' => 'datetime',
        ]);
        $this->crud->addColumn([
            'name' => 'end_time', // The db column name
            'label' => "End Time", // Table column heading
            'type' => 'datetime',
        ]);
        $this->crud->addColumn([
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'tag', // The db column name
            'label' => "Tag", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'max_unworn_value',
            'type' => 'number',
        ]);
        $this->crud->addColumn([
            'name' => 'entry_reward',
            'type' => 'number',
        ]);
        $this->crud->addColumn([
            'name' => 'requirement', // The db column name
            'label' => "Requirement", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'prizes', // The db column name
            'label' => "Prizes", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'dress_code', // The db column name
            'label' => "Dress Code", // Table column heading
            'type' => 'text',
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(StoreRequest::class);

        $this->crud->addField([
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'cover', // The db column name
            'label' => "Cover", // Table column heading
            'type' => 'image',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'background', // The db column name
            'label' => "Background", // Table column heading
            'type' => 'image',
            'tab' => 'Infomation'
        ]);
        
        $this->crud->addField([
            'name' => 'short_description', // The db column name
            'label' => "Short Description", // Table column heading
            'type' => 'text',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'long_description', // The db column name
            'label' => "Long Description", // Table column heading
            'type' => 'textarea',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'start_time', // The db column name
            'label' => "Start Time", // Table column heading
            'type' => 'datetime',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'end_time', // The db column name
            'label' => "End Time", // Table column heading
            'type' => 'datetime',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'tag', // The db column name
            'label' => "Tag", // Table column heading
            'type' => 'text',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'max_unworn_value',
            'type' => 'number',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'entry_reward',
            'type' => 'number',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'requirement', // The db column name
            'label' => "Requirement", // Table column heading
            'type' => 'text',
            'tab' => 'Infomation'
        ]);
        $this->crud->addField([
            'name' => 'prizes', // The db column name
            'label' => "Prizes", // Table column heading
            'type' => 'repeatable',
            'new_item_label'  => 'Add', // customize the text of the button
            'tab' => 'Prizes',
            'fields' => [ // also works as: "fields"
                [
                    'name'    => 'require_star',
                    'type'    => 'number',
                    'label'   => 'Require star',
                    'attributes' => [
                        'step' => "0.01",
                    ],
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name'    => 'type',
                    'type'    => 'select_from_array',
                    'label'   => 'Type',
                    'options'     => ['ITEM' => 'ITEM', 'HARD' => 'HARD', 'SOFT' => 'SOFT',],
                    'allows_null' => false,
                    'default'     => 'ITEM',
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name'    => 'value',
                    'type'    => 'number',
                    'label'   => 'Value',
                    'attributes' => [
                        'step' => "0.01",
                    ],
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name' => 'item_id',
                    'type' => 'select2',
                    'label' => 'Item',
                    'attribute' => 'id',
                    'entity' => '',
                    'model' => \App\Models\Item::class,
                    'options'   => (function ($query) {
                        return $query->get('id');
                    }),
                    'wrapperAttributes' => ['class' => 'item-id form-group col-md-12'],
                ],

            ]
        ]);
        $this->crud->addField([
            'name' => 'dress_code',
            'label' => "Dress Code",
            'type' => 'repeatable',
            'new_item_label'  => 'Add',
            'tab' => 'Dress Code',
            'fields' => [
                [
                    'name'    => 'name',
                    'type'    => 'text',
                    'label'   => 'Name',
                ],
                [
                    'name' => 'colors',
                    'type' => 'select2_multiple',
                    'label' => 'Colors',
                    'attribute' => 'name',
                    'model' => \App\Models\Color::class,
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name' => 'collections',
                    'type' => 'select2_multiple',
                    'label' => 'Collections',
                    'attribute' => 'name',
                    'model' => \App\Models\Collection::class,
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name' => 'patterns',
                    'type' => 'select2_multiple',
                    'label' => 'Patterns',
                    'attribute' => 'name',
                    'model' => \App\Models\Pattern::class,
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name' => 'materials',
                    'type' => 'select2_multiple',
                    'label' => 'Materials',
                    'attribute' => 'name',
                    'model' => \App\Models\Material::class,
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name' => 'brands',
                    'type' => 'select2_multiple',
                    'label' => 'Brands',
                    'attribute' => 'name',
                    'model' => \App\Models\Brand::class,
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name' => 'type_id',
                    'type' => 'select2_multiple',
                    'label' => 'Type',
                    'attribute' => 'name',
                    'model' => \App\Models\Type::class,
                    'wrapperAttributes' => ['class' => 'form-group col-md-4'],
                ],
                [
                    'name' => 'item_id',
                    'type' => 'select2_multiple',
                    'label' => 'Item',
                    'attribute' => 'id',
                    'model' => \App\Models\Item::class,
                    'options'   => (function ($query) {
                        return $query->get('id');
                    }),
                    'wrapperAttributes' => ['class' => 'item-id form-group col-md-12'],
                ],

            ]
        ]);

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
