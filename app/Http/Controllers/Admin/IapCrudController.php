<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PositionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class IapCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Iap');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/iap');
        $this->crud->setEntityNameStrings('iap', 'Iap');
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->setFromDb();
    }

    protected function setupCreateOperation()
    {
        $this->crud->addField([
            'name' => 'name',
            'label' => "Name",
            'type' => 'text',
        ]);
        $this->crud->addField([
            'name' => 'product_id',
            'label' => "Product ID",
            'type' => 'text',
        ]);
        $this->crud->addField([
            'name' => 'value',
            'label' => "Value",
            'type' => 'number',
        ]);
        $this->crud->addField([
            'name' => 'type',
            'label' => "Type",
            'type'        => 'select_from_array',
            'options'     => ['ticket' => 'Ticket', 'hard' => 'Hard', 'soft' => 'Soft'],
            'allows_null' => true,
        ]);
        $this->crud->addField([
            'name' => 'price',
            'label' => "Price",
            'type' => 'number',
        ]);
        $this->crud->addField([
            'name' => 'old_price',
            'label' => "Old Price",
            'type' => 'number',
        ]);

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
