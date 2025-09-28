<?php

namespace App\Repositories;


use App\Models\Eater;

class EaterRepository
{
    public static function getListWithDietTypes($language, $orderByField='', $orderByDirection='', $searchText='', $paginateBy='')
    {
        $queryObject = Eater::query()
            ->selectRaw('eaters.id, eaters.name, eaters.name as title, eaters.diet_type_id, diet_types_translations.translation AS diet_type')
            ->join('diet_types', 'diet_types.id', '=', 'eaters.diet_type_id')
            ->join('diet_types_translations', 'diet_types.id', '=', 'diet_types_translations.diet_types_id')
            ->where('lang', $language);

        if (strlen($searchText)>2) {
            $queryObject->where('name', 'like', '%'.$searchText.'%');
        }

        if (!empty($orderByField) && !empty($orderByDirection)) {
            $queryObject->orderBy($orderByField, $orderByDirection);
        } else {
            $queryObject->orderBy('name', 'asc');
        }

        if (is_numeric($paginateBy)) {
            return $queryObject->paginate($paginateBy);
        } else {
            return $queryObject->get();
        }
    }
}
