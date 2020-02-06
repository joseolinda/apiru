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

//Rotas de Autenticação
Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');
//Rotas de recuperação de senha
Route::post('/redefinepassword', 'PasswordResetController@redefinePassword');
Route::post('/reset', 'PasswordResetController@reset');

//total - 11
//Campus - Campus
//Apenas Admin pode fazer CRUD
route::group(['prefix'=>'campus', 'middleware' => ['check.admin','check.assistance']], function (){
    Route::get('/', 'CampusController@index')->name('campus.index');
    Route::post('/', 'CampusController@store')->name('campus.store');
    Route::get('/show/{id}', 'CampusController@show')->name('campus.show');
    Route::put('/{id}', 'CampusController@update')->name('campus.update');
    Route::delete('/{id}', 'CampusController@destroy')->name('campus.destroy');
    Route::get('/search/{search}', 'CampusController@search')->name('campus.search');
});

//Apenas Admin pode fazer CRUD
//User - Usuario
route::group(['prefix'=>'user', 'middleware' => ['check.admin']], function (){
   route::get('/','UserController@index')->name('user.index');
   route::post('/','UserController@store')->name('user.store');
   route::get('/show/{id}','UserController@show')->name('user.show');
   Route::put('/{id}', 'UserController@update')->name('user.update');
   Route::delete('/{id}', 'UserController@destroy')->name('user.destroy');
   Route::get('/search/{search}', 'UserController@search')->name('user.search');
});

//Apenas Assitencia pode fazer CRUD
//course - Curso
route::group(['prefix'=>'course','middleware' => ['check.assistance']],function (){
    route::get('/','CourseController@index')->name('course.index');
   route::post('/','CourseController@store')->name('course.store');
   route::get('/show/{id}','CourseController@show')->name('course.show');
   Route::put('/{id}', 'CourseController@update')->name('course.update');
   Route::delete('/{id}', 'CourseController@destroy')->name('course.destroy');
   Route::get('/search/{search}', 'CourseController@search')->name('course.search');
});

//shift - Turno
route::group(['prefix'=>'shift','middleware' => ['check.assistance']],function (){
    route::get('/','ShiftController@index')->name('shift.index');
   route::post('/','ShiftController@store')->name('shift.store');
   route::get('/show/{id}','ShiftController@show')->name('shift.show');
   Route::put('/{id}', 'ShiftController@update')->name('shift.update');
   Route::delete('/{id}', 'ShiftController@destroy')->name('shift.destroy');
   Route::get('/search/{search}', 'ShiftController@search')->name('shift.search');
});

//Student - Aluno
route::group(['prefix'=>'student','middleware' => ['check.assistance']],function (){
    route::get('/','StudentController@index')->name('student.index');
   route::post('/','StudentController@store')->name('student.store');
   route::get('/show/{id}','StudentController@show')->name('student.show');
   Route::put('/{id}', 'StudentController@update')->name('student.update');
   Route::delete('/{id}', 'StudentController@destroy')->name('student.destroy');
   Route::get('/search/{search}', 'StudentController@search')->name('student.search');
});

//Meal - Refeição
route::group(['prefix'=>'meal'],function (){
    route::get('/','MealController@index')->name('meal.index')->middleware(['check.nutritionist']);
   route::post('/','MealController@store')->name('meal.store')->middleware(['check.nutritionist']);
   route::get('/show/{id}','MealController@show')->name('meal.show')->middleware(['check.nutritionist']);
   Route::put('/{id}', 'MealController@update')->name('meal.update')->middleware(['check.nutritionist','check.assistance','check.reception']);
   Route::delete('/{id}', 'MealController@destroy')->name('meal.destroy')->middleware(['check.nutritionist']);
   Route::get('/search/{search}', 'MealController@search')->name('meal.search')->middleware(['check.nutritionist','check.assistance','check.reception']);
});

//Menu - Cardapio
//Apenas Nutricionista pode fazer CRUD
route::group(['prefix'=>'menu','middleware' => ['check.nutritionist']],function (){
    route::get('/','MenuController@index')->name('menu.index');
   route::post('/','MenuController@store')->name('menu.store');
   route::get('/show/{id}','MenuController@show')->name('menu.show');
   Route::put('/{id}', 'MenuController@update')->name('menu.update');
   Route::delete('/{id}', 'MenuController@destroy')->name('menu.destroy');
   Route::get('/search/{search}', 'MenuController@search')->name('menu.search');
});

//Scheduling - Agendamento
route::group(['prefix'=>'scheduling'],function (){
    route::get('/','SchedulingController@index')->name('scheduling.index');
   route::post('/','SchedulingController@store')->name('scheduling.store');
   route::get('/show/{id}','SchedulingController@show')->name('scheduling.show');
   Route::put('/{id}', 'SchedulingController@update')->name('scheduling.update')->middleware(['check.assistance','check.reception']);
   Route::delete('/{id}', 'SchedulingController@destroy')->name('scheduling.destroy');
   Route::get('/search/{search}', 'SchedulingController@search')->name('scheduling.search')->middleware(['check.assistance','check.reception']);
});


//Republic - Republica
route::group(['prefix'=>'republic','middleware' => ['check.assistance']],function (){
    route::get('/','RepublicController@index')->name('republic.index');
   route::post('/','RepublicController@store')->name('republic.store');
   route::get('/show/{id}','RepublicController@show')->name('republic.show');
   Route::put('/{id}', 'RepublicController@update')->name('republic.update');
   Route::delete('/{id}', 'RepublicController@destroy')->name('republic.destroy');
   Route::get('/search/{search}', 'RepublicController@search')->name('republic.search');
});


//Permições - Allowstudenmealday
route::group(['prefix'=>'allowstudenmealday','middleware' => ['check.assistance']],function (){
    route::get('/','AllowstudenmealdayController@index')->name('allowstudenmealday.index');
    route::post('/','AllowstudenmealdayController@store')->name('allowstudenmealday.store');
    route::get('/show/{republic}','AllowstudenmealdayController@show')->name('allowstudenmealday.show');
    Route::put('/{id}', 'AllowstudenmealdayController@update')->name('allowstudenmealday.update');
    Route::delete('/{id}', 'AllowstudenmealdayController@destroy')->name('allowstudenmealday.destroy');
    Route::get('/search/{search}', 'AllowstudenmealdayController@search')->name('allowstudenmealday.search');
});
