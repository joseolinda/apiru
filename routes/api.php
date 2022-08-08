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

Route::middleware('auth:api', 'throttle:60000,1')->get('/user', function (Request $request) {
    return $request->user();
});
//Verificações
//Rotas de Autenticações
Route::post('/register', 'AuthController@register')->middleware(['check.assistance','check.reception','check.nutritionist','check.student']);
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');
//Rotas de recuperação de senha

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
route::group(['prefix'=>'course'],function (){
    route::get('/','Assistencia\CourseController@index')->name('course.index')->middleware(['check.admin','check.reception','check.nutritionist','check.student']);
    Route::get('/all', 'Assistencia\CourseController@all')->name('course.all')->middleware(['check.admin','check.reception','check.nutritionist','check.student']);
   route::post('/','Assistencia\CourseController@store')->name('course.store')->middleware(['check.admin','check.reception','check.nutritionist','check.student']);
   route::get('/show/{id}','Assistencia\CourseController@show')->name('course.show')->middleware(['check.admin','check.reception','check.student']);
   Route::put('/{id}', 'Assistencia\CourseController@update')->name('course.update')->middleware(['check.admin','check.reception','check.nutritionist','check.student']);
   Route::delete('/{id}', 'Assistencia\CourseController@destroy')->name('course.destroy')->middleware(['check.admin','check.reception','check.nutritionist','check.student']);
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
    route::get('/all','Assistencia\StudentController@all')->name('student.all');
    route::get('/history/{id}','Assistencia\StudentController@historyStudent')->name('student.historyStudent');
   route::post('/','Assistencia\StudentController@store')->name('student.store');
   route::get('/show/{id}','Assistencia\StudentController@show')->name('student.show');
   Route::put('/{id}', 'Assistencia\StudentController@update')->name('student.update');
   Route::delete('/{id}', 'Assistencia\StudentController@destroy')->name('student.destroy');
});

//Republic - Republica
route::group(['prefix'=>'republic','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::get('/','Assistencia\RepublicController@index')->name('republic.index');
    route::post('/','Assistencia\RepublicController@store')->name('republic.store');
    route::get('/show/{id}','Assistencia\RepublicController@show')->name('republic.show');
    Route::put('/{id}', 'Assistencia\RepublicController@update')->name('republic.update');
    Route::delete('/{id}', 'Assistencia\RepublicController@destroy')->name('republic.destroy');
    Route::get('/students-are-not-republic', 'Assistencia\RepublicController@studentAreNotRepublic')->name('republic.studentAreNotRepublic');
    //item republic
    route::get('/item/{idRepublic}','Assistencia\ItemRepublicController@index')->name('itemRepublic.index');
    route::post('/item','Assistencia\ItemRepublicController@store')->name('itemRepublic.store');
    route::put('/item/{id}','Assistencia\ItemRepublicController@update')->name('itemRepublic.update');
    route::delete('/item/{id}','Assistencia\ItemRepublicController@destroy')->name('itemRepublic.destroy');
});
route::group(['prefix'=>'item-republic','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::post('/','Assistencia\ItemRepublicController@store')->name('itemRepublic.store');
});


//Permições - Allowstudenmealday
route::group(['prefix'=>'allowstudenmealday','middleware' => ['check.admin','check.reception','check.nutritionist','check.student']],function (){
    route::get('/','Assistencia\AllowstudenmealdayController@index')->name('allowstudenmealday.index');
    route::get('/all-meal','Assistencia\AllowstudenmealdayController@allMeal')->name('allowstudenmealday.allMeal');
    route::post('/','Assistencia\AllowstudenmealdayController@store')->name('allowstudenmealday.store');
    route::get('/show/{republic}','Assistencia\AllowstudenmealdayController@show')->name('allowstudenmealday.show');
    Route::put('/{id}', 'Assistencia\AllowstudenmealdayController@update')->name('allowstudenmealday.update');
    Route::delete('/{id}', 'Assistencia\AllowstudenmealdayController@destroy')->name('allowstudenmealday.destroy');
});

//Scheduling - Agendamento
route::group(['prefix'=>'scheduling', 'middleware' => ['check.admin','check.nutritionist','check.student']],function (){
    route::post('/justification/{id}','Assistencia\SchedulingController@absenceJustification')->name('scheduling.absenceJustification');
    route::post('/','Assistencia\SchedulingController@scheduleMeal')->name('scheduling.scheduleMeal');
    route::get('/list-by-date','Assistencia\SchedulingController@listSchedulingMeals')->name('scheduling.listSchedulingMeals');
    route::delete('/{id}', 'Assistencia\SchedulingController@destroy')->name('scheduling.destroy');
});

//Meal - Refeição
route::group(['prefix'=>'meal'],function (){
    route::get('/','Nutritionist\MealController@index')->name('meal.index')->middleware(['check.admin','check.student']);
    route::get('/all','Nutritionist\MealController@all')->name('meal.all')->middleware(['check.admin','check.student']);
    route::post('/','Nutritionist\MealController@store')->name('meal.store')->middleware(['check.admin','check.reception','check.assistance','check.student']);
    route::get('/show/{id}','Nutritionist\MealController@show')->name('meal.show')->middleware(['check.admin','check.reception','check.student']);
    Route::put('/{id}', 'Nutritionist\MealController@update')->name('meal.update')->middleware(['check.admin','check.student']);
    Route::delete('/{id}', 'Nutritionist\MealController@destroy')->name('meal.destroy')->middleware(['check.admin','check.reception','check.assistance','check.student']);
});

//Menu - Cardapio
//Apenas Nutricionista pode fazer CRUD
route::group(['prefix'=>'menu','middleware' => ['check.admin','check.reception','check.assistance','check.student']],function (){
    route::get('/','Nutritionist\MenuController@index')->name('menu.index');
    route::get('/all-by-date','Nutritionist\MenuController@allByDate')->name('menu.allByDate');
    route::post('/','Nutritionist\MenuController@store')->name('menu.store');
    route::get('/show/{id}','Nutritionist\MenuController@show')->name('menu.show');
    Route::put('/{id}', 'Nutritionist\MenuController@update')->name('menu.update');
    Route::delete('/{id}', 'Nutritionist\MenuController@destroy')->name('menu.destroy');
});

route::group(['prefix'=>'report','middleware' => ['check.admin', 'check.student']],function (){
    route::get('/list-scheduling','ReportStudentMealController@listScheduling')->name('report.listScheduling');
    route::get('/list-scheduling-print','ReportStudentMealController@listSchedulingPrint')->name('report.listSchedulingPrint');
    route::get('/all-meal','ReportStudentMealController@allMeal')->name('report.allMeal');
    Route::get('/all-course', 'ReportStudentMealController@allCourse')->name('course.allCourse');
});

route::group(['prefix' => 'report', 'middleware' => ['check.admin']], function () {
    route::get('/list-waste', 'ReportWasteController@index')->name('reportWaste.listWaste');
    route::put('/add-waste-report', 'ReportWasteController@update')->name('reportWaste.addReport');
});

route::group(['prefix'=>'all'],function (){
    route::get('/campus','All@campus_active')->name('all.campus');
    route::get('/meals','All@allMeal')->name('all.allMeal');
    route::get('/menus-today','All@menusToday')->name('all.menusToday');
    route::get('/menus-week','All@menusByWeek')->name('all.menusByWeek');
    route::get('/menus-by-date','All@allMenuByDay')->name('all.allMenuByDay');
    route::get('/students','All@allStudent')->name('all.allStudent');
    route::get('/students-by-mat-or-cod','All@studentByMatOrCod')->name('all.studentByMatOrCod');
    route::get('/show-student/{id}','All@showStudent')->name('all.showStudent');
    route::get('/show-user/{id}','All@showUser')->name('all.showUser');
    route::get('/','Assistencia\AllowstudenmealdayController@index')->name('allowstudenmealday.index');
});

route::group(['prefix'=>'confirm-meals','middleware' => ['check.admin', 'check.student']],function (){
    route::post('/','ConfirmMealsController@confirmMeal')->name('confirmMeals.confirmMeal');
    route::get('/list','ConfirmMealsController@listConfirmedMeals')->name('confirmMeals.listConfirmedMeals');
    route::post('/registered','ConfirmMealsController@qtdMealsRegistered')->name('confirmMeals.qtdMealsRegistered');
});

route::group(['prefix'=>'student/schedulings','middleware' => ['check.admin', 'check.reception','check.assistance', 'check.nutritionist']],function (){
    route::get('/used','Student\StudentSchedulingController@schedulings_used')->name('student.schedulings.used');
    route::get('/not-used','Student\StudentSchedulingController@schedulings_not_used')->name('student.schedulings.notUsed');
    route::get('/to-use','Student\StudentSchedulingController@schedulings_to_use')->name('student.schedulings.toUse');
    route::get('/canceled','Student\StudentSchedulingController@schedulings_canceled')->name('student.schedulings.canceled');
    route::get('/','Student\StudentSchedulingController@schedulings')->name('student.schedulings');
    route::post('/new','Student\StudentSchedulingController@newScheduling')->name('student.newScheduling');
    route::put('/cancel','Student\StudentSchedulingController@cancelScheduling')->name('student.cancelScheduling');
    route::get('/allows-meal-by-day','Student\StudentSchedulingController@allowsMealByDay')->name('student.allowsMealByDay');
});

//Gerenciador de formularios 
route::group(['prefix' => 'forms', 'middleware' => ['check.admin', 'check.assistance']], function (){
    route::get('/', 'Forms\AssistenciaForms@index')->name('forms.show');
    route::post('/new', 'Forms\AssistenciaForms@store')->name('forms.store');
    route::put('/{id}/edit', 'Forms\AssistenciaForms@update')->name('forms.update');
    route::delete('/{id}/delete', 'Forms\AssistenciaForms@destroy')->name('forms.destroy');
});

//Respostas do formulário
route::group(['prefix'=> 'student/forms', 'middleware' => ['check.admin', 'check.student'] function(){
    route::get('/', 'Forms\Student@index')->name('forms.view');
    route::post('/response', 'Forms\Student@store')->name('forms.response');    
}])