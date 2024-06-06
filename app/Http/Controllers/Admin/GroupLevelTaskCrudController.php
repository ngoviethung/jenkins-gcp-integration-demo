<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\GroupLevelTaskRequest as StoreRequest;
use App\Http\Requests\GroupLevelTaskRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;


/**
 * Class GroupLevelTaskCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class GroupLevelTaskCrudController extends CrudController
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
        $this->crud->setModel('App\Models\GroupLevelTask');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/groupleveltask');
        $this->crud->setEntityNameStrings('groupleveltask', 'group_level_tasks');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns



        // add asterisk for fields that are required in GroupLevelTaskRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');

        $this->crud->addColumn([
            'name'        => 'currency',
            'label'       => 'Currency',
            'type'        => 'radio',
            'options'     => [
                1 => "Soft",
                2 => "Hard"
            ],
        ]);

        $this->crud->setFromDb();

        $this->crud->removeField('currency');
        $this->crud->addField([   // radio
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
            'inline'      => true, // show the radios all on the same line?,
        ]);

    }

}
