<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your plugin. These
| routes are loaded by the RouteServiceProvider of your plugin within
| a group which contains the "web" middleware group and your plugin name
| as prefix. Now create something great!
|
*/
Route::middleware('can:paysafecardmanual.manage')->group(function () {
    Route::get('/', 'AdminController@index')->name('index');

    Route::post('/{code}/accept', 'AdminController@accept')->name('accept');
    Route::post('/{code}/refuse', 'AdminController@refuse')->name('refuse');
});
