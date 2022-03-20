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
    return view('welcome');
});
route::get('/usuarios/','UserController@index');
route::get('/usuario/{id}','UserController@show');
*/

Route::get("/json/alunos", function () {
     //return "Oi";
$headers =[
        'Access-Control-Allow-Origin' => '*',
        'Content-Type' => 'application/json',
	'Access-Control-Allow-Methods' => 'GET'
    ];


    return response()
           ->file(resource_path('json/alunos.json'), $headers); 
});
