<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use DB;
use App\Models\Version;

/**
 * Class PositionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LevelCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    //use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Level');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/level');
        $this->crud->setEntityNameStrings('level', 'Level');
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'level',
            'label' => "Level",
            'type' => 'number'
        ]);
        $this->crud->addColumn([
            'name' => 'exp',
            'label' => "Exp",
            'type' => 'number'
        ]);
        $this->crud->addColumn([
            'name' => 'rewards',
            'label' => "Rewards",
            'type' => 'text',
            'limit' => 100
        ]);

        $this->crud->orderBy('level', 'DESC');
        
    }

    protected function setupCreateOperation()
    {


        $this->crud->addField([
            'name' => 'level',
            'label' => "Level",
            'type' => 'number'
        ]);
        $this->crud->addField([
            'name' => 'exp',
            'label' => "Exp",
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

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        $this->updateFileLevelJson();

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
                            $this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        $this->updateFileLevelJson();

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function updateFileLevelJson(){
        $levels = \App\Models\Level::orderBy('level', 'ASC')->get();
        $data = [];
        foreach($levels as $value){
            $rewards = json_decode($value->rewards);
            $new_rewards = [];
            foreach($rewards as $reward){
                $new_rewards[] = [
                    'type' => $reward->type,
                    'value' => $reward->type == 'ITEM' ? (int)$reward->item_id : (int)$reward->value
                ];
            }
            $data[] = [
                'level' => $value->level,
                'exp' => $value->exp,
                'rewards' => $new_rewards
            ];
        }

        file_put_contents(public_path('export/level.json'), json_encode($data));

        $version = Version::where('key', 'levelsData')->count();
        if($version > 0){
            Version::where('key', 'levelsData')->increment('version');
        }else{
            Version::create(['key' => 'levelsData', 'version' => 1]);
        }
    }
}
