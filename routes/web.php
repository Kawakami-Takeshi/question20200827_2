<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'admin','middleware' => 'auth'], function() {
    Route::get('family/create', 'Admin\FamilyController@add');
    Route::get('family/edit', 'Admin\FamilyController@edit');
    Route::post('family/edit', 'Admin\FamilyController@update');
    Route::post('family/create', 'Admin\FamilyController@create');
    Route::get('family', 'Admin\FamilyController@index');
    Route::get('family/delete', 'Admin\FamilyController@delete');
    
    Route::get('rate/create', 'Admin\RateController@add');
    Route::get('rate/asset', 'Admin\AssetController@add');
    //Route::post('rate/asset', 'Admin\AssetController@create');
});