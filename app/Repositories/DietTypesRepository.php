<?php

namespace App\Repositories;

use App\Models\DietType;

class DietTypesRepository
{
    public static function getListOfDietTypes($lang = 'lt')
    {
        return DietType::query()->selectRaw('diet_types.id, diet_types_translations.translation AS title, calories_min, calories_max, proteins_min, proteins_max, fat_min, fat_max, carbohydrates_min, carbohydrates_max')
            ->join('diet_types_translations', 'diet_types.id', '=', 'diet_types_translations.diet_types_id')
            ->where('lang', $lang)
            ->get();
    }

    public static function getPaginatedListOfDietTypes($language, $orderByField='', $orderByDirection='', $searchText='', $paginateBy='')
    {
        $queryObject = DietType::query()->selectRaw('diet_types.id, diet_types_translations.translation AS title, calories_min, calories_max, proteins_min, proteins_max, fat_min, fat_max, carbohydrates_min, carbohydrates_max')
            ->join('diet_types_translations', 'diet_types.id', '=', 'diet_types_translations.diet_types_id')
            ->where('lang', $language);

        if (strlen($searchText)>2) {
            $queryObject->where('diet_types_translations.translation', 'like', '%'.$searchText.'%');
        }

        if (!empty($orderByField) && !empty($orderByDirection)) {
            $queryObject->orderBy($orderByField, $orderByDirection);
        } else {
            $queryObject->orderBy('diet_types_translations.translation', 'asc');
        }

        if (is_numeric($paginateBy)) {
            return $queryObject->paginate($paginateBy);
        } else {
            return $queryObject->get();
        }
    }

    public static function getDietTypeWithTranslation($id, $lang = 'lt')
    {
        return DietType::query()->selectRaw('diet_types.id, diet_types_translations.translation AS title, calories_min, calories_max, proteins_min, proteins_max, fat_min, fat_max, carbohydrates_min, carbohydrates_max')
            ->join('diet_types_translations', 'diet_types.id', '=', 'diet_types_translations.diet_types_id')
            ->where('lang', $lang)
            ->where('diet_types.id', $id)
            ->first();
    }
}
