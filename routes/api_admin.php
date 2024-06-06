<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 30-Oct-19
 * Time: 4:28 PM
 */

Route::group([
    'namespace' => 'Admin\Api',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')]
], function () {
    Route::group([
        'prefix' => 'items',
        'as' => 'api_admin.items.',
    ], function () {
        Route::get('/', 'ItemController@getItems');
        Route::get('/list_table', 'ItemController@getItemsForTable');
        Route::get('/{id}/styles', 'ItemController@getStylesById');
        Route::get('/{id}/topics', 'ItemController@getCurrentTopicsById');
    });
    Route::group([
        'prefix' => 'tasks',
        'as' => 'api_admin.tasks.',
    ], function () {
        Route::get('/{id}/styles', 'TaskController@getStylesById');
        Route::get('/{id}/types', 'TaskController@getTypesById');
    });
    Route::group([
        'prefix' => 'styles',
        'as' => 'api_admin.styles.',
    ], function () {
        Route::get('/', 'StyleController@getStyles')->name('list');
    });
    Route::group([
        'prefix' => 'types',
        'as' => 'api_admin.types.',
    ], function () {
        Route::get('/', 'TypeController@getTypes');
        Route::get('/type-options', 'TypeController@typeOptions')->name('options');
    });
    Route::group([
        'prefix' => 'topics',
        'as' => 'api_admin.topics.',
    ], function () {
        Route::get('/', 'TopicController@getTopics');
        Route::get('/{id}/topic-details', 'TopicController@getTopicDetails');
        Route::get('/{id}/types', 'TopicController@getTypesById');
    });


    Route::get('get-items-preview', 'ItemController@getItemsPreview');
    Route::get('get-item-detail', 'ItemController@getItemDetail');
    Route::get('get-item-template', 'ItemController@getItemTemplates');
    Route::get('get-item-outfit', 'ItemController@getItemOutfits');
    Route::get('get-children-type', 'TypeController@getChildrenType');
    Route::get('get-position-by-type', 'TypeController@getPositionByType');

});

