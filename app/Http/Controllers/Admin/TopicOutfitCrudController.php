<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Admin\Topic\TopicRequest as StoreRequest;
use App\Http\Requests\Admin\Topic\TopicRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class TopicCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class TopicOutfitCrudController extends CrudController
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
        $this->crud->setModel('App\Models\Topic');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/topic/outfit');
        $this->crud->setEntityNameStrings('Topic Outfit', 'Topic Outfit');


    }

    protected function setupListOperation(){

        $this->crud->addColumn([
            'name' => 'name',
            'type' => 'topic_outfit.text_link',
            'limit' => 1000
        ]);
        $this->crud->addColumn([
            'name' => 'total_outfit_good',
            'label' => 'Good',
            'type' => 'topic_outfit.model_function_link',
            'function_name' => 'getTotalOutfit',
            'function_parameters' => [1],
        ]);
        $this->crud->addColumn([
            'name' => 'total_outfit_bad',
            'label' => 'Bad',
            'type' => 'topic_outfit.model_function_link',
            'function_name' => 'getTotalOutfit',
            'function_parameters' => [0],
        ]);
        $this->crud->addClause('where', 'use_in_game', 1);
        $this->crud->removeButtons(['create', 'update', 'delete']);
    }


}
