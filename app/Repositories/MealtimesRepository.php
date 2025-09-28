<?php

namespace App\Repositories;

use App\Models\Mealtime;

class MealtimesRepository
{
    public static function getPaginatedListOfMealtimes($language, $orderByField='', $orderByDirection='', $searchText='', $paginateBy='')
    {
        $queryObject = Mealtime::query()
            ->selectRaw('mealtimes.id, mealtimes_translations.translation AS title, percent_from, percent_to')
            ->join('mealtimes_translations', 'mealtimes.id', '=', 'mealtimes_translations.mealtime_id')
            ->where('lang', $language);

        if (strlen($searchText)>2) {
            $queryObject->where('mealtimes_translations.translation', 'like', '%'.$searchText.'%');
        }

        if (!empty($orderByField) && !empty($orderByDirection)) {
            $queryObject->orderBy($orderByField, $orderByDirection);
        } else {
            $queryObject->orderBy('mealtimes_translations.translation', 'asc');
        }

        if (is_numeric($paginateBy)) {
            return $queryObject->paginate($paginateBy);
        } else {
            return $queryObject->get();
        }
    }
}
