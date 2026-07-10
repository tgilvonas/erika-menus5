<?php

namespace App\Repositories;

use App\Models\Day;

class DaysRepository
{
    public static function getDayWithAllRelations($id = 'new')
    {
        if (is_numeric($id)) {
            $day = Day::query()->with([
                'eater',
                'mealtimes',
                'mealtimes.translation',
                'mealtimes.dishes' => function ($query) use ($id) { $query->wherePivot('day_id', $id); },
                'mealtimes.dishes.translation',
                'mealtimes.dishes.ingredients' => function ($query) { $query->withPivot(['mass_brutto', 'mass_netto']); },
                'mealtimes.dishes.ingredients.translation',
            ])->where('days.id', $id)->first();
        } else {
            $day = new Day();
            $day->date = date('Y-m-d');
            $day->eater = new \stdClass();
            $day->mealtimes = [];
        }
        return $day;
    }

    public static function getListOfDays($eaterId=null, $dateFrom=null, $dateTo=null)
    {
        $queryObject = Day::query()->select(DB::raw("days.id, days.date, eaters.name AS eater_name, CONCAT(days.date, ' ', eaters.name) AS title"))
            ->join('eaters', 'days.eater_id', '=', 'eaters.id')
            ->orderBy('date', 'desc');

        if (is_numeric($eaterId)) {
            $queryObject->where('days.eater_id', $eaterId);
        }
        if (!empty($dateFrom)) {
            $timeObject = new Carbon($dateFrom);
            $queryObject->whereDate('days.date', '>=', $timeObject->format('Y-m-d'));
        }
        if (!empty($dateTo)) {
            $queryObject->whereDate('days.date', '<=', $dateTo);
        }

        return $queryObject->paginate(10);
    }
}
