<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use DB;


/**
 * Class PositionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VotingRewardCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\VotingReward');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/voting-reward');
        $this->crud->setEntityNameStrings('voting reward', 'Voting reward');
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'streak',
            'label' => "Streak",
            'type' => 'number'
        ]);
        $this->crud->addColumn([
            'name' => 'step',
            'label' => "Step",
            'type' => 'number'
        ]);
       
        $this->crud->addColumn([
            'name' => 'rewards',
            'label' => "Rewards",
            'type' => 'text',
            'limit' => 100
        ]);

        $this->crud->orderBy('streak', 'DESC');
        
    }

    protected function setupCreateOperation()
    {


        $this->crud->addField([
            'name' => 'streak',
            'label' => "Streak",
            'type' => 'number'
        ]);

        $this->crud->addField([
            'name' => 'step',
            'label' => "Step",
            'type' => 'number'
        ]);

        $this->crud->addField([
            'name' => 'rewards', 
            'label' => 'Rewards',
            'type' => 'repeatable',
            'new_item_label'  => 'Add',
            'fields' => [
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
    
        
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

}
