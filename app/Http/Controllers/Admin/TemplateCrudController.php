<?php

namespace App\Http\Controllers\Admin;


use App\Http\Requests\Admin\Template\TemplateRequest as StoreRequest;
use App\Http\Requests\Admin\Template\TemplateRequest as UpdateRequest;

use App\Models\Type;
use App\Models\Topic;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Request;

class TemplateCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Template');
        $this->crud->setRoute(config('backpack.base.route_prefix').'/template');
        $this->crud->setEntityNameStrings('Template', 'Templates');
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::list_template', $this->data);
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'label' => "Name",
            'type' => 'text',
            'name' => 'name',
        ]);


    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(StoreRequest::class);

        $this->crud->addField([
            'label' => "Name",
            'type' => 'text',
            'name' => 'name',
        ]);
        $this->crud->addField([
            'label' => "Background",
            'type' => 'background',
            'name' => 'background',
        ]);

        $topics = Topic::get()->pluck('name', 'id')->toArray();
        $this->crud->addField([
            'label' => "Topic",
            'type' => 'select2_from_array',
            'name' => 'topic_id',
            'options' => $topics,
            'default' => 0,
            'allows_null' => true,
        ]);

        $materials = \App\Models\Material::get()->pluck('name', 'id')->toArray();
        $this->crud->addField([
            'label' => "Material",
            'type' => 'select2_from_array',
            'name' => 'material_id',
            'options' => $materials,
            'default' => 0,
            'allows_null' => true,
        ]);
        $colors = \App\Models\Color::get()->pluck('name', 'id')->toArray();
        $this->crud->addField([
            'label' => "Color",
            'type' => 'select2_from_array',
            'name' => 'color_id',
            'options' => $colors,
            'default' => 0,
            'allows_null' => true,
        ]);
        $patterns = \App\Models\Pattern::get()->pluck('name', 'id')->toArray();
        $this->crud->addField([
            'label' => "Pattern",
            'type' => 'select2_from_array',
            'name' => 'pattern_id',
            'options' => $patterns,
            'default' => 0,
            'allows_null' => true,
        ]);

        $skins = \App\Models\Skin::get()->pluck('name', 'id')->toArray();
        $this->crud->addField([
            'label' => "Skin",
            'type' => 'select_from_array',
            'name' => 'model',
            'options' => $skins,
            'allows_null' => false,
        ]);


        $this->crud->addField([
            'label' => "Item",
            'type' => 'list_item',
            'name' => 'item_id',

        ]);


//        $this->crud->addField([
//            'label' => "Model",
//            'type' => 'model',
//            'name' => 'model',
//        ]);

        $this->crud->addField([
            'label' => 'Admin',
            'type' => 'hidden',
            'name' => 'admin_id',
            'value' => backpack_user()->id

        ]);
        $this->crud->addField([
            'label' => 'Item Cheking',
            'type' => 'hidden',
            'name' => 'item_checking_id',
        ]);


        //$this->crud->setFromDb();


    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::create_template', $this->data);
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::edit_template', $this->data);
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        $data_update = $this->crud->getStrippedSaveRequest();
        $data_update['file_zip'] = NULL;
        $data_update['template'] = NULL;

        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()), $data_update);

        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

}
