<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Meal;
use App\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'description' => 'required',
        'date' => 'required',
        'campus_id' => 'required',
        'meal_id' => 'required',
    ];
    private $messages = [
        'description.required' => 'O nome é obrigatório',
        'date.required' => 'A Data é obrigatória',
        'campus_id.required' => 'O Campus é obrigatório',
        'meal_id.required' => 'A refeição é obrigatória',
    ];

    //Métodos Criados
    public function verifyCampusValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Campus::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }
    public function verifyMealValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Meal::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = Menu::get();
        return response()->json($menus);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $menu = new Menu();

        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        //Verificando se existe campus cadastrad.
        if(!$this->verifyCampusValid($request->campus_id)){
            return response()->json([
                'message' => 'Campus inválido!'
            ], 404);
        }
        if(!$this->verifyMealValid($request->meal_id)){
            return response()->json([
                'message' => 'Refeição inválida !'
            ], 404);
        }
        $menu->description = $request->description;
        $menu->date = $request->date;
        $menu->campus_id = $request->campus_id;
        $menu->meal_id = $request->meal_id;
        $menu->save();

        return response()->json($menu, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menu = Menu::find($id);
        if (!$menu){
            return response()->json([
                'message' => 'Cardápio não encontrado!'
            ], 404);
        }
        return response()->json($menu);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        $menu = Menu::find($id);

        if(!$menu){
            return response()->json([
                'message' => 'Cardápio não encontrado!'
            ], 404);
        }

        $menu->description = $request->description;
        $menu->date = $request->date;
        $menu->campus_id = $request->campus_id;
        $menu->meal_id = $request->meal_id;
        $menu->save();

        return response()->json($menu, 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $menu = Menu::find($id);
        if (!$menu){
            return response()->json([
                'message' => 'Cardápio não encontrado!'
            ], 404);
        }
        $menu->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $menu = Menu::where( 'date', 'LIKE', '%' . $search . '%' )->get();
        if(!$menu){
            return response()->json([
                'message' => 'Cardápio não encontrado!'
            ], 404);
        }
        return response()->json($menu, 200);
    }
}
