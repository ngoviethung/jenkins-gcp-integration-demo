<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SkinRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SkinCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SkinCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Skin');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/skin');
        $this->crud->setEntityNameStrings('skin', 'skins');
    }

    protected function setupListOperation()
    {

        $this->crud->addColumn([
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'thumbnail', // The db column name
            'label' => "Thumbnail", // Table column heading
            'type' => 'image',
            'max-width' => '300px'
        ]);
        $this->crud->addColumn([
            'name' => 'body_image', // The db column name
            'label' => "Body Image", // Table column heading
            'type' => 'image',
            'max-width' => '50px'

        ]);
        $this->crud->addColumn([
            'name' => 'left_hand_image', // The db column name
            'label' => "Left Hand Image", // Table column heading
            'type' => 'image',
            'max-width' => '50px'

        ]);
        $this->crud->addColumn([
            'name' => 'right_hand_image', // The db column name
            'label' => "Right Hand Image", // Table column heading
            'type' => 'image',
            'max-width' => '50px'

        ]);

    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(SkinRequest::class);

        $this->crud->addField([
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text',
        ]);
        $this->crud->addField([
            'name' => 'thumbnail', // The db column name
            'label' => "Thumbnail", // Table column heading
            'type' => 'image',
            'upload' => true,
        ]);
        $this->crud->addField([
            'name' => 'right_hand_image', // The db column name
            'label' => "Right Hand Image", // Table column heading
            'type' => 'image',
            'upload' => true,
            'wrapperAttributes' => ['class' => 'form-group col-md-4'],
        ]);
        $this->crud->addField([
            'name' => 'body_image', // The db column name
            'label' => "Body Image", // Table column heading
            'type' => 'image',
            'upload' => true,
            'wrapperAttributes' => ['class' => 'form-group col-md-4'],
        ]);
        $this->crud->addField([
            'name' => 'left_hand_image', // The db column name
            'label' => "Left Hand Image", // Table column heading
            'type' => 'image',
            'upload' => true,
            'wrapperAttributes' => ['class' => 'form-group col-md-4'],
        ]);

        $this->crud->removeField('code');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
