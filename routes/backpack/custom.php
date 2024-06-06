<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.


Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    Route::group(['middleware' => ['role:Admin|Tester|ItemEditor']], function () {
        Route::crud('item', 'ItemCrudController');
    });

    Route::group(['middleware' => ['role:Admin|Stylist|ItemEditor']], function () {
        Route::crud('template', 'TemplateCrudController');
    });

    Route::group(['middleware' => ['role:Admin|ItemEditor']], function () {
        Route::crud('task', 'TaskCrudController');
        Route::crud('event', 'EventCrudController');
        Route::crud('topic/outfit', 'TopicOutfitCrudController');
        Route::crud('outfit', 'OutfitCrudController');
    });

    Route::group(['middleware' => ['role:Admin']], function () {

        Route::crud('language', 'LanguageCrudController');
        Route::crud('style', 'StyleCrudController');
        Route::crud('type', 'TypeCrudController');
        Route::crud('topic', 'TopicCrudController');


        Route::crud('grouplevelitem', 'GroupLevelItemCrudController');
        Route::crud('groupleveltype', 'GroupLevelTypeCrudController');
        Route::crud('groupleveltask', 'GroupLevelTaskCrudController');
        Route::crud('taskcategory', 'TaskCategoryCrudController');


        Route::get('export', 'ExportDataController@export');
        Route::post('export_to_firebase', 'ExportDataController@exportToFirebase');
        Route::post('submitToFirebase', 'ExportDataController@submitToFirebase');
        Route::post('eloconvert/getMinMax', 'EloConvertCrudController@getMinMax');

        Route::crud('groupleveltopic', 'GroupLevelTopicCrudController');
        Route::crud('model', 'CharacterModelCrudController');
        Route::crud('eloconvert', 'EloConvertCrudController');

        Route::crud('download-export', 'DownloadExportCrudController');

        Route::crud('members', 'MemberCrudController');
        Route::crud('challenges', 'ChallengeCrudController');
        Route::crud('iap', 'IapCrudController');
        Route::crud('level', 'LevelCrudController');
        Route::crud('config', 'ConfigCrudController');
        Route::crud('shop', 'ShopCrudController');
        Route::crud('voting-reward', 'VotingRewardCrudController');

    });


    Route::crud('skin', 'SkinCrudController');
    Route::crud('position', 'PositionCrudController');
}); // this should be the absolute last line of this file
