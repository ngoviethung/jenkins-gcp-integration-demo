<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation

use Backpack\CRUD\CrudPanel;

/**
 * Class StyleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class DownloadExportCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
//    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
//    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
//    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Export');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/download-export');
        $this->crud->setEntityNameStrings('Files', 'Files Export');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns

        $this->crud->addColumn([
            'name' => 'file', // The db column name
            'label' => "File", // Table column heading
            'type'  => 'model_function',
            'function_name' => 'getDownloadLink', // the method in your Model
            'limit' => 1000
        ]);

        $this->crud->setFromDb();

        $this->crud->addColumn([
            'name' => 'created_at', // The db column name
            'label' => "Request At", // Table column heading
            'type'  => 'text',
        ]);

        $this->crud->addColumn([
            'name' => 'updated_at', // The db column name
            'label' => "Created File At", // Table column heading
            'type'  => 'text',
        ]);

        $this->crud->addColumn([
            'name' => 'completion_time', // The db column name
            'label' => "Completion Time", // Table column heading
            'type'  => 'model_function',
            'function_name' => 'getCompletionTime'
        ]);


        // add asterisk for fields that are required in StyleRequest

    }
}
