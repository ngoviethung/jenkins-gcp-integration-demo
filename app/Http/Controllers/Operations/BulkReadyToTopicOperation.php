<?php

namespace App\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;


trait BulkReadyToTopicOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupBulkReadyToTopicRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/bulk-ready-to-topic', [
            'as'        => $routeName.'.bulkReadyToTopic',
            'uses'      => $controller.'@bulkReadyToTopic',
            'operation' => 'bulkReadyToTopic',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupBulkReadyToTopicDefaults()
    {
        $this->crud->allowAccess('bulkReadyToTopic');

        $this->crud->operation('bulkReadyToTopic', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->enableBulkActions();
            $this->crud->addButton('top1', 'bulk_ready_to_topic', 'view', 'crud::buttons.bulk_ready_to_topic', 'end');

        });
    }

    /**
     * Create duplicates of multiple entries in the datatabase.
     *
     * @param int $id
     *
     * @return Response
     */
    public function bulkReadyToTopic()
    {
        $this->crud->hasAccessOrFail('bulkReadyToTopic');

        $entries = $this->request->input('entries');
        $data = [];

        $ready_to_topic = $this->request->input('ready_to_topic');

        foreach ($entries as $key => $id) {
            if ($item = $this->crud->model->find($id)) {
                $item->ready_to_topic = $ready_to_topic;
                $item->save();
            }
        }

        return $data;
    }
}
