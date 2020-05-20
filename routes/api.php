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
//Verificações
//Rotas de Autenticações
Route::post('/register', 'AuthController@register')->middleware(['check.assistance','check.reception','check.nutritionist','check.student']);
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');
//Rotas de recuperação de senha

//Falta criar os controllers de redifinir senha.
Route::post('/redefinepassword', 'PasswordResetController@redefinePassword');
Route::post('/reset', 'PasswordResetController@reset');

//total - 11
//Campus - Campus
//Apenas Admin pode fazer CRUD
route::group(['prefix'=>'campus', 'middleware' => ['check.assistance',
                    'check.reception','check.nutritionist','check.student']], function (){
    Route::get('/', 'Admin\CampusController@index')->name('campus.index');
    Route::get('/all', 'Admin\CampusController@all')->name('campus.all');
    Route::post('/', 'Admin\CampusController@store')->name('campus.store');
    Route::get('/show/{id}', 'Admin\CampusController@show')->name('campus.show');
    Route::put('/{id}', 'Admin\CampusController@update')->name('campus.update');
    Route::delete('/{id}', 'Admin\CampusController@destroy')->name('campus.destroy');
});

//Apenas Admin pode fazer CRUD
//User - Usuario
route::group(['prefix'=>'user','middleware' => ['check.assistance','check.reception','check.nutritionist','check.student']], function (){
   route::get('/','Admin\UserController@index')->name('user.index');
   route::get('/show/{id}','Admin\UserController@show')->name('user.show');
   Route::put('/{id}', 'Admin\UserController@update')->name('user.update');
   Route::delete('/{id}', 'Admin\UserController@destroy')->name('user.destroy');
});

//Apenas Assitencia pode fazer CRUD
//course - Curso
route::group(['prefix'=>'course','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::get('/','Assistencia\CourseController@index')->name('course.index');
    Route::get('/all', 'Assistencia\CourseController@all')->name('course.all');
   route::post('/','Assistencia\CourseController@store')->name('course.store');
   route::get('/show/{id}','Assistencia\CourseController@show')->name('course.show');
   Route::put('/{id}', 'Assistencia\CourseController@update')->name('course.update');
   Route::delete('/{id}', 'Assistencia\CourseController@destroy')->name('course.destroy');
});

//shift - Turno
route::group(['prefix'=>'shift','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::get('/','Assistencia\ShiftController@index')->name('shift.index');
    Route::get('/all', 'Assistencia\ShiftController@all')->name('Shift.all');
   route::post('/','Assistencia\ShiftController@store')->name('shift.store');
   route::get('/show/{id}','Assistencia\ShiftController@show')->name('shift.show');
   Route::put('/{id}', 'Assistencia\ShiftController@update')->name('shift.update');
   Route::delete('/{id}', 'Assistencia\ShiftController@destroy')->name('shift.destroy');
});

//Student - Aluno
route::group(['prefix'=>'student','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::get('/','Assistencia\StudentController@index')->name('student.index');
   route::post('/','Assistencia\StudentController@store')->name('student.store');
   route::get('/show/{id}','Assistencia\StudentController@show')->name('student.show');
   Route::put('/{id}', 'Assistencia\StudentController@update')->name('student.update');
   Route::delete('/{id}', 'Assistencia\StudentController@destroy')->name('student.destroy');
});

//Meal - Refeição
route::group(['prefix'=>'meal'],function (){
    route::get('/','MealController@index')->name('meal.index')->middleware(['check.admin','check.student']);
   route::post('/','MealController@store')->name('meal.store')->middleware(['check.admin','check.reception','check.assistance','check.student']);
   route::get('/show/{id}','MealController@show')->name('meal.show')->middleware(['check.admin','check.reception','check.assistance','check.student']);
   Route::put('/{id}', 'MealController@update')->name('meal.update')->middleware(['check.admin','check.student']);
   Route::delete('/{id}', 'MealController@destroy')->name('meal.destroy')->middleware(['check.admin','check.reception','check.assistance','check.student']);
   Route::get('/search/{search}', 'MealController@search')->name('meal.search')->middleware(['check.admin','check.student']);
});

//Menu - Cardapio
//Apenas Nutricionista pode fazer CRUD
//Falta criar o middleware do student
route::group(['prefix'=>'menu','middleware' => ['check.admin','check.reception','check.assistance','check.student']],function (){
    route::get('/','MenuController@index')->name('menu.index');
    //route::get('/','MenuController@index')->name('menu.index')->middleware(['check.student']);
   route::post('/','MenuController@store')->name('menu.store');
   route::get('/show/{id}','MenuController@show')->name('menu.show');
   Route::put('/{id}', 'MenuController@update')->name('menu.update');
   Route::delete('/{id}', 'MenuController@destroy')->name('menu.destroy');
   Route::get('/search/{search}', 'MenuController@search')->name('menu.search');
});

//Scheduling - Agendamento
//Falta a parte do studante.
route::group(['prefix'=>'scheduling'],function (){
    route::get('/','SchedulingController@index')->name('scheduling.index')->middleware(['check.admin','check.nutritionist','check.student']);
   route::post('/','SchedulingController@store')->name('scheduling.store');
   route::get('/show/{id}','SchedulingController@show')->name('scheduling.show');
   Route::put('/{id}', 'SchedulingController@update')->name('scheduling.update')->middleware(['check.admin','check.nutritionist','check.student']);
   Route::delete('/{id}', 'SchedulingController@destroy')->name('scheduling.destroy');
   Route::get('/search/{search}', 'SchedulingController@search')->name('scheduling.search')->middleware(['check.admin','check.nutritionist','check.student']);
   Route::post('canceled/{id}','SchedulingController@canceledScheduling')->name('scheduling.cancel');
});


//Republic - Republica
route::group(['prefix'=>'republic','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::get('/','RepublicController@index')->name('republic.index');
   route::post('/','RepublicController@store')->name('republic.store');
   route::get('/show/{id}','RepublicController@show')->name('republic.show');
   Route::put('/{id}', 'RepublicController@update')->name('republic.update');
   Route::delete('/{id}', 'RepublicController@destroy')->name('republic.destroy');
   Route::get('/search/{search}', 'RepublicController@search')->name('republic.search');
});


//Permições - Allowstudenmealday
route::group(['prefix'=>'allowstudenmealday','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::get('/','AllowstudenmealdayController@index')->name('allowstudenmealday.index');
    route::post('/','AllowstudenmealdayController@store')->name('allowstudenmealday.store');
    route::get('/show/{republic}','AllowstudenmealdayController@show')->name('allowstudenmealday.show');
    Route::put('/{id}', 'AllowstudenmealdayController@update')->name('allowstudenmealday.update');
    Route::delete('/{id}', 'AllowstudenmealdayController@destroy')->name('allowstudenmealday.destroy');
    Route::get('/search/{search}', 'AllowstudenmealdayController@search')->name('allowstudenmealday.search');
});
