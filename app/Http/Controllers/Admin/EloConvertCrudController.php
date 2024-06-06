<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EloConvertRequest;
use App\Models\EloConvert;
use App\Models\Topic;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class EloConvertCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EloConvertCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\EloConvert');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/eloconvert');
        $this->crud->setEntityNameStrings('eloconvert', 'elo_converts');
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'json', // The db column name
            'label' => "Json", // Table column heading
            'type' => 'text',
        ]);
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->setFromDb();
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(EloConvertRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->addFields([
            [
                'label' => "Type",
                'type' => "select2",
                'name' => 'type_id',
                'entity' => 'type',
                'attribute' => "name",
                'model' => \App\Models\Type::class
            ],
            [
                'label' => "Topic",
                'type' => "select2_to_elo",
                'name' => 'topic_id',
                'entity' => 'topic',
                'attribute' => "name",
                'model' => \App\Models\Topic::class
            ],
            [
                'label' => "Min Elo Score",
                'name' => 'min_elo_score',
                'type' => 'text_readonly',
                'readonly' => true,
            ],
            [
                'label' => "Max Elo Score",
                'name' => 'max_elo_score',
                'type' => 'text_readonly',
                'readonly' => true,
            ],
            [
                'label' => "Min Score",
                'name' => 'min_score',
            ],
            [
                'label' => "Max Score",
                'name' => 'max_score',
            ],
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function getMinMax() {
        try {

            $topicId = \request()->post('topicId');
            $typeId = \request()->post('typeId');

            $topic = Topic::find($topicId);

            if(!$topic) {
                throw new \Exception('Wrong topic id');
            }

            $items = $topic->items()->where('type_id', '=', $typeId)->get();
            if(!count($items)) {
                throw new \Exception('No Items');
            }

            $maxScore = collect($items)->max('elo_score');
            $minScore = collect($items)->min('elo_score');

            return [
                'code' => 200,
                'message' => 'Successfully',
                'data' => [
                    'max_score' => $maxScore,
                    'min_score' => $minScore,
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => 500,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        /** @var EloConvert $json */
        $json = $item->publish();
        $item->json = $json;
        $item->save();

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
