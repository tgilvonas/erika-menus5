<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Day extends Model
{
    protected $guarded = ['id'];

    public function eater()
    {
        return $this->belongsTo(Eater::class);
    }

    public function mealtimes()
    {
        return $this->belongsToMany(Mealtime::class, 'days_mealtimes');
    }

    public function sumNutritionValues(): Attribute
    {
        $day = self::getDayWithAllRelations($this->id);
        return Attribute::make(
            get: function () use ($day) {
                $nutritionValues = [];

                $nutritionValues['total'] = [
                    'mass_brutto' => 0,
                    'mass_netto' => 0,
                    'proteins' => 0,
                    'fat' => 0,
                    'carbohydrates' => 0,
                    'calories' => 0,
                ];

                foreach ($day->mealtimes as $mealtime) {
                    foreach ($mealtime->dishes as $dish) {
                        $nutritionValues['dishes'][$dish->id] = [
                            'mass_brutto' => 0,
                            'mass_netto' => 0,
                            'proteins' => 0,
                            'fat' => 0,
                            'carbohydrates' => 0,
                            'calories' => 0,
                        ];
                        foreach ($dish->ingredients as $ingredient) {
                            $nutritionValues['dishes'][$dish->id]['mass_brutto'] = $ingredient->pivot->mass_brutto ?? 0;
                            $nutritionValues['dishes'][$dish->id]['mass_netto'] = $ingredient->pivot->mass_netto ?? 0;
                            $nutritionValues['dishes'][$dish->id]['proteins'] += ($ingredient->pivot->mass_netto ?? 0) * $ingredient->proteins;
                            $nutritionValues['dishes'][$dish->id]['fat'] += ($ingredient->pivot->mass_netto ?? 0) * $ingredient->fat;
                            $nutritionValues['dishes'][$dish->id]['carbohydrates'] += ($ingredient->pivot->mass_netto ?? 0) * $ingredient->carbohydrates;
                            $nutritionValues['dishes'][$dish->id]['calories'] += ($ingredient->pivot->mass_netto ?? 0) * $ingredient->calories;
                        }
                        $nutritionValues['total']['mass_brutto'] += $nutritionValues['dishes'][$dish->id]['mass_brutto'];
                        $nutritionValues['total']['mass_netto'] += $nutritionValues['dishes'][$dish->id]['mass_netto'];
                        $nutritionValues['total']['proteins'] += $nutritionValues['dishes'][$dish->id]['proteins'];
                        $nutritionValues['total']['fat'] += $nutritionValues['dishes'][$dish->id]['fat'];
                        $nutritionValues['total']['carbohydrates'] += $nutritionValues['dishes'][$dish->id]['carbohydrates'];
                        $nutritionValues['total']['calories'] += $nutritionValues['dishes'][$dish->id]['calories'];
                    }
                }
                return $nutritionValues;
            },
        );
    }

    public static function getDayWithAllRelations($id = 'new')
    {
        if (is_numeric($id)) {
            $day = self::with([
                'eater',
                'mealtimes',
                'mealtimes.translation',
                'mealtimes.dishes' => function ($query) use ($id) { $query->wherePivot('day_id', $id); },
                'mealtimes.dishes.translation',
                'mealtimes.dishes.ingredients' => function ($query) { $query->withPivot(['mass_brutto', 'mass_netto']); },
                'mealtimes.dishes.ingredients.translation',
            ])->where('days.id', $id)->first();
        } else {
            $day = new self;
            $day->date = date('Y-m-d');
            $day->eater = new \stdClass();
            $day->mealtimes = [];
        }
        return $day;
    }

    public static function getListOfDays($eaterId=null, $dateFrom=null, $dateTo=null)
    {
        $queryObject = self::select(DB::raw("days.id, days.date, eaters.name AS eater_name, CONCAT(days.date, ' ', eaters.name) AS title"))
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
