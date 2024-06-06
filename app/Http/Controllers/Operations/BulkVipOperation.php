<?php

namespace App\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;


trait BulkVipOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupBulkVipRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/bulk-vip', [
            'as'        => $routeName.'.bulkVip',
            'uses'      => $controller.'@bulkVip',
            'operation' => 'bulkVip',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupBulkVipDefaults()
    {
        $this->crud->allowAccess('bulkVip');

        $this->crud->operation('bulkVip', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->enableBulkActions();
            $this->crud->addButton('top1', 'bulk_vip', 'view', 'crud::buttons.bulk_vip', 'end');

        });
    }

    /**
     * Create duplicates of multiple entries in the datatabase.
     *
     * @param int $id
     *
     * @return Response
     */
    public function bulkVip()
    {
        $this->crud->hasAccessOrFail('bulkVip');

        $entries = $this->request->input('entries');
        $data = [];

        $vip = $this->request->input('vip');

        foreach ($entries as $key => $id) {
            if ($item = $this->crud->model->find($id)) {
                $item->vip = $vip;
                $item->save();
            }
        }

        return $data;
    }
}
