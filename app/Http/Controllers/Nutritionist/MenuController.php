<?php

namespace App\Http\Controllers\Nutritionist;

use App\Campus;
use App\Http\Controllers\Controller;
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
        'meal_id' => 'required',
    ];
    private $messages = [
        'description.required' => 'A descrição é obrigatória',
        'date.required' => 'A data é obrigatória',
        'meal_id.required' => 'A refeição é obrigatória',
    ];

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
    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $request->date;

        $menus = Menu::when($date, function ($query) use ($date) {
            return $query->where('date', $date);
        })->with('meal')
        ->where('campus_id', $user->campus_id)
        ->orderBy('date', 'desc')
        ->paginate(10);

        return response()->json($menus, 200);
    }

    public function allByDate(Request $request)
    {
        if(!$request->date){
            return response()->json([
                'message' => 'Informe a data!'
            ], 404);
        }

        $user = auth()->user();

        $menus = Menu::where('campus_id', $user->campus_id)
            ->where('date', $request->date)
            ->with('meal')
            ->orderBy('description')
            ->get();

        return response()->json($menus, 200);
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

        if(!$this->verifyMealValid($request->meal_id)){
            return response()->json([
                'message' => 'Refeição inválida !'
            ], 404);
        }
        $user = auth()->user();

        $menu->description = $request->description;
        $menu->date = $request->date;
        $menu->campus_id = $user->campus_id;
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
        $user = auth()->user();
        if ($menu->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O cardápio não pertence ao campus do usuário!'
            ], 202);
        }
        return response()->json($menu, 200);
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

        $user = auth()->user();
        if ($menu->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O cardápio não pertence ao campus do usuário!'
            ], 202);
        }

        $menu->description = $request->description;
        $menu->date = $request->date;
        $menu->campus_id = $user->campus_id;
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

        $user = auth()->user();
        if ($menu->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O cardápio não pertence ao campus do usuário!'
            ], 202);
        }
        $menu->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }
}
