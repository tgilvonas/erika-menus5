<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\DishTranslation;
use App\Repositories\DishesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DishesController
{
    public function index()
    {
        return Inertia::render('Dishes');
    }

    public function getJsonList()
    {
        DishesRepository::getList(
            request('paginate_by'),
            request('search_text'),
        );
    }

    public function edit($id)
    {
        if (is_numeric($id)) {
            $ingredients = Dish::getIngredients($id);
            $dishId = $id;
            $dishTranslation = DishTranslation::query()->where('dish_id', $id)
                ->where('lang', 'lt') // temporarily
                ->first();
        } else {
            $ingredients = [];
            $dishId = '';
            $dishTranslation = new DishTranslation();
        }

        return view('dishes.edit', [
            'ingredients' => $ingredients,
            'dishId' => $dishId,
            'dishTranslation' => $dishTranslation,
        ]);
    }

    public function save(Request $request)
    {
        if (is_numeric($request->dish_id)) {
            $dish = Dish::find($request->dish_id);
            $dishTranslation = DishTranslation::query()->where('dish_id', $request->dish_id)
                ->where('lang', 'lt')  // temporarily
                ->first();
        } else {
            $dish = new Dish();
            $dishTranslation = new DishTranslation();
        }

        $dish->dish_title = $request->dish_title;
        $dish->save();

        $dishTranslation->dish_id = $dish->id;
        $dishTranslation->lang = 'lt';
        $dishTranslation->translation = $request->dish_title;
        $dishTranslation->save();

        DB::table('dishes_ingredients')->where('dish_id', $dish->id)->delete();
        foreach ($request->ingredients as $key => $ingredient) {
            DB::table('dishes_ingredients')->insert([
                'dish_id' => $dish->id,
                'ingredient_id' => $ingredient['id'],
                'mass_brutto' => $ingredient['mass_brutto'],
                'mass_netto' => $ingredient['mass_netto'],
                'ord' => $key,
            ]);
        }

        return [
            'result' => 'Success',
            'message' => __('general.dish').' "'.$request->dish_title.'" '.'" '.__('general.saved'),
            'dish' => $dish,
        ];
    }

    public function delete()
    {
        $dishTranslation = DishTranslation::query()->where('dish_id', request('id'))
            ->where('lang', '=', 'lt')
            ->first();
        $dishTitle = $dishTranslation->translation;

        DishTranslation::query()->where('dish_id', request('id'))->delete();
        Dish::query()->where('id', request('id'))->delete();
        DB::table('dishes_ingredients')->where('dish_id', request('id'))->delete();

        return [
            'result' => 'Success',
            'message' => __('general.dish').' "'.$dishTitle.'" '.__('general.deleted')
        ];
    }
}
