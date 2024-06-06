<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CharacterModelRequest;
use App\Models\Type;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CharacterModelCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\CharacterModel');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/model');
        $this->crud->setEntityNameStrings('model', 'models');
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->addColumns([
            ['name' => 'name'],
            [
                'name' => 'thumb',
                'type' => 'image',
            ],
            [
                'name' => 'image',
                'type' => 'image',
                'width' => '100px'
            ],
            [
                'name' => 'sort_order',
                'type' => 'number',
            ],
            [
                'name' => 'pos_x',
                'type' => 'numeric',
            ],
            [
                'name' => 'pos_y',
                'type' => 'numeric',
            ],
        ]);


    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(CharacterModelRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->addFields([
            ['name' => 'name'],
            [
                'name' => 'thumb',
                'type' => 'browse',
            ],
            [
                'name' => 'image',
                'type' => 'browse',
            ],
            [
                'name' => 'pos_x',
                'type' => 'number',
                'attributes' => [
                    'step' => "0.01",
                ],
            ],
            [
                'name' => 'pos_y',
                'type' => 'number',
                'attributes' => [
                    'step' => "0.01",
                ],
            ],

            [
                'name' => 'sort_order',
                'type' => 'number',
            ],
            [   // repeatable
                'name'  => 'default_items',
                'label' => 'Default Items',
                'type'  => 'repeatable',
                'fields' => [
                    [
                        'name'    => 'type_id',
                        'type'    => 'select_from_array',
                        'label'   => 'Type',
                        'options' => Type::all()->pluck('name', 'id')->toArray(),
                        'parent' => 'default_items',
                    ],
                    [
                        'name'    => 'image',
                        'type'    => 'browse',
                        'label'   => 'Image',
                    ],
                    [
                        'name'    => 'pos_x',
                        'type'    => 'text',
                        'label'   => 'Pos X',
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ],
                    [
                        'name'    => 'pos_y',
                        'type'    => 'text',
                        'label'   => 'Pos X',
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ],
                ],

                // optional
                'new_item_label'  => 'Add Item', // customize the text of the button
            ],
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
