<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'namespace' => 'Api',
], function () {
    Route::get('/ip', 'IndexController@getTime');
    Route::get('/getOrder', 'IndexController@getInfoFromOrder');
    Route::post('/iap', 'IndexController@iap');

    Route::get('/event/{id}', 'EventController@show');

    //Route::any('create_item', 'ItemController@createItem');
    //Route::any('update_item', 'ItemController@updateItem');
    Route::any('create_image', 'ItemController@createImage');

//    Route::any('create_topic', 'TopicController@createTopic');
//    Route::any('update_topic', 'TopicController@updateTopic');
//    Route::any('delete_topic', 'TopicController@deleteTopic');
//
//    Route::any('create_type', 'TypeController@createType');
//    Route::any('update_type', 'TypeController@updateType');
//    Route::any('delete_type', 'TypeController@deleteType');
//
//    Route::any('create_style', 'StyleController@createStyle');
//    Route::any('update_style', 'StyleController@updateStyle');
//    Route::any('delete_style', 'StyleController@deleteStyle');

    Route::post('/login', 'UserController@login');
    Route::post('/logout', 'UserController@logout');

    Route::get('/getPairItems', 'EloController@getPairItems');
    Route::post('/vote', 'EloController@vote');
    Route::get('/data', 'EloController@getData');

    Route::any('/outfit/create-from-template', 'OutfitController@createFromTemplate');
});

Route::post('conver_image_base64', 'Api\TemplateController@converImageBase64');
Route::any('merge_and_zip_image', 'Api\TemplateController@mergeAndZipImage');
Route::post('template/delete_file', 'Api\TemplateController@deleteFile');
Route::post('template/clone-template', 'Api\TemplateController@cloneTemplate');


Route::any('test-merge-image', 'MergeImageController@mergeAndZipImage');
Route::any('test-vote', 'Api\ChallengeController@vote');


//API for client

Route::post('identifier', 'Api\LoginController@identifier');//ok

Route::group(['namespace' => 'Api', 'middleware' => 'jwt.verify.login'], function () {
    Route::post('login-google', 'LoginController@loginGoogle');//ok
    Route::post('login-facebook', 'LoginController@loginFacebook');//ok
});

Route::group(['namespace' => 'Api', 'middleware' => 'jwt.verify'], function () {

    Route::post('link-to-google', 'LoginController@linkToGoogle');//ok
    Route::post('link-to-facebook', 'LoginController@linkToFacebook');//ok

    Route::get('user/info', 'UserController@getUserInfo');  //ok
    Route::delete('user/delete', 'UserController@deleteUser');//ok
    Route::post('logout', 'UserController@logout');//ok

    Route::get('/data', 'DataController@getData');  //ok

    Route::get('/challenges', 'ChallengeController@getChallenges');  //ok
    Route::post('/submit-challenge', 'ChallengeController@submitChallenge');  //ok
    Route::get('/vote-challenge', 'ChallengeController@getVote'); //ok
    Route::post('/submit-vote-challenge', 'ChallengeController@submitVote'); //ok
    Route::get('/result-challenge', 'ChallengeController@getResultChallenge'); //ok


    Route::get('/iap', 'IapController@getIap'); //ok
    Route::post('/buy-iap', 'IapController@buyIap'); //ok
    Route::post('/add-currency-test', 'IapController@addCurrency'); //ok

    Route::post('/buy-item', 'ItemPurchaseController@buyItem'); //ok
    Route::post('/reset-unworn-item', 'ItemPurchaseController@resetUnwornItem'); //ok

    Route::post('/reward', 'RewardController@reward'); //ok

    

});

