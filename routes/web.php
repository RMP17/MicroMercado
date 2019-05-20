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

/*Route::get('/', function () {
    View::addExtension('html', 'php');
    return view('index');
});
Route::group(['domain' => '192.168.1.4'], function () {
	Route::any('{all}', function () {
		//View::addExtension('html', 'php');
		return view('index');
	})->where(['all' => '.*']);
});
*/
Route::any('{all}', function () {
	//View::addExtension('html', 'php');
	return view('index');
})->where(['all' => '.*']);

