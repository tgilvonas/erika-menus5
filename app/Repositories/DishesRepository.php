<?php

namespace App\Repositories;

use App\Models\Dish;

class DishesRepository
{
    public static function getList($paginateBy = 10, $searchText = '', $language = 'lt')
    {
        $queryObj = Dish::query()->selectRaw('dishes.id, translation as title, SUM(calories * mass_netto) AS calories_total, SUM(proteins * mass_netto) AS proteins_total, SUM(fat * mass_netto) AS fat_total, SUM(carbohydrates * mass_netto) AS carbohydrates_total, SUM(mass_netto) AS mass_total')
            ->join('dishes_translations', 'dishes.id', '=', 'dishes_translations.dish_id')
            ->join('dishes_ingredients', 'dishes.id', '=', 'dishes_ingredients.dish_id')
            ->join('ingredients', 'dishes_ingredients.ingredient_id', '=', 'ingredients.id')
            ->where('dishes_translations.lang', $language)
            ->groupBy(['dishes.id', 'translation']);

        if (!empty($searchText)) {
            $queryObj->where('dishes_translations.translation', 'like', '%'.$searchText.'%');
        }

        return $queryObj->paginate($paginateBy);
    }
}
