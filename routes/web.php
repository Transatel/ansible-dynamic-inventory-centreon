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

// ------------------------------------------------------------------------
// ANSIBLE INVENTORY

Route::get('ansible/inventory/list','AnsibleInventory\AnsibleInventoryController@getCompleteInventory');
Route::get('ansible/inventory/host/{name}','AnsibleInventory\AnsibleInventoryController@getHostDetails')->where('name', '[A-Za-z0-9_/-]+');
// non-standard
Route::delete('ansible/inventory/cache','AnsibleInventory\AnsibleInventoryController@reloadInventoryCache');



