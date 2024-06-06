<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\GroupLevelItemRequest as StoreRequest;
use App\Http\Requests\GroupLevelItemRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use DB;

/**
 * Class GroupLevelItemCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class GroupLevelItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\GroupLevelItem');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/grouplevelitem');
        $this->crud->setEntityNameStrings('grouplevelitem', 'group_level_items');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->setFromDb();

        $this->crud->addField([
            'name' => 'items_fake',
            'label' => 'Items',
            'type' => 'datatable',
            'tab' => 'RelationShip'
        ]);

        // add asterisk for fields that are required in GroupLevelItemRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // insert item in the db
        $proModel = $this->crud->create($this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $proModel;

        $selectedIds = $request->request->get('selectedIds');
        if(isset($selectedIds) && $selectedIds) {
            $selectedIds = explode(',', $selectedIds);
            if(count($selectedIds)) {
                $items = Item::all()->whereIn('id', $selectedIds);
                foreach ($items as $item) {
                    $item->group_level_item_id = $this->crud->entry->id;
                    $item->save();
                }
            }
        }

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($proModel->getKey());
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // update the row in the db
        $proModel = $this->crud->update($request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $proModel;

        foreach ($this->crud->entry->items as $item) {
            $item->group_level_item_id = 0;
            $item->save();
        }

        $selectedIds = $request->request->get('selectedIds');
        if($selectedIds) {
            $selectedIds = explode(',', $selectedIds);

            if(count($selectedIds)) {
                $items = Item::all()->whereIn('id', $selectedIds);
                foreach ($items as $item) {
                    $item->group_level_item_id = $this->crud->entry->id;
                    $item->save();
                }
            }
        }
        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($proModel->getKey());
    }
}
