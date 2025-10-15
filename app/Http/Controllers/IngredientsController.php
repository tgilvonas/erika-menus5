<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientRequest;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use App\Repositories\IngredientsRepository;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class IngredientsController
{
    public function index(): Response
    {
        return Inertia::render('Ingredients');
    }

    public function getJsonList()
    {
        return IngredientsRepository::getListWithTranslations(
            'lt',
            request('order_by_field'),
            request('order_by_direction'),
            request('search_text'),
            request('paginate_by')
        );
    }

    public function save(IngredientRequest $request)
    {
        if (is_numeric(request('id'))) {
            $ingredient = Ingredient::find(request('id'));
            $ingredientTranslation = IngredientTranslation::query()->where('ingredient_id', '=', request('id'))->first();
            $msgPart = __('general.updated');
        } else {
            $ingredientExists = IngredientTranslation::query()->where('translation', request('title'))->get();
            if (count($ingredientExists)>0) {
                return [
                    'result'=>'ingredient_exists',
                    'message'=>__('general.ingredient').' "'.request('title').'" '.__('general.already_exists')
                ];
            }

            $ingredient = new Ingredient();
            $ingredientTranslation = new IngredientTranslation();
            $msgPart = __('general.created');
        }

        $ingredient->ingredient_title = $request->title;
        $ingredient->proteins = $request->proteins;
        $ingredient->fat = $request->fat;
        $ingredient->carbohydrates = $request->carbohydrates;
        $ingredient->calories = $request->calories;
        $ingredient->save();

        $ingredientTranslation->ingredient_id = $ingredient->id;
        $ingredientTranslation->translation = $request->title;
        $ingredientTranslation->lang = 'lt'; // temporarily
        $ingredientTranslation->save();

        return [
            'result' => 'Success',
            'message' => __('general.ingredient').' "'.$ingredientTranslation->translation.'" '.$msgPart,
            'ingredient' => $ingredient,
        ];
    }

    public function delete()
    {
        $ingredientTranslation = IngredientTranslation::query()->where('ingredient_id', request('id'))
            ->where('lang', 'lt')
            ->first();
        $ingredientTitle = $ingredientTranslation->translation;

        $ingredientExistsInDishes = DB::table('dishes_ingredients')->where('ingredient_id', request('id'))->get();
        if (count($ingredientExistsInDishes)>0) {
            return [
                'result' => 'error',
                'message' => __('general.ingredient').' "'. $ingredientTitle.'" '.__('general.included_into_meals_delete_impossible'),
            ];
        }

        IngredientTranslation::query()->where('ingredient_id', request('id'))->delete();
        Ingredient::query()->where('id', request('id'))->delete();

        return [
            'result' => 'success',
            'message' => __('general.ingredient').' "'.$ingredientTitle.'" '.__('general.deleted'),
        ];
    }
}
