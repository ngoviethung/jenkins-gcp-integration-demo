<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PositionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ShopCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Shop');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/shop');
        $this->crud->setEntityNameStrings('shop', 'Shop');
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'metadata',
            'label' => "Shop Data(json)",
            'type' => 'text',
            'limit' => 100
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->addField([
            'name' => 'metadata',
            'label' => "Shop Data(json)",
            'type' => 'textarea',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
