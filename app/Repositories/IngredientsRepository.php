<?php

namespace App\Repositories;

use App\Models\Ingredient;

class IngredientsRepository
{
    public static function getListWithTranslations($language, $orderByField='', $orderByDirection='', $searchText='', $paginateBy='')
    {
        $queryObject = Ingredient::query()
            ->selectRaw('ingredients.id, proteins, carbohydrates, fat, calories, ingredients_translations.translation AS title')
            ->join('ingredients_translations', 'ingredients.id', '=', 'ingredients_translations.ingredient_id')
            ->where('lang', $language);

        if (strlen($searchText)>2) {
            $queryObject->where('ingredients_translations.translation', 'like', '%'.$searchText.'%');
        }

        if (!empty($orderByField) && !empty($orderByDirection)) {
            $queryObject->orderBy($orderByField, $orderByDirection);
        } else {
            $queryObject->orderBy('ingredients_translations.translation', 'asc');
        }

        if (is_numeric($paginateBy)) {
            return $queryObject->paginate($paginateBy);
        } else {
            return $queryObject->get();
        }
    }

    public static function getIngredientWithTranslation($id, $language='lt')
    {
        return Ingredient::query()
            ->selectRaw('ingredients.id, proteins, carbohydrates, fat, calories, ingredients_translations.translation AS title')
            ->join('ingredients_translations', 'ingredients.id', '=', 'ingredients_translations.ingredient_id')
            ->where('lang', $language)
            ->where('ingredients.id', $id)
            ->first();
    }
}
