<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PositionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ConfigCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Config');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/config');
        $this->crud->setEntityNameStrings('config', 'Config');
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'metadata',
            'label' => "Configs",
            'type' => 'table',
            'columns'         => [
                'key'  => 'Key',
                'value'  => 'Value',
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->addField([
            'name' => 'metadata',
            'label' => "Configs",
            'type' => 'table',
            'entity_singular' => 'option', // used on the "Add X" button
            'columns'         => [
                'key'  => 'Key',
                'value'  => 'Value',
            ],
            'min' => 1,
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
